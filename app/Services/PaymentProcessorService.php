<?php

namespace App\Services;

use App\Models\Ally;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentTransaction;
use App\Models\Payout;
use App\Models\Sale;
use App\Models\User;
use App\Utils\JsonHelper;
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

            // 2. Validar datos del sistema
            $systemValidatedData = $request->validate([
                'user_id' => 'nullable|integer|exists:allies,user_id',
                'descripcion' => 'nullable|string|max:255',
            ]);

            $validatedData = array_merge($bankValidatedData, $systemValidatedData);

            // 3. Obtener usuario (OPCIONAL - puede ser null para pagos públicos)
            $user = auth()->user();
            $userId = $user ? $user->id : ($validatedData['user_id'] ?? null);

            Log::info("Usuario procesando pago", [
                'authenticated' => $user ? 'si' : 'no',
                'user_id' => $userId,
                'has_user_in_request' => isset($validatedData['user_id'])
            ]);

            // 4. Preparar datos para BNC
            $bncData = $prepareBncData($validatedData);
            $bncData['Currency'] = 'BS';

            // 5. CREAR PAYMENT (entidad base)
            $payment = Payment::create([
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            Log::info("✅ Payment creado", ['payment_id' => $payment->id]);

            // 6. CREAR ORDER
            $order = Order::create([
                'total' => $validatedData['Amount'],
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            Log::info("✅ Order creada", ['order_id' => $order->id]);

            // 7. Procesar aliado
            $aliadoData = $this->processAliadoData($validatedData);

            // 8. Validar errores de aliado
            if (!empty($aliadoData['aliado_errors']) && $aliadoData['has_aliado']) {
                Log::warning("Problemas con aliado, pero continuando con pago", [
                    'user_id' => $aliadoData['user_id'],
                    'errors' => $aliadoData['aliado_errors']
                ]);
                $aliadoData['has_aliado'] = false;
                $aliadoData['discount'] = 0;
                $aliadoData['comision_porcentaje'] = 0;
                $aliadoData['monto_comision'] = 0;
                $aliadoData['monto_neto'] = $validatedData['Amount'];
            }

            // 9. CREAR PAYMENT TRANSACTION (con userId opcional)
            $transaction = $this->createPaymentTransaction(
                $paymentType,
                $validatedData,
                $userId,
                $aliadoData,
                $payment->id,
                $order->id
            );
            Log::info("✅ PaymentTransaction creada", [
                'transaction_id' => $transaction->id,
                'reference' => $transaction->reference_code,
                'user_id' => $transaction->user_id
            ]);

            // 10. Ejecutar pago en BNC
            $bncResponse = $this->executeBncPayment($paymentType, $bncData);

            // 11. Validar respuesta del BNC
            if (!$this->isBncResponseSuccessful($bncResponse, $paymentType)) {
                $this->updateFailedTransaction($transaction, $order, $bncResponse);
                throw new \Exception("Fallo al procesar el pago {$paymentType}.");
            }

            // 12. Actualizar transacción con respuesta BNC
            $this->updateTransactionWithBncResponse($transaction, $bncResponse);

            // 13. Actualizar ORDER a completada
            $order->update(['status' => 'completed']);

            // 14. Guardar venta
            $venta = $this->createSale(
                $paymentType,
                $validatedData,
                $bncResponse,
                $aliadoData,
                $transaction,
                $order,
                $payment
            );

            // 15. Crear payout si aplica
            $payout = null;
            if ($aliadoData['has_aliado'] && $aliadoData['aliado_valid']) {
                Log::debug("Creando payout para aliado válido", [
                    'user_id' => $aliadoData['user_id'],
                    'aliado_id' => $aliadoData['aliado_id'],
                    'discount' => $aliadoData['discount'],
                    'comision' => $aliadoData['comision_porcentaje']
                ]);
                $payout = $this->payoutService->createPayout($venta, $aliadoData, $bncResponse);

                $transaction->update([
                    'payout_id' => $payout->id,
                    'status' => 'confirmed'
                ]);
            } else {
                $transaction->update(['status' => 'confirmed']);
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
                'data' => $this->buildSuccessResponse(
                    $venta,
                    $payout,
                    $bncResponse,
                    $aliadoData,
                    $transaction,
                    $order,
                    $payment
                )
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
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el pago. Por favor, intente nuevamente.',
                'error_details' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Crear PaymentTransaction (acepta userId nullable)
     */
    private function createPaymentTransaction(
        string $paymentType,
        array $validatedData,
        ?int $userId,
        array $aliadoData,
        int $paymentId,
        int $orderId
    ): PaymentTransaction {
        $confirmationData = [
            'request_data' => $validatedData,
            'payment_type' => $paymentType,
            'payment_id' => $paymentId,
            'order_id' => $orderId,
            'aliado_data' => $aliadoData,
            'created_at' => now()->toDateTimeString()
        ];

        return PaymentTransaction::create([
            'user_id' => $userId,
            'ally_id' => $aliadoData['aliado_id'] ?? null,
            'original_amount' => $validatedData['Amount'],
            'discount_percentage' => $aliadoData['discount'] ?? 0,
            'amount_to_ally' => $aliadoData['monto_neto'] ?? 0,
            'platform_commission' => $aliadoData['monto_comision'] ?? 0,
            'payment_method' => $this->mapPaymentMethod($paymentType),
            'status' => 'pending_manual_confirmation',
            'reference_code' => $this->generateReferenceCode(),
            'confirmation_data' => JsonHelper::encode($confirmationData),
        ]);
    }

    /**
     * Actualizar transacción con respuesta BNC
     */
    private function updateTransactionWithBncResponse(PaymentTransaction $transaction, array $bncResponse): void
    {
        $currentData = JsonHelper::decode($transaction->confirmation_data);
        $currentData['bnc_response'] = $bncResponse;
        $currentData['bnc_response_time'] = now()->toDateTimeString();

        $transaction->update([
            'confirmation_data' => JsonHelper::encode($currentData)
        ]);
    }

    /**
     * Actualizar transacción fallida
     */
    private function updateFailedTransaction(PaymentTransaction $transaction, Order $order, array $bncResponse): void
    {
        $currentData = JsonHelper::decode($transaction->confirmation_data);
        $currentData['bnc_response'] = $bncResponse;
        $currentData['error'] = 'Pago fallido';
        $currentData['error_time'] = now()->toDateTimeString();

        $transaction->update([
            'status' => 'failed',
            'confirmation_data' => JsonHelper::encode($currentData)
        ]);

        $order->update(['status' => 'failed']);
    }

    /**
     * Mapear método de pago
     */
    private function mapPaymentMethod(string $paymentType): string
    {
        return match ($paymentType) {
            'c2p' => 'pago_movil',
            'card' => 'tarjeta_credito',
            'p2p' => 'transferencia_bancaria',
            default => $paymentType
        };
    }

    /**
     * Generar código de referencia único
     */
    private function generateReferenceCode(): string
    {
        do {
            $reference = 'TXN-' . strtoupper(substr(md5(uniqid()), 0, 12));
        } while (PaymentTransaction::where('reference_code', $reference)->exists());

        return $reference;
    }

    /**
     * Ejecuta el pago según el tipo
     */
    private function executeBncPayment(string $paymentType, array $bncData): array
    {
        try {
            Log::info("🔵 ENVIANDO A BNC - {$paymentType}", [
                'bnc_data_keys' => array_keys($bncData),
                'bnc_data_sanitized' => $this->sanitizeSensitiveData($bncData)
            ]);

            // Verificar que BncApiService tenga token válido
            if (!$this->bncApiService->hasWorkingKey()) {
                Log::warning("🟡 BNC - No hay WorkingKey válido, intentando obtener...");
                
                $encryptedToken = $this->bncApiService->getSessionToken();
                if ($encryptedToken) {
                    $workingKey = $this->bncApiService->processSessionToken($encryptedToken);
                    Log::info("🟢 BNC - WorkingKey obtenido: " . ($workingKey ? 'OK' : 'FALLÓ'));
                } else {
                    Log::error("🔴 BNC - No se pudo obtener token de sesión");
                }
            }

            $response = match ($paymentType) {
                'c2p' => $this->bncApiService->initiateC2PPayment($bncData),
                'card' => $this->bncApiService->processCardPayment($bncData),
                'p2p' => $this->bncApiService->validateP2PPayment($bncData),
                default => throw new \Exception("Tipo de pago no soportado: {$paymentType}")
            };

            // Log de la respuesta completa (sanitizada)
            Log::info("🔵 RESPUESTA DE BNC - {$paymentType}", [
                'response_type' => gettype($response),
                'response_status' => $response['Status'] ?? ($response['success'] ?? 'unknown'),
                'response_message' => $response['Message'] ?? ($response['message'] ?? 'Sin mensaje'),
                'response_code' => $response['Code'] ?? ($response['code'] ?? 'N/A'),
                'has_reference' => isset($response['Reference']) ? 'SI' : 'NO',
                'has_transaction_id' => isset($response['TransactionIdentifier']) ? 'SI' : 'NO',
                'has_auth_code' => isset($response['AuthorizationCode']) ? 'SI' : 'NO',
            ]);

            // Verificar si la respuesta es exitosa
            if (isset($response['Status']) && $response['Status'] !== 'OK') {
                Log::error("🔴 BNC - Error en respuesta: " . ($response['Message'] ?? 'Error desconocido'));
            }

            return $response;
            
        } catch (\Exception $e) {
            Log::error("🔴 ERROR EN BNC - {$paymentType}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Sanitiza datos sensibles para logs
     */
    private function sanitizeSensitiveData(array $data): array
    {
        $sanitized = [];
        foreach ($data as $key => $value) {
            if (in_array($key, ['CardNumber', 'CVV', 'CardPIN', 'Token'])) {
                $value = (string)$value;
                $sanitized[$key] = strlen($value) > 8 
                    ? substr($value, 0, 4) . '****' . substr($value, -4)
                    : '****';
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitizeSensitiveData($value);
            } else {
                $sanitized[$key] = $value;
            }
        }
        return $sanitized;
    }

    /**
     * Procesa datos del aliado con COMISIÓN MANUAL según descuento
     */
    private function processAliadoData(array $validatedData): array
    {
        $aliadoData = [
            'has_aliado' => false,
            'aliado_id' => null,
            'user_id' => null,
            'aliado_name' => null,
            'aliado_status' => null,
            'discount' => 0,
            'comision_porcentaje' => 0,
            'monto_comision' => 0,
            'monto_despues_descuento' => $validatedData['Amount'],
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

                        // Obtener descuento del aliado
                        $aliadoData['discount'] = $this->parseDiscount($aliado->discount);

                        // Determinar comisión según descuento
                        $aliadoData['comision_porcentaje'] = $this->determineCommission($aliadoData['discount']);

                        // Cálculos detallados
                        $aliadoData['monto_despues_descuento'] = $validatedData['Amount'] * (1 - ($aliadoData['discount'] / 100));
                        $aliadoData['monto_comision'] = round(
                            $aliadoData['monto_despues_descuento'] * ($aliadoData['comision_porcentaje'] / 100),
                            2
                        );
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
     * Determina la comisión manual según el descuento del aliado
     */
    private function determineCommission(float $discountAliado): float
    {
        $comision = match (true) {
            $discountAliado >= 0 && $discountAliado <= 10 => 15.0,
            $discountAliado > 10 && $discountAliado <= 20 => 12.0,
            $discountAliado > 20 && $discountAliado <= 30 => 10.0,
            $discountAliado > 30 => 8.0,
            default => 15.0
        };

        Log::debug("Comisión determinada", [
            'discount_aliado' => $discountAliado,
            'comision_calculada' => $comision
        ]);

        return $comision;
    }

    /**
     * Convierte el discount string a float
     */
    private function parseDiscount(?string $discount): float
    {
        if (empty($discount)) {
            return 0.0;
        }

        $cleanDiscount = str_replace(['%', ' '], '', $discount);
        return (float) $cleanDiscount;
    }

    /**
     * Crea la venta con relaciones a payment, order y transaction
     */
    private function createSale(
        string $paymentType,
        array $validatedData,
        array $bncResponse,
        array $aliadoData,
        PaymentTransaction $transaction,
        Order $order,
        Payment $payment
    ): Sale {
        $baseData = [
            'aliado_id' => $aliadoData['aliado_id'] ?? null,
            'monto_total' => $validatedData['Amount'],
            'monto_pagado' => $validatedData['Amount'],
            'referencia_banco' => $bncResponse['Reference'] ?? null,
            'transaction_id' => $bncResponse['TransactionIdentifier'] ?? null,
            'estado' => 'completado',
            'fecha_venta' => now(),
            'fecha_pago' => now(),
            'descripcion' => $validatedData['descripcion'] ?? "Pago {$paymentType} procesado",
            'codigo_autorizacion' => $bncResponse['AuthorizationCode'] ?? null,
            'respuesta_banco' => JsonHelper::encode($bncResponse),
            'currency' => 'BS',
            'payment_transaction_id' => $transaction->id,
            'order_id' => $order->id,
            'payment_id' => $payment->id,
        ];

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
        Log::debug("Validando respuesta BNC para: {$paymentType}", ['response' => $bncResponse]);

        // Primero verificar si hay error explícito
        if (isset($bncResponse['Status']) && $bncResponse['Status'] !== 'OK') {
            Log::warning("🔴 BNC respondió con Status: {$bncResponse['Status']}");
            return false;
        }

        if (isset($bncResponse['success']) && $bncResponse['success'] === false) {
            Log::warning("🔴 BNC respondió con success: false");
            return false;
        }

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
                $hasTransactionId = isset($response['TransactionIdentifier']) && !empty($response['TransactionIdentifier']);
                $hasReference = isset($response['Reference']) && !empty($response['Reference']);
                
                if (!$hasTransactionId) {
                    Log::warning("🔴 Falta TransactionIdentifier en respuesta BNC");
                }
                if (!$hasReference) {
                    Log::warning("🔴 Falta Reference en respuesta BNC");
                }
                
                return $hasTransactionId && $hasReference;
            },
            'p2p' => function ($response) {
                $hasReference = isset($response['Reference']) && !empty($response['Reference']);
                $hasAuthCode = isset($response['AuthorizationCode']) && !empty($response['AuthorizationCode']);
                
                if (!$hasReference) {
                    Log::warning("🔴 Falta Reference en respuesta BNC");
                }
                if (!$hasAuthCode) {
                    Log::warning("🔴 Falta AuthorizationCode en respuesta BNC");
                }
                
                return $hasReference && $hasAuthCode;
            }
        ];

        if ($paymentType && isset($validations[$paymentType])) {
            $isValid = $validations[$paymentType]($bncResponse);
            if ($isValid) {
                Log::info("✅ Validación {$paymentType} exitosa");
                return true;
            } else {
                Log::warning("🔴 Validación específica para {$paymentType} falló");
            }
        }

        // Validaciones genéricas como fallback
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
                Log::info("✅ Validación genérica exitosa: {$validationName}");
                return true;
            }
        }

        Log::warning('🔴 Todas las validaciones BNC fallaron');
        return false;
    }

    /**
     * Construye respuesta de éxito con todas las entidades
     */
    private function buildSuccessResponse(
        Sale $venta,
        ?Payout $payout,
        array $bncResponse,
        array $aliadoData,
        PaymentTransaction $transaction,
        Order $order,
        Payment $payment
    ): array {
        $response = [
            'venta' => [
                'id' => $venta->id,
                'monto_total' => $venta->monto_total,
                'referencia_banco' => $venta->referencia_banco,
                'transaction_id' => $venta->transaction_id,
                'metodo_pago' => $venta->metodo_pago,
                'estado' => $venta->estado,
                'fecha_venta' => $venta->fecha_venta ? $venta->fecha_venta->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s'),
            ],
            'payment_transaction' => [
                'id' => $transaction->id,
                'reference_code' => $transaction->reference_code,
                'status' => $transaction->status,
                'original_amount' => $transaction->original_amount,
                'amount_to_ally' => $transaction->amount_to_ally,
                'platform_commission' => $transaction->platform_commission,
                'payment_method' => $transaction->payment_method,
            ],
            'order' => [
                'id' => $order->id,
                'total' => $order->total,
                'status' => $order->status,
                'created_at' => $order->created_at->format('Y-m-d H:i:s'),
            ],
            'payment' => [
                'id' => $payment->id,
                'created_at' => $payment->created_at->format('Y-m-d H:i:s'),
            ],
            'bnc_response' => [
                'Reference' => $bncResponse['Reference'] ?? null,
                'Status' => $bncResponse['Status'] ?? null,
            ]
        ];

        if ($aliadoData['has_aliado']) {
            $response['aliado'] = [
                'aliado_id' => $aliadoData['aliado_id'],
                'aliado_name' => $aliadoData['aliado_name'],
                'discount' => $aliadoData['discount'],
                'comision_porcentaje' => $aliadoData['comision_porcentaje'],
                'monto_comision' => $aliadoData['monto_comision'],
                'monto_despues_descuento' => $aliadoData['monto_despues_descuento'],
                'monto_neto' => $aliadoData['monto_neto'],
                'payout_id' => $payout?->id,
            ];
        }

        return $response;
    }
}
