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
        Log::info("=== INICIANDO PROCESAMIENTO PAGO {$paymentType} ===", [
            'request_data' => $request->all(),
            'payment_type' => $paymentType
        ]);

        DB::beginTransaction();
        try {
            // 1. Validar campos esenciales del banco
            Log::debug("Validando campos bancarios para {$paymentType}");
            $bankValidatedData = $request->validate($bankValidationRules);
            Log::debug("Validación bancaria exitosa", $bankValidatedData);

            // 2. Validar aliado_id si existe
            $systemValidatedData = $request->validate([
                'aliado_id' => 'nullable|integer|exists:aliados,id',
                'descripcion' => 'nullable|string|max:255',
            ]);
            Log::debug("Validación sistema exitosa", $systemValidatedData);

            $validatedData = array_merge($bankValidatedData, $systemValidatedData);

            // 3. Preparar datos para BNC
            Log::debug("Preparando datos BNC para {$paymentType}");
            $bncData = $prepareBncData($validatedData);
            $bncData['Currency'] = 'BS';
            Log::debug("Datos BNC preparados", $bncData);

            // 4. Ejecutar pago en BNC
            Log::info("Ejecutando pago {$paymentType} en BNC");
            $bncResponse = $this->executeBncPayment($paymentType, $bncData);
            Log::info("Respuesta BNC recibida", [
                'payment_type' => $paymentType,
                'bnc_response' => $bncResponse
            ]);

            // 5. Validar respuesta del BNC - CON MÁS DETALLE
            Log::debug("Validando respuesta BNC para {$paymentType}");
            if (!$this->isBncResponseSuccessful($bncResponse, $paymentType)) {
                $errorMessage = $bncResponse['Message'] ?? $bncResponse['message'] ?? "Fallo al procesar el pago {$paymentType}.";
                Log::error("VALIDACIÓN BNC FALLIDA para {$paymentType}", [
                    'bnc_response' => $bncResponse,
                    'payment_type' => $paymentType,
                    'error_message' => $errorMessage
                ]);
                throw new \Exception($errorMessage);
            }
            Log::info("Validación BNC exitosa para {$paymentType}");

            // 6. Procesar datos del aliado
            Log::debug("Procesando datos del aliado");
            $aliadoData = $this->processAliadoData($validatedData);
            Log::debug("Datos aliado procesados", $aliadoData);

            // 7. Guardar venta
            Log::debug("Creando registro de venta");
            $venta = $this->createSale($paymentType, $validatedData, $bncResponse, $aliadoData);
            Log::info("Venta creada exitosamente", ['venta_id' => $venta->id]);

            // 8. Guardar payout si hay aliado
            $payout = null;
            if ($aliadoData['has_aliado']) {
                Log::debug("Creando payout para aliado", ['aliado_id' => $aliadoData['aliado_id']]);
                $payout = $this->payoutService->createPayout($venta, $aliadoData, $bncResponse);
                Log::info("Payout creado exitosamente", ['payout_id' => $payout?->id]);
            } else {
                Log::debug("No hay aliado, no se crea payout");
            }

            DB::commit();

            Log::info("=== PAGO {$paymentType} PROCESADO EXITOSAMENTE ===", [
                'venta_id' => $venta->id,
                'payout_id' => $payout?->id,
                'aliado_id' => $aliadoData['aliado_id']
            ]);

            return response()->json([
                'success' => true,
                'message' => "Pago {$paymentType} procesado exitosamente.",
                'data' => $this->buildSuccessResponse($venta, $payout, $bncResponse, $aliadoData)
            ], 200);
        } catch (ValidationException $e) {
            DB::rollBack();
            Log::error("ERROR DE VALIDACIÓN en {$paymentType}", [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Datos de entrada inválidos.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("ERROR GENERAL en pago {$paymentType}: " . $e->getMessage(), [
                'payment_type' => $paymentType,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString() // ← ESTO ES IMPORTANTE
            ]);

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

        // Validaciones específicas por tipo de pago - MÁS FLEXIBLES
        $validations = [
            'c2p' => function ($response) {
                // Para C2P: Status OK O tiene Reference (algunas respuestas pueden no tener Status)
                $hasStatusOK = isset($response['Status']) && $response['Status'] === 'OK';
                $hasReference = isset($response['Reference']) && !empty($response['Reference']);
                $hasAuthCode = isset($response['AuthorizationCode']) && !empty($response['AuthorizationCode']);

                // C2P es exitoso si: (Status OK Y Reference) O (Reference Y AuthCode) O solo Reference
                return ($hasStatusOK && $hasReference) ||
                    ($hasReference && $hasAuthCode) ||
                    $hasReference; // Solo con Reference ya es válido para C2P
            },
            'card' => function ($response) {
                // Para VPOS: TransactionIdentifier + Reference
                return isset($response['TransactionIdentifier']) && !empty($response['TransactionIdentifier']) &&
                    isset($response['Reference']) && !empty($response['Reference']);
            },
            'p2p' => function ($response) {
                // Para P2P: Reference + AuthorizationCode
                return isset($response['Reference']) && !empty($response['Reference']) &&
                    isset($response['AuthorizationCode']) && !empty($response['AuthorizationCode']);
            }
        ];

        // Si conocemos el tipo de pago, usar validación específica
        if ($paymentType && isset($validations[$paymentType])) {
            $isValid = $validations[$paymentType]($bncResponse);
            if ($isValid) {
                Log::info("Validación {$paymentType} exitosa", [
                    'criteria_met' => 'validacion_especifica',
                    'response_keys' => array_keys($bncResponse)
                ]);
                return true;
            } else {
                Log::warning("Validación específica {$paymentType} fallida", [
                    'response' => $bncResponse,
                    'expected_c2p' => 'Status: OK + Reference O Reference + AuthCode O solo Reference'
                ]);
            }
        }

        // Validación genérica MEJORADA para cualquier tipo
        $genericValidations = [
            // 1. Tiene Reference (campo más importante)
            'has_reference' => isset($bncResponse['Reference']) && !empty($bncResponse['Reference']),

            // 2. Tiene Status OK + algún identificador
            'has_status_ok' => isset($bncResponse['Status']) && $bncResponse['Status'] === 'OK' &&
                (isset($bncResponse['Reference']) || isset($bncResponse['TransactionIdentifier'])),

            // 3. Tiene TransactionIdentifier + Reference (VPOS)
            'has_transaction_id' => isset($bncResponse['TransactionIdentifier']) && !empty($bncResponse['TransactionIdentifier']) &&
                isset($bncResponse['Reference']) && !empty($bncResponse['Reference']),

            // 4. Tiene AuthorizationCode + Reference (P2P/C2P)
            'has_auth_code' => isset($bncResponse['AuthorizationCode']) && !empty($bncResponse['AuthorizationCode']) &&
                isset($bncResponse['Reference']) && !empty($bncResponse['Reference']),

            // 5. Tiene success true
            'has_success_true' => isset($bncResponse['success']) && $bncResponse['success'] === true,

            // 6. Para C2P: Solo Reference puede ser suficiente
            'c2p_reference_only' => $paymentType === 'c2p' &&
                isset($bncResponse['Reference']) && !empty($bncResponse['Reference'])
        ];

        foreach ($genericValidations as $validationName => $validation) {
            if ($validation) {
                Log::info('Validación genérica exitosa', [
                    'payment_type' => $paymentType,
                    'validation_criteria' => $validationName,
                    'response_keys' => array_keys($bncResponse)
                ]);
                return true;
            }
        }

        Log::warning('Validación BNC fallida', [
            'payment_type' => $paymentType,
            'response' => $bncResponse,
            'available_keys' => array_keys($bncResponse)
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
