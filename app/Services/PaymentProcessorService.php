<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\Payout;
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
            $bankValidatedData = $request->validate($bankValidationRules);

            // 2. ✅ VALIDACIÓN MEJORADA - BUSCAR USER_ID EN TABLA ALLIES
            $systemValidatedData = $request->validate([
                'user_id' => 'nullable|integer|exists:allies,user_id',
                'descripcion' => 'nullable|string|max:255',
            ]);

            $validatedData = array_merge($bankValidatedData, $systemValidatedData);

            // 3. Preparar datos para BNC
            $bncData = $prepareBncData($validatedData);
            $bncData['Currency'] = 'BS';

            // 4. Ejecutar pago en BNC
            $bncResponse = $this->executeBncPayment($paymentType, $bncData);

            // 5. Validar respuesta del BNC
            if (!$this->isBncResponseSuccessful($bncResponse, $paymentType)) {
                throw new \Exception("Fallo al procesar el pago {$paymentType}.");
            }

            // 6. ✅ PROCESAR ALIADO POR USER_ID CON COMISIÓN MANUAL
            $aliadoData = $this->processAliadoData($validatedData);

            // ✅ VALIDAR SI HAY ERRORES CON EL ALIADO
            if (!empty($aliadoData['aliado_errors']) && $aliadoData['has_aliado']) {
                Log::warning("Problemas con aliado, pero continuando con pago", [
                    'user_id' => $aliadoData['user_id'],
                    'errors' => $aliadoData['aliado_errors']
                ]);
                // Continuar sin comisión si hay problemas con el aliado
                $aliadoData['has_aliado'] = false;
                $aliadoData['discount'] = 0;
                $aliadoData['comision_porcentaje'] = 0;
                $aliadoData['monto_comision'] = 0;
                $aliadoData['monto_neto'] = $validatedData['Amount'];
            }

            // 7. Guardar venta
            $venta = $this->createSale($paymentType, $validatedData, $bncResponse, $aliadoData);

            // 8. ✅ SOLO CREAR PAYOUT SI ALIADO ES VÁLIDO
            $payout = null;
            if ($aliadoData['has_aliado'] && $aliadoData['aliado_valid']) {
                Log::debug("Creando payout para aliado válido", [
                    'user_id' => $aliadoData['user_id'],
                    'aliado_id' => $aliadoData['aliado_id'],
                    'discount' => $aliadoData['discount'],
                    'comision' => $aliadoData['comision_porcentaje']
                ]);
                $payout = $this->payoutService->createPayout($venta, $aliadoData, $bncResponse);
            } else {
                Log::debug("No se crea payout - aliado no válido o no existe", [
                    'has_aliado' => $aliadoData['has_aliado'],
                    'aliado_valid' => $aliadoData['aliado_valid'] ?? false,
                    'user_id' => $aliadoData['user_id']
                ]);
            }

            DB::commit();

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
            Log::error("ERROR GENERAL en pago {$paymentType}: " . $e->getMessage());
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
        try {
            Log::debug("Ejecutando pago BNC: {$paymentType}", [
                'bnc_data_keys' => array_keys($bncData)
            ]);

            $response = match ($paymentType) {
                'c2p' => $this->bncApiService->initiateC2PPayment($bncData),
                'card' => $this->bncApiService->processCardPayment($bncData),
                'p2p' => $this->bncApiService->validateP2PPayment($bncData),
                default => throw new \Exception("Tipo de pago no soportado: {$paymentType}")
            };

            Log::debug("Respuesta BNC ejecutada", [
                'payment_type' => $paymentType,
                'response_type' => gettype($response),
                'response_keys' => is_array($response) ? array_keys($response) : 'NOT_ARRAY'
            ]);

            return $response;
        } catch (\Exception $e) {
            Log::error("Error ejecutando pago BNC {$paymentType}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * ✅ ACTUALIZADO: Procesa datos del aliado con COMISIÓN MANUAL según descuento
     */
    private function processAliadoData(array $validatedData): array
    {
        $aliadoData = [
            'has_aliado' => false,
            'aliado_id' => null,
            'user_id' => null,
            'aliado_name' => null,
            'aliado_status' => null,
            'discount' => 0, // ✅ DESCUENTO DEL ALIADO
            'comision_porcentaje' => 0, // ✅ COMISIÓN MANUAL
            'monto_comision' => 0,
            'monto_despues_descuento' => $validatedData['Amount'], // ✅ NUEVO
            'monto_neto' => $validatedData['Amount'],
            'aliado_valid' => false,
            'aliado_errors' => []
        ];

        if (!empty($validatedData['user_id'])) {
            try {
                Log::debug("Buscando aliado por user_id en BD", ['user_id' => $validatedData['user_id']]);

                $aliado = Ally::with('user')
                    ->where('user_id', $validatedData['user_id'])
                    ->first();

                if ($aliado) {
                    Log::debug("Aliado encontrado por user_id", [
                        'aliado_id' => $aliado->id,
                        'user_id' => $aliado->user_id,
                        'company_name' => $aliado->company_name,
                        'discount' => $aliado->discount,
                        'status' => $aliado->status
                    ]);

                    if ($aliado->isActive()) {
                        $aliadoData['has_aliado'] = true;
                        $aliadoData['aliado_id'] = $aliado->id;
                        $aliadoData['user_id'] = $aliado->user_id;
                        $aliadoData['aliado_name'] = $aliado->company_name ?? $aliado->user->name ?? 'Aliado';
                        $aliadoData['aliado_status'] = $aliado->status;
                        $aliadoData['aliado_valid'] = true;

                        // ✅ OBTENER DESCUENTO DEL ALIADO
                        $aliadoData['discount'] = $this->parseDiscount($aliado->discount);

                        // ✅ DETERMINAR COMISIÓN MANUAL SEGÚN DESCUENTO
                        $aliadoData['comision_porcentaje'] = $this->determineCommission($aliadoData['discount']);

                        // ✅ CÁLCULOS DETALLADOS
                        // 1. Aplicar descuento del aliado
                        $aliadoData['monto_despues_descuento'] = $validatedData['Amount'] * (1 - ($aliadoData['discount'] / 100));
                        
                        // 2. Calcular tu comisión sobre el monto después del descuento
                        $aliadoData['monto_comision'] = round(
                            $aliadoData['monto_despues_descuento'] * ($aliadoData['comision_porcentaje'] / 100), 
                            2
                        );
                        
                        // 3. Calcular lo que recibe el aliado
                        $aliadoData['monto_neto'] = round(
                            $aliadoData['monto_despues_descuento'] - $aliadoData['monto_comision'], 
                            2
                        );

                        Log::info("✅ Cálculos con comisión manual completados", [
                            'user_id' => $aliado->user_id,
                            'aliado_id' => $aliado->id,
                            'discount_aliado' => $aliadoData['discount'] . '%',
                            'comision_plataforma' => $aliadoData['comision_porcentaje'] . '%',
                            'monto_original' => $validatedData['Amount'],
                            'monto_despues_descuento' => $aliadoData['monto_despues_descuento'],
                            'comision_monto' => $aliadoData['monto_comision'],
                            'monto_neto_aliado' => $aliadoData['monto_neto'],
                            'resumen' => "Cliente paga: {$validatedData['Amount']} | " .
                                        "Descuento: {$aliadoData['discount']}% | " .
                                        "Tu comisión: {$aliadoData['comision_porcentaje']}% | " .
                                        "Aliado recibe: {$aliadoData['monto_neto']}"
                        ]);

                    } else {
                        $aliadoData['aliado_errors'][] = "Aliado inactivo o suspendido: {$aliado->status}";
                        Log::warning("Aliado no activo por user_id", [
                            'user_id' => $validatedData['user_id'],
                            'status' => $aliado->status
                        ]);
                    }
                } else {
                    $aliadoData['aliado_errors'][] = "No se encontró aliado para el user_id: {$validatedData['user_id']}";
                    Log::warning("Aliado no encontrado por user_id", ['user_id' => $validatedData['user_id']]);
                }
            } catch (\Exception $e) {
                $aliadoData['aliado_errors'][] = "Error al consultar aliado por user_id: " . $e->getMessage();
                Log::error("Error consultando aliado por user_id", [
                    'user_id' => $validatedData['user_id'],
                    'error' => $e->getMessage()
                ]);
            }
        } else {
            Log::debug("No se proporcionó user_id, procesando como pago directo");
        }

        return $aliadoData;
    }

    /**
     * ✅ NUEVO: Determina la comisión manual según el descuento del aliado
     */
    private function determineCommission(float $discountAliado): float
    {
        // ✅ TABLA DE COMISIONES MANUALES SEGÚN DESCUENTO
        $comision = match(true) {
            $discountAliado >= 0 && $discountAliado <= 10 => 15.0,   // 15% si descuento 0-10%
            $discountAliado > 10 && $discountAliado <= 20 => 12.0,   // 12% si descuento 11-20%
            $discountAliado > 20 && $discountAliado <= 30 => 10.0,   // 10% si descuento 21-30%
            $discountAliado > 30 => 8.0,                            // 8% si descuento >30%
            default => 15.0
        };

        Log::debug("Comisión determinada", [
            'discount_aliado' => $discountAliado,
            'comision_calculada' => $comision
        ]);

        return $comision;
    }

    /**
     * ✅ NUEVO: Convierte el discount string a float
     */
    private function parseDiscount(?string $discount): float
    {
        if (empty($discount)) {
            return 0.0;
        }
        
        // Convierte "15%" a 15.0, "10" a 10.0, etc.
        $cleanDiscount = str_replace(['%', ' '], '', $discount);
        
        return (float) $cleanDiscount;
    }

    /**
     * Crea la venta básica
     */
    private function createSale(string $paymentType, array $validatedData, array $bncResponse, array $aliadoData): Sale
    {
        $baseData = [
            'aliado_id' => $aliadoData['aliado_id'] ?? null, // ✅ USAR aliado_id REAL
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
     * Valida si la respuesta del BNC fue exitosa
     */
    private function isBncResponseSuccessful(array $bncResponse, string $paymentType = null): bool
    {
        // ... (mantener misma lógica de validación)
        Log::debug("Validando respuesta BNC para: {$paymentType}", ['response' => $bncResponse]);

        $validations = [
            'c2p' => function ($response) {
                $hasStatusOK = isset($response['Status']) && $response['Status'] === 'OK';
                $hasReference = isset($response['Reference']) && !empty($response['Reference']);
                $hasAuthCode = isset($response['AuthorizationCode']) && !empty($response['AuthorizationCode']);

                return ($hasStatusOK && $hasReference) ||
                    ($hasReference && $hasAuthCode) ||
                    $hasReference;
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

        if ($paymentType && isset($validations[$paymentType])) {
            $isValid = $validations[$paymentType]($bncResponse);
            if ($isValid) {
                Log::info("Validación {$paymentType} exitosa");
                return true;
            }
        }

        // Validación genérica
        $genericValidations = [
            'has_reference' => isset($bncResponse['Reference']) && !empty($bncResponse['Reference']),
            'has_status_ok' => isset($bncResponse['Status']) && $bncResponse['Status'] === 'OK' &&
                (isset($bncResponse['Reference']) || isset($bncResponse['TransactionIdentifier'])),
            'has_transaction_id' => isset($bncResponse['TransactionIdentifier']) && !empty($bncResponse['TransactionIdentifier']) &&
                isset($bncResponse['Reference']) && !empty($bncResponse['Reference']),
            'has_auth_code' => isset($bncResponse['AuthorizationCode']) && !empty($bncResponse['AuthorizationCode']) &&
                isset($bncResponse['Reference']) && !empty($bncResponse['Reference']),
            'has_success_true' => isset($bncResponse['success']) && $bncResponse['success'] === true,
            'c2p_reference_only' => $paymentType === 'c2p' &&
                isset($bncResponse['Reference']) && !empty($bncResponse['Reference'])
        ];

        foreach ($genericValidations as $validationName => $validation) {
            if ($validation) {
                Log::info('Validación genérica exitosa', ['validation_criteria' => $validationName]);
                return true;
            }
        }

        Log::warning('Validación BNC fallida');
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
                'discount' => $aliadoData['discount'], // ✅ NUEVO
                'comision_porcentaje' => $aliadoData['comision_porcentaje'],
                'monto_comision' => $aliadoData['monto_comision'],
                'monto_despues_descuento' => $aliadoData['monto_despues_descuento'], // ✅ NUEVO
                'monto_neto' => $aliadoData['monto_neto'],
                'payout_id' => $payout?->id,
            ];
        }

        return $response;
    }
}