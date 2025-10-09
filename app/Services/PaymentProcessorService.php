<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\Payout;
use App\Models\CompanyPayout;
use App\Models\Ally;
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
     * Procesa cualquier tipo de pago de forma simplificada
     */
    public function processPayment(
        Request $request,
        string $paymentType,
        array $bankValidationRules,
        callable $prepareBncData
    ): JsonResponse {
        Log::info("Solicitud recibida para procesar pago {$paymentType}", [
            'payment_type' => $paymentType,
            'aliado_id' => $request->aliado_id
        ]);

        DB::beginTransaction();
        try {
            // 1. Validar campos esenciales del banco
            $bankValidatedData = $request->validate($bankValidationRules);

            // 2. Validar aliado_id si existe
            $systemValidatedData = $request->validate([
                'aliado_id' => 'nullable|integer|exists:aliados,id',
                'descripcion' => 'nullable|string|max:255',
            ]);

            $validatedData = array_merge($bankValidatedData, $systemValidatedData);

            // 3. Preparar datos para BNC
            $bncData = $prepareBncData($validatedData);
            $bncData['Currency'] = 'BS'; // Moneda por defecto

            Log::debug("Datos para BNC", ['bnc_data' => $bncData]);

            // 4. Ejecutar pago en BNC
            $bncResponse = $this->executeBncPayment($paymentType, $bncData);

            // 5. Validar respuesta del BNC
            if (!$this->isBncResponseSuccessful($bncResponse)) {
                $errorMessage = $bncResponse['Message'] ?? "Fallo al procesar el pago {$paymentType}.";
                Log::error("Fallo BNC", ['response' => $bncResponse]);
                throw new \Exception($errorMessage);
            }

            // 6. Procesar datos del aliado (simplificado)
            $aliadoData = $this->processAliadoData($validatedData);

            // 7. Guardar venta básica
            $venta = $this->createSale($paymentType, $validatedData, $bncResponse, $aliadoData);

            // 8. Guardar payout si hay aliado
            $payout = null;
            if ($aliadoData['has_aliado']) {
                $payout = $this->payoutService->createPayout($venta, $aliadoData, $bncResponse);
            }

            DB::commit();

            Log::info("Pago {$paymentType} exitoso", [
                'venta_id' => $venta->id,
                'aliado_id' => $aliadoData['aliado_id'],
                'payout_id' => $payout?->id
            ]);

            return response()->json([
                'success' => true,
                'message' => "Pago {$paymentType} procesado exitosamente.",
                'data' => $this->buildSuccessResponse($venta, $payout, $bncResponse, $aliadoData)
            ], 200);
        } catch (ValidationException $e) {
            DB::rollBack();
            Log::warning("Error validación", ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Datos de entrada inválidos.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error en pago: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el pago. Por favor, intente nuevamente.',
                'error_details' => config('app.debug') ? $e->getMessage() : null,
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
     * Procesa datos del aliado (versión simplificada)
     */
    private function processAliadoData(array $validatedData): array
    {
        $aliadoData = [
            'has_aliado' => false,
            'aliado_id' => null,
            'aliado_name' => null,
            'comision_porcentaje' => 0,
            'monto_comision' => 0,
            'monto_neto' => $validatedData['Amount']
        ];

        if (!empty($validatedData['aliado_id'])) {
            $aliado = Ally::with('user')->find($validatedData['aliado_id']);

            if ($aliado) {
                $aliadoData['has_aliado'] = true;
                $aliadoData['aliado_id'] = $aliado->id;
                $aliadoData['aliado_name'] = $aliado->company_name ?? $aliado->user->name ?? 'Aliado';

                // Calcular comisión básica (10% por defecto para desarrollo)
                $aliadoData['comision_porcentaje'] = 10.0;
                $aliadoData['monto_comision'] = round($validatedData['Amount'] * 0.10, 2);
                $aliadoData['monto_neto'] = round($validatedData['Amount'] * 0.90, 2);
            }
        }

        return $aliadoData;
    }

    /**
     * Crea la venta básica
     */
    private function createSale(string $paymentType, array $validatedData, array $bncResponse, array $aliadoData): Sale
    {
        $baseData = [
            'aliado_id' => $validatedData['aliado_id'] ?? null,
            'monto_total' => $validatedData['Amount'],
            'monto_pagado' => $validatedData['Amount'],
            'referencia_banco' => $bncResponse['Reference'] ?? null,
            'transaction_id' => $bncResponse['TransactionIdentifier'] ?? null,
            'estado' => 'completado',
            'fecha_venta' => now(),
            'fecha_pago' => now(),
            'descripcion' => $validatedData['descripcion'] ?? "Pago {$paymentType} procesado",
            'codigo_autorizacion' => $bncResponse['AuthorizationCode'] ?? null,
            'respuesta_banco' => json_encode($bncResponse),
            'currency' => 'BS',
        ];

        // Agregar datos específicos del tipo de pago
        $specificData = match ($paymentType) {
            'c2p' => [
                'metodo_pago' => 'pago_movil',
                'banco_destino' => $validatedData['DebtorBankCode'],
                'telefono_cliente' => $validatedData['DebtorCellPhone'],
                'cedula_cliente' => $validatedData['DebtorID'],
            ],
            'card' => [
                'metodo_pago' => 'tarjeta_credito',
                'numero_tarjeta' => substr($validatedData['CardNumber'], -4),
                'nombre_titular' => $validatedData['CardHolderName'],
            ],
            'p2p' => [
                'metodo_pago' => 'transferencia_p2p',
                'banco_destino' => $validatedData['BeneficiaryBankCode'],
                'telefono_beneficiario' => $validatedData['BeneficiaryCellPhone'],
                'cedula_beneficiario' => $validatedData['BeneficiaryID'],
            ],
            default => ['metodo_pago' => $paymentType]
        };

        return Sale::create(array_merge($baseData, $specificData));
    }

    /**
     * Valida si la respuesta del BNC fue exitosa con detección de tipo de pago
     */
    private function isBncResponseSuccessful(array $bncResponse, string $paymentType = null): bool
    {
        Log::debug("Validando respuesta BNC para: {$paymentType}", ['response' => $bncResponse]);

        // Validaciones específicas por tipo de pago
        $validations = [
            'c2p' => function ($response) {
                return isset($response['Status']) && $response['Status'] === 'OK' &&
                    isset($response['Reference']) && !empty($response['Reference']);
            },
            'card' => function ($response) {
                return isset($response['TransactionIdentifier']) && !empty($response['TransactionIdentifier']) &&
                    isset($response['Reference']) && !empty($response['Reference']);
            },
            'p2p' => function ($response) {
                return isset($response['Reference']) && !empty($response['Reference']) &&
                    isset($response['AuthorizationCode']) && !empty($response['AuthorizationCode']);
            }
        ];

        // Si conocemos el tipo de pago, usar validación específica
        if ($paymentType && isset($validations[$paymentType])) {
            $isValid = $validations[$paymentType]($bncResponse);
            if ($isValid) {
                Log::info("Validación {$paymentType} exitosa");
                return true;
            }
        }

        // Validación genérica para cualquier tipo
        $genericValidations = [
            // Tiene Reference + algún código de autorización
            isset($bncResponse['Reference']) && !empty($bncResponse['Reference']) &&
                (isset($bncResponse['AuthorizationCode']) || isset($bncResponse['TransactionIdentifier'])),

            // Tiene Status OK + Reference
            isset($bncResponse['Status']) && $bncResponse['Status'] === 'OK' &&
                isset($bncResponse['Reference']) && !empty($bncResponse['Reference']),

            // Tiene success true
            isset($bncResponse['success']) && $bncResponse['success'] === true,

            // Tiene TransactionIdentifier + Reference (VPOS)
            isset($bncResponse['TransactionIdentifier']) && !empty($bncResponse['TransactionIdentifier']) &&
                isset($bncResponse['Reference']) && !empty($bncResponse['Reference'])
        ];

        foreach ($genericValidations as $validation) {
            if ($validation) {
                Log::info('Validación genérica exitosa', ['payment_type' => $paymentType]);
                return true;
            }
        }

        Log::warning('Validación BNC fallida', [
            'payment_type' => $paymentType,
            'response' => $bncResponse
        ]);

        return false;
    }

    /**
     * Construye respuesta de éxito simplificada
     */
    private function buildSuccessResponse(Sale $venta, ?Payout $payout, array $bncResponse, array $aliadoData): array
    {
        $response = [
            'venta' => [
                'id' => $venta->id,
                'monto_total' => $venta->monto_total,
                'referencia_banco' => $venta->referencia_banco,
                'transaction_id' => $venta->transaction_id,
                'metodo_pago' => $venta->metodo_pago,
                'estado' => $venta->estado,
                // CORRECCIÓN: Usar now() si fecha_venta es null
                'fecha_venta' => $venta->fecha_venta ? $venta->fecha_venta->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s'),
                'fecha_pago' => $venta->fecha_pago ? $venta->fecha_pago->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s'),
            ],
            'bnc_response' => [
                'Reference' => $bncResponse['Reference'] ?? null,
                'Status' => $bncResponse['Status'] ?? null,
            ]
        ];

        // Agregar información del aliado si existe
        if ($aliadoData['has_aliado']) {
            $response['aliado'] = [
                'aliado_id' => $aliadoData['aliado_id'],
                'aliado_name' => $aliadoData['aliado_name'],
                'comision_porcentaje' => $aliadoData['comision_porcentaje'],
                'monto_comision' => $aliadoData['monto_comision'],
                'monto_neto' => $aliadoData['monto_neto'],
                'payout_id' => $payout?->id,
            ];
        }

        return $response;
    }
}
