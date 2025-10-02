<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\Payout;
use App\Models\Ally;
use App\Services\BncApiService;
use App\Services\PayoutService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class PaymentProcessorService
{
    protected BncApiService $bncApiService;
    protected PayoutService $payoutService;

    public function __construct(
        BncApiService $bncApiService,
        PayoutService $payoutService
    ) {
        $this->bncApiService = $bncApiService;
        $this->payoutService = $payoutService;
    }

    /**
     * Procesa cualquier tipo de pago de forma genérica
     */
    public function processPayment(
        Request $request,
        string $paymentType,
        array $bankValidationRules,
        callable $prepareBncData
    ): JsonResponse {
        Log::info("Solicitud recibida para procesar pago {$paymentType}.", [
            'request_data' => $request->all(),
            'payment_type' => $paymentType
        ]);

        DB::beginTransaction();
        try {
            // 1. Validar campos esenciales específicos del banco
            $bankValidatedData = $request->validate($bankValidationRules);

            // 2. Campos adicionales opcionales comunes
            $systemValidatedData = $request->validate([
                'ChildClientID' => 'nullable|string|max:50',
                'BranchID' => 'nullable|string|max:50',
                'cliente_id' => 'nullable|integer|exists:clientes,id',
                'sucursal_id' => 'nullable|integer|exists:sucursales,id',
                'aliado_id' => 'nullable|integer|exists:aliados,id',
                'descripcion' => 'nullable|string|max:255',
            ]);

            $validatedData = array_merge($bankValidatedData, $systemValidatedData);

            // 3. Preparar datos para BNC usando el callback
            $bncData = $prepareBncData($validatedData);

            Log::debug("Datos preparados para BNC {$paymentType}", [
                'bnc_data' => $bncData,
                'payment_type' => $paymentType
            ]);

            // 4. Ejecutar pago según el tipo
            $bncResponse = $this->executeBncPayment($paymentType, $bncData);

            // 5. Validar respuesta del BNC
            if (!$this->isBncResponseSuccessful($bncResponse)) {
                $errorMessage = $bncResponse['Message'] ?? $bncResponse['message'] ?? "Fallo al procesar el pago {$paymentType}.";
                Log::error("Fallo al procesar pago {$paymentType}", [
                    'bnc_response' => $bncResponse,
                    'payment_type' => $paymentType
                ]);
                throw new \Exception($errorMessage);
            }

            // 6. Procesar aliado y comisiones
            $aliadoData = $this->processAliadoData($validatedData);

            // 7. Guardar venta
            $venta = $this->createSale($paymentType, $validatedData, $bncResponse, $aliadoData);

            // 8. Guardar payout si hay aliado
            $payout = $this->payoutService->createPayout($venta, $aliadoData, $bncResponse);

            DB::commit();

            Log::info("Pago {$paymentType} procesado exitosamente", [
                'venta_id' => $venta->id,
                'payment_type' => $paymentType
            ]);

            return response()->json([
                'success' => true,
                'message' => "Pago {$paymentType} procesado exitosamente.",
                'data' => $this->buildSuccessResponse($venta, $payout, $bncResponse, $validatedData, $aliadoData)
            ], 200);
        } catch (ValidationException $e) {
            DB::rollBack();
            Log::warning("Error de validación en pago {$paymentType}", [
                'errors' => $e->errors(),
                'payment_type' => $paymentType
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Datos de entrada inválidos.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error en pago {$paymentType}: " . $e->getMessage(), [
                'payment_type' => $paymentType,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            $userMessage = $this->getUserFriendlyErrorMessage($e, $paymentType);

            return response()->json([
                'success' => false,
                'message' => $userMessage,
                'error_details' => config('app.debug') ? $e->getMessage() : null,
                'bnc_response' => $bncResponse ?? null
            ], 500);
        }
    }

    /**
     * Ejecuta el pago según el tipo
     */
    private function executeBncPayment(string $paymentType, array $bncData): array
    {
        return match ($paymentType) {
            'c2p' => $this->bncApiService->initiateC2PPayment($bncData),
            'card' => $this->bncApiService->processCardPayment($bncData),
            'p2p' => $this->bncApiService->initiateP2PPayment($bncData),
            default => throw new \Exception("Tipo de pago no soportado: {$paymentType}")
        };
    }

    /**
     * Procesa datos del aliado y calcula comisiones
     */
    private function processAliadoData(array $validatedData): array
    {
        $aliadoData = [
            'has_aliado' => false,
            'aliado_id' => null,
            'aliado_name' => null,
            'comision_porcentaje' => 0,
            'monto_comision' => 0,
            'monto_neto' => $validatedData['Amount'],
            'discount_original' => null
        ];

        if (!empty($validatedData['aliado_id'])) {
            $aliado = Ally::with('user')->find($validatedData['aliado_id']);

            if ($aliado) {
                $aliadoData['has_aliado'] = true;
                $aliadoData['aliado_id'] = $aliado->id;
                $aliadoData['aliado_name'] = $aliado->company_name ?? $aliado->user->name ?? 'Aliado';
                $aliadoData['discount_original'] = $aliado->discount;
                $aliadoData['comision_porcentaje'] = $this->extractPercentageFromDiscount($aliado->discount);
                $aliadoData = $this->calculateCommissionsFromDiscount($aliadoData, $validatedData['Amount']);
            }
        }

        return $aliadoData;
    }

    /**
     * Extrae el porcentaje numérico del campo discount
     */
    private function extractPercentageFromDiscount(?string $discount): float
    {
        if (empty($discount)) {
            return 0.0;
        }

        if (preg_match('/(\d+(?:\.\d+)?)\s*%/i', $discount, $matches)) {
            return (float) $matches[1];
        }

        if (is_numeric($discount)) {
            return (float) $discount;
        }

        if (preg_match('/(\d+(?:\.\d+)?)\s*(?:porciento|percent|pct?)/i', $discount, $matches)) {
            return (float) $matches[1];
        }

        Log::warning('No se pudo extraer porcentaje del discount', ['discount' => $discount]);
        return 0.0;
    }

    /**
     * Calcula las comisiones usando el porcentaje extraído del campo discount
     */
    private function calculateCommissionsFromDiscount(array $aliadoData, float $amount): array
    {
        $comisionPorcentaje = $aliadoData['comision_porcentaje'];

        if ($comisionPorcentaje <= 0) {
            $aliadoData['monto_comision'] = 0;
            $aliadoData['monto_neto'] = $amount;
            return $aliadoData;
        }

        $montoComision = $amount * ($comisionPorcentaje / 100);
        $aliadoData['monto_comision'] = round($montoComision, 2);
        $aliadoData['monto_neto'] = round($amount - $montoComision, 2);

        return $aliadoData;
    }

    /**
     * Crea la venta según el tipo de pago
     */
    private function createSale(string $paymentType, array $validatedData, array $bncResponse, array $aliadoData): Sale
    {
        $baseData = [
            'cliente_id' => $validatedData['cliente_id'] ?? null,
            'sucursal_id' => $validatedData['sucursal_id'] ?? null,
            'aliado_id' => $validatedData['aliado_id'] ?? null,
            'monto_total' => $validatedData['Amount'],
            'monto_pagado' => $validatedData['Amount'],
            'referencia_banco' => $bncResponse['Reference'] ?? null,
            'transaction_id' => $bncResponse['TransactionIdentifier'] ?? null,
            'estado' => 'completado',
            'fecha_venta' => now(),
            'fecha_pago' => now(),
            'descripcion' => $validatedData['descripcion'] ?? "Pago {$paymentType} procesado",
            'codigo_autorizacion' => $bncResponse['AuthorizationCode'] ?? $bncResponse['Code'] ?? null,
            'respuesta_banco' => json_encode($bncResponse),
        ];

        $specificData = match ($paymentType) {
            'c2p' => [
                'metodo_pago' => 'pago_movil',
                'terminal' => $validatedData['Terminal'],
                'banco_destino' => $validatedData['DebtorBankCode'],
                'telefono_cliente' => $validatedData['DebtorCellPhone'],
                'cedula_cliente' => $validatedData['DebtorID'],
            ],
            'card' => [
                'metodo_pago' => 'tarjeta_credito',
                'terminal' => $validatedData['TransactionIdentifier'],
                'numero_tarjeta' => substr($validatedData['CardNumber'], -4),
                'nombre_titular' => $validatedData['CardHolderName'],
                'tipo_tarjeta' => $validatedData['idCardType'],
            ],
            'p2p' => [
                'metodo_pago' => 'p2p',
                'banco_destino' => $validatedData['BeneficiaryBankCode'],
                'telefono_cliente' => $validatedData['BeneficiaryCellPhone'],
                'cedula_cliente' => $validatedData['BeneficiaryID'],
                'beneficiario' => $validatedData['BeneficiaryName'],
                'email_beneficiario' => $validatedData['BeneficiaryEmail'] ?? null,
            ],
            default => []
        };

        return Sale::create(array_merge($baseData, $specificData));
    }

    /**
     * Valida si la respuesta del BNC fue exitosa
     */
    private function isBncResponseSuccessful(array $bncResponse): bool
    {
        if (!is_array($bncResponse)) {
            return false;
        }

        if (isset($bncResponse['Status']) && $bncResponse['Status'] === 'OK') {
            return true;
        }

        if (isset($bncResponse['status']) && $bncResponse['status'] === 'OK') {
            return true;
        }

        if (isset($bncResponse['success']) && $bncResponse['success'] === true) {
            return true;
        }

        if (isset($bncResponse['AuthorizationCode']) && !empty($bncResponse['AuthorizationCode'])) {
            return true;
        }

        if (isset($bncResponse['Reference']) && !empty($bncResponse['Reference'])) {
            return true;
        }

        return false;
    }

    /**
     * Construye respuesta de éxito estandarizada
     */
    /**
     * Construye respuesta de éxito estandarizada
     */
    private function buildSuccessResponse(Sale $venta, ?Payout $payout, array $bncResponse, array $validatedData, array $aliadoData): array
    {
        $response = [
            'venta_id' => $venta->id,
            'referencia_banco' => $bncResponse['Reference'] ?? null,
            'transaction_id' => $bncResponse['TransactionIdentifier'] ?? null,
            'codigo_autorizacion' => $bncResponse['AuthorizationCode'] ?? $bncResponse['Code'] ?? null,
            'monto' => $validatedData['Amount'],
            'fecha' => $venta->fecha_venta ? $venta->fecha_venta->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s'), // ← CORRECCIÓN AQUÍ
            'estado' => 'completado',
        ];

        // Agregar información del aliado si existe
        if ($aliadoData['has_aliado']) {
            $response['aliado'] = [
                'aliado_id' => $aliadoData['aliado_id'],
                'aliado_name' => $aliadoData['aliado_name'] ?? '',
                'discount_original' => $aliadoData['discount_original'],
                'comision_porcentaje' => $aliadoData['comision_porcentaje'],
                'monto_comision' => $aliadoData['monto_comision'],
                'monto_neto' => $aliadoData['monto_neto'],
                'payout_id' => $payout?->id,
                'estado_payout' => $payout?->estado
            ];
        }

        return $response;
    }

    /**
     * Obtiene mensaje de error amigable para el usuario
     */
    private function getUserFriendlyErrorMessage(\Exception $e, string $paymentType): string
    {
        $baseMessage = 'Error al procesar el pago. ';

        if (str_contains($e->getMessage(), '409')) {
            return $baseMessage . 'El sistema bancario reportó un conflicto. Por favor, intente nuevamente.';
        }

        if (str_contains($e->getMessage(), 'timeout')) {
            return $baseMessage . 'El tiempo de espera se agotó. Por favor, intente nuevamente.';
        }

        if (str_contains($e->getMessage(), 'validation')) {
            return $baseMessage . 'Por favor, verifique los datos e intente nuevamente.';
        }

        return $baseMessage . 'Por favor, intente nuevamente.';
    }
}
