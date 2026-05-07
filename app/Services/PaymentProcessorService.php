<?php

namespace App\Services;

use App\Models\Ally;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Payout;
use App\Models\Sale;
use App\Models\PaymentTransaction;
use App\Services\BncApiService;
use App\Services\PayoutService;
use App\Helpers\JsonHelper;
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
            $systemRules = [
                'user_id' => 'nullable|integer|exists:allies,user_id',
                'descripcion' => 'nullable|string|max:255',
                'request_id' => 'nullable|string|max:50',
            ];

            if ($paymentType === 'debito_emitir') {
                $systemRules['codigo_sms'] = 'required|string|size:8';
            }

            $systemValidatedData = $request->validate($systemRules);

            $validatedData = array_merge($bankValidatedData, $systemValidatedData);

            // Detectar tipo de débito (cuenta o teléfono)
            $tipoDebito = $this->detectarTipoDebito($validatedData);
            if ($tipoDebito) {
                Log::info("📱 Tipo de débito detectado: {$tipoDebito}", [
                    'DebtorAccountType' => $validatedData['DebtorAccountType'] ?? $validatedData['DebtorAccType'] ?? null,
                    'DebtorAccount' => $this->maskAccountNumber($validatedData['DebtorAccount'] ?? '')
                ]);
            }

            if ($tipoDebito === 'TELÉFONO') {
                $this->validarFormatoTelefono($validatedData['DebtorAccount']);
            }

            $authUser = auth()->user();
            $clientUserId = $authUser ? $authUser->id : ($validatedData['user_id'] ?? null);

            Log::info("Usuario procesando pago", [
                'authenticated' => $authUser ? 'si' : 'no',
                'client_user_id' => $clientUserId,
                'has_ally_in_request' => isset($validatedData['user_id'])
            ]);

            $bncData = $prepareBncData($validatedData);
            $bncData['Currency'] = 'BS';

            $payment = Payment::create([
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            Log::info("✅ Payment creado", ['payment_id' => $payment->id]);

            $order = Order::create([
                'total' => $validatedData['Amount'] ?? $validatedData['monto'] ?? 0,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            Log::info("✅ Order creada", ['order_id' => $order->id]);

            $allyData = $this->processAllyData($validatedData);

            if (!empty($allyData['ally_errors']) && $allyData['has_ally']) {
                Log::warning("Problemas con aliado, pero continuando con pago", [
                    'ally_user_id' => $allyData['user_id'],
                    'errors' => $allyData['ally_errors']
                ]);
                $allyData['has_ally'] = false;
                $allyData['discount'] = 0;
                $allyData['commission_percentage'] = 0;
                $allyData['commission_amount'] = 0;
                $allyData['net_amount'] = $validatedData['Amount'] ?? $validatedData['monto'] ?? 0;
            }

            $transaction = $this->createPaymentTransaction(
                $paymentType,
                $validatedData,
                $clientUserId,
                $allyData,
                $payment->id,
                $order->id,
                $tipoDebito
            );
            Log::info("✅ PaymentTransaction creada", [
                'transaction_id' => $transaction->id,
                'reference' => $transaction->reference_code,
                'client_user_id' => $transaction->user_id,
                'ally_id' => $transaction->ally_id,
                'tipo_debito' => $tipoDebito
            ]);

            $bncResponse = $this->executeBncPayment($paymentType, $bncData, $validatedData);

            if (!$this->isBncResponseSuccessful($bncResponse, $paymentType)) {
                $this->updateFailedTransaction($transaction, $order, $bncResponse);
                throw new \Exception("Fallo al procesar el pago {$paymentType}.");
            }

            $this->updateTransactionWithBncResponse($transaction, $bncResponse);

            $order->update(['status' => 'completed']);

            $sale = $this->createSale(
                $paymentType,
                $validatedData,
                $bncResponse,
                $allyData,
                $transaction,
                $order,
                $payment,
                $tipoDebito
            );

            $payout = null;
            if ($allyData['has_ally'] && $allyData['ally_valid']) {
                Log::debug("Creando payout para aliado válido", [
                    'ally_user_id' => $allyData['user_id'],
                    'ally_id' => $allyData['ally_id'],
                    'discount' => $allyData['discount'],
                    'commission' => $allyData['commission_percentage']
                ]);
                $payout = $this->payoutService->createPayout($sale, $allyData, $bncResponse);

                $transaction->update([
                    'payout_id' => $payout->id,
                    'status' => 'confirmed'
                ]);
            } else {
                $transaction->update(['status' => 'confirmed']);
                Log::debug("No se crea payout - aliado no válido o no existe", [
                    'has_ally' => $allyData['has_ally'],
                    'ally_valid' => $allyData['ally_valid'] ?? false,
                    'user_id' => $allyData['user_id']
                ]);
            }

            DB::commit();

            Log::info("🎉 Pago completado exitosamente", [
                'payment_type' => $paymentType,
                'transaction_id' => $transaction->id,
                'reference' => $transaction->reference_code,
                'tipo_debito' => $tipoDebito
            ]);

            return response()->json([
                'success' => true,
                'message' => $this->getSuccessMessage($paymentType),
                'data' => $this->buildSuccessResponse(
                    $sale,
                    $payout,
                    $bncResponse,
                    $allyData,
                    $transaction,
                    $order,
                    $payment,
                    $tipoDebito
                )
            ], 200);
        } catch (ValidationException $e) {
            DB::rollBack();
            Log::error("❌ ERROR DE VALIDACIÓN en {$paymentType}", [
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
            Log::error("❌ ERROR GENERAL en pago {$paymentType}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            $errorDetails = config('app.debug') ? $e->getMessage() : null;

            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el pago. Por favor, intente nuevamente.',
                'error_details' => $errorDetails,
            ], 500);
        }
    }

    /**
     * Detectar si es débito a cuenta o teléfono
     */
    private function detectarTipoDebito(array $data): ?string
    {
        $esDebito = isset($data['DebtorAccountType']) || isset($data['DebtorAccType']);

        if (!$esDebito) {
            return null;
        }

        $tipoCuenta = $data['DebtorAccountType'] ?? $data['DebtorAccType'] ?? null;

        if ($tipoCuenta === 'TLF' || $tipoCuenta === 'CELE') {
            return 'TELÉFONO';
        }

        return 'CUENTA';
    }

    /**
     * Validar formato de teléfono venezolano
     */
    private function validarFormatoTelefono(string $telefono): void
    {
        if (!preg_match('/^(0412|0414|0416|0424|0426)[0-9]{7}$/', $telefono)) {
            Log::warning('⚠️ Formato de teléfono inválido', ['telefono' => $telefono]);
            throw new \Exception('Formato de teléfono inválido. Debe ser un número venezolano válido (ej: 04141234567)');
        }
    }

    /**
     * Enmascarar número de cuenta/teléfono para logs
     */
    private function maskAccountNumber(string $number): string
    {
        if (strlen($number) <= 8) {
            return '****';
        }
        return substr($number, 0, 4) . '****' . substr($number, -4);
    }

    /**
     * Obtener mensaje de éxito según tipo de pago
     */
    private function getSuccessMessage(string $paymentType): string
    {
        return match ($paymentType) {
            'c2p' => 'Pago móvil procesado exitosamente.',
            'card' => 'Pago con tarjeta procesado exitosamente.',
            'debito_solicitar' => 'Solicitud de débito enviada. Revisa tu SMS.',
            'debito_emitir' => 'Débito inmediato procesado exitosamente.',
            'debito_reenviar' => 'Código SMS reenviado exitosamente.',
            default => "Pago {$paymentType} procesado exitosamente."
        };
    }

    /**
     * Crear PaymentTransaction
     */
    private function createPaymentTransaction(
        string $paymentType,
        array $validatedData,
        ?int $clientUserId,
        array $allyData,
        int $paymentId,
        int $orderId,
        ?string $tipoDebito = null
    ): PaymentTransaction {
        $amount = $validatedData['Amount'] ?? $validatedData['monto'] ?? 0;

        $confirmationData = [
            'request_data' => $validatedData,
            'payment_type' => $paymentType,
            'payment_id' => $paymentId,
            'order_id' => $orderId,
            'ally_data' => $allyData,
            'created_at' => now()->toDateTimeString()
        ];

        if ($tipoDebito) {
            $confirmationData['tipo_debito'] = $tipoDebito;
            $confirmationData['debtor_account_masked'] = $this->maskAccountNumber($validatedData['DebtorAccount'] ?? '');
        }

        $status = match ($paymentType) {
            'debito_solicitar' => 'pending_sms',
            'debito_emitir' => 'pending_confirmation',
            default => 'pending_manual_confirmation'
        };

        return PaymentTransaction::create([
            'user_id' => $clientUserId,
            'ally_id' => $allyData['ally_id'] ?? null,
            'original_amount' => $amount,
            'discount_percentage' => $allyData['discount'] ?? 0,
            'amount_to_ally' => $allyData['net_amount'] ?? $amount,
            'platform_commission' => $allyData['commission_amount'] ?? 0,
            'payment_method' => $this->mapPaymentMethod($paymentType),
            'status' => $status,
            'reference_code' => $this->generateReferenceCode(),
            'confirmation_data' => JsonHelper::encode($confirmationData),
        ]);
    }

    /**
     * Actualizar transacción con respuesta BNC
     */
    private function updateTransactionWithBncResponse(PaymentTransaction $transaction, array $bncResponse): void
    {
        // ✅ Usar json_decode directamente
        $currentData = json_decode($transaction->confirmation_data, true);

        // ✅ Si es null, inicializar como array vacío
        if (!is_array($currentData)) {
            $currentData = [];
        }

        $currentData['bnc_response'] = $bncResponse;
        $currentData['bnc_response_time'] = now()->toDateTimeString();

        $transaction->update([
            'confirmation_data' => json_encode($currentData)
        ]);
    }

    /**
     * Actualizar transacción fallida
     */
    private function updateFailedTransaction(PaymentTransaction $transaction, Order $order, array $bncResponse): void
    {
        // ✅ Usar json_decode directamente
        $currentData = json_decode($transaction->confirmation_data, true);

        // ✅ Si es null, inicializar como array vacío
        if (!is_array($currentData)) {
            $currentData = [];
        }

        $currentData['bnc_response'] = $bncResponse;
        $currentData['error'] = 'Pago fallido';
        $currentData['error_time'] = now()->toDateTimeString();

        $transaction->update([
            'status' => 'failed',
            'confirmation_data' => json_encode($currentData)
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
            'debito_solicitar', 'debito_emitir', 'debito_reenviar' => 'debito_inmediato',
            default => $paymentType
        };
    }

    /**
     * Generar código de referencia único
     */
    private function generateReferenceCode(): string
    {
        $prefix = 'TXN-';
        $maxAttempts = 10;
        $attempts = 0;

        do {
            // ✅ Usar random_bytes para mayor seguridad (PHP 7+)
            $random = bin2hex(random_bytes(6)); // 12 caracteres hexadecimales
            $reference = $prefix . strtoupper($random);

            $attempts++;

            // ✅ Prevenir bucle infinito
            if ($attempts >= $maxAttempts) {
                // Fallback con timestamp si hay muchos intentos fallidos
                $reference = $prefix . strtoupper(uniqid() . bin2hex(random_bytes(3)));
                break;
            }
        } while (PaymentTransaction::where('reference_code', $reference)->exists());

        return $reference;
    }

    /**
     * Ejecuta el pago según el tipo
     */
    private function executeBncPayment(string $paymentType, array $bncData, array $validatedData): array
    {
        try {
            Log::info("🔵 ENVIANDO A BNC - {$paymentType}", [
                'bnc_data_keys' => array_keys($bncData),
                'bnc_data_sanitized' => $this->sanitizeSensitiveData($bncData)
            ]);

            if (!$this->bncApiService->hasWorkingKey()) {
                Log::warning("🟡 BNC - No hay WorkingKey válido, intentando obtener...");

                $encryptedToken = $this->bncApiService->getSessionToken();
                if ($encryptedToken) {
                    $workingKey = $this->bncApiService->processSessionToken($encryptedToken);
                    Log::info("🟢 BNC - WorkingKey obtenido: " . ($workingKey ? 'OK' : 'FALLÓ'));
                } else {
                    Log::error("🔴 BNC - No se pudo obtener token de sesión");
                    throw new \Exception("No se pudo obtener token de sesión del BNC");
                }
            }

            $startTime = microtime(true);

            $response = match ($paymentType) {
                'c2p' => $this->bncApiService->initiateC2PPayment($bncData),
                'card' => $this->bncApiService->processCardPayment($bncData),
                'debito_solicitar' => $this->bncApiService->solicitarDebito($bncData),
                'debito_emitir' => $this->bncApiService->emitirDebito($bncData),
                'debito_reenviar' => $this->bncApiService->reenviarSms($bncData),
                default => throw new \Exception("Tipo de pago no soportado: {$paymentType}")
            };

            $executionTime = round((microtime(true) - $startTime) * 1000, 2);
            Log::info("⏱️ Tiempo de respuesta BNC: {$executionTime}ms");

            if (!is_array($response)) {
                Log::error("🔴 Respuesta de BNC no es un array", ['response' => $response]);
                throw new \Exception("Respuesta inválida del BNC");
            }

            Log::info("🔵 RESPUESTA DE BNC - {$paymentType}", [
                'response' => $response,
                'has_status' => isset($response['status']) ? 'SI' : 'NO',
                'status_value' => $response['status'] ?? null,
                'has_message' => isset($response['message']) ? 'SI' : 'NO',
                'has_validation' => isset($response['validation']) ? 'SI' : 'NO',
            ]);

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
            if (in_array($key, ['CardNumber', 'CVV', 'CardPIN', 'Token', 'DebtorAccount'])) {
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
     * Procesa datos del aliado (receptor del pago)
     */
    private function processAllyData(array $validatedData): array
    {
        $amount = $validatedData['Amount'] ?? $validatedData['monto'] ?? 0;

        $allyData = [
            'has_ally' => false,
            'ally_id' => null,
            'user_id' => null,
            'ally_name' => null,
            'ally_status' => null,
            'discount' => 0,
            'commission_percentage' => 0,
            'commission_amount' => 0,
            'amount_after_discount' => $amount,
            'net_amount' => $amount,
            'ally_valid' => false,
            'ally_errors' => []
        ];

        if (!empty($validatedData['user_id'])) {
            try {
                Log::debug("Buscando aliado por user_id en BD", ['user_id' => $validatedData['user_id']]);

                $ally = Ally::with('user')
                    ->where('user_id', $validatedData['user_id'])
                    ->first();

                if ($ally) {
                    Log::debug("Aliado encontrado por user_id", [
                        'ally_id' => $ally->id,
                        'user_id' => $ally->user_id,
                        'company_name' => $ally->company_name,
                        'discount' => $ally->discount,
                        'status' => $ally->status
                    ]);

                    if ($ally->isActive()) {
                        $allyData['has_ally'] = true;
                        $allyData['ally_id'] = $ally->id;
                        $allyData['user_id'] = $ally->user_id;
                        $allyData['ally_name'] = $ally->company_name ?? $ally->user->name ?? 'Aliado';
                        $allyData['ally_status'] = $ally->status;
                        $allyData['ally_valid'] = true;

                        $allyData['discount'] = $this->parseDiscount($ally->discount);
                        $allyData['commission_percentage'] = $this->determineCommission($allyData['discount']);

                        $allyData['amount_after_discount'] = $amount * (1 - ($allyData['discount'] / 100));
                        $allyData['commission_amount'] = round(
                            $allyData['amount_after_discount'] * ($allyData['commission_percentage'] / 100),
                            2
                        );
                        $allyData['net_amount'] = round(
                            $allyData['amount_after_discount'] - $allyData['commission_amount'],
                            2
                        );

                        Log::info("✅ Cálculos con comisión manual completados", [
                            'user_id' => $ally->user_id,
                            'ally_id' => $ally->id,
                            'discount' => $allyData['discount'] . '%',
                            'commission_percentage' => $allyData['commission_percentage'] . '%',
                            'original_amount' => $amount,
                            'amount_after_discount' => $allyData['amount_after_discount'],
                            'commission_amount' => $allyData['commission_amount'],
                            'net_amount' => $allyData['net_amount'],
                        ]);
                    } else {
                        $allyData['ally_errors'][] = "Aliado inactivo o suspendido: {$ally->status}";
                        Log::warning("Aliado no activo por user_id", [
                            'user_id' => $validatedData['user_id'],
                            'status' => $ally->status
                        ]);
                    }
                } else {
                    $allyData['ally_errors'][] = "No se encontró aliado para el user_id: {$validatedData['user_id']}";
                    Log::warning("Aliado no encontrado por user_id", ['user_id' => $validatedData['user_id']]);
                }
            } catch (\Exception $e) {
                $allyData['ally_errors'][] = "Error al consultar aliado por user_id: " . $e->getMessage();
                Log::error("Error consultando aliado por user_id", [
                    'user_id' => $validatedData['user_id'],
                    'error' => $e->getMessage()
                ]);
            }
        } else {
            Log::debug("No se proporcionó user_id, procesando como pago directo");
        }

        return $allyData;
    }

    /**
     * Determina la comisión manual según el descuento del aliado
     */
    private function determineCommission(float $discount): float
    {
        $commission = match (true) {
            $discount >= 0 && $discount <= 10 => 15.0,
            $discount > 10 && $discount <= 20 => 12.0,
            $discount > 20 && $discount <= 30 => 10.0,
            $discount > 30 => 8.0,
            default => 15.0
        };

        Log::debug("Comisión determinada", [
            'discount' => $discount,
            'commission_calculated' => $commission
        ]);

        return $commission;
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
     * Crea la venta con relaciones a payment, order y transaction - ADAPTADO PARA POSTGRESQL
     */
    private function createSale(
        string $paymentType,
        array $validatedData,
        array $bncResponse,
        array $allyData,
        PaymentTransaction $transaction,
        Order $order,
        Payment $payment,
        ?string $tipoDebito = null
    ): Sale {
        $amount = $validatedData['Amount'] ?? $validatedData['monto'] ?? 0;

        $bankReference = $bncResponse['validation'] ?? $bncResponse['Reference'] ?? $bncResponse['reference'] ?? null;
        $transactionId = $bncResponse['value'] ?? $bncResponse['TransactionId'] ?? $bncResponse['transactionId'] ?? null;

        if ($paymentType === 'debito_emitir' && isset($bncResponse['message'])) {
            if (preg_match('/:\s*(\d+)/', $bncResponse['message'], $matches)) {
                $transactionId = $matches[1];
            }
        }

        $baseData = [
            'ally_id' => $allyData['ally_id'] ?? null,
            'total_amount' => $amount,
            'paid_amount' => $amount,
            'bank_reference' => $bankReference,
            'transaction_id' => $transactionId,
            'status' => 'completed',
            'sale_date' => now(),
            'payment_date' => now(),
            'description' => $validatedData['descripcion'] ?? "Pago {$paymentType} procesado",
            'authorization_code' => $bncResponse['AuthorizationCode'] ?? null,
            'bank_response' => JsonHelper::encode($bncResponse),
        ];

        $specificData = match ($paymentType) {
            'c2p' => [
                'payment_method' => 'pago_movil',
                'destination_bank' => $validatedData['DebtorBankCode'] ?? null,
                'client_phone' => $validatedData['DebtorCellPhone'] ?? null,
                'client_id_number' => $validatedData['DebtorID'] ?? null,
            ],
            'card' => [
                'payment_method' => 'tarjeta_credito',
                'card_last_digits' => isset($validatedData['CardNumber']) ? substr($validatedData['CardNumber'], -4) : null,
                'card_holder_name' => $validatedData['CardHolderName'] ?? null,
            ],
            'debito_solicitar', 'debito_emitir', 'debito_reenviar' => [
                'payment_method' => 'debito_inmediato',
                'destination_bank' => $validatedData['DebtorBank'] ?? null,
                'account_last_digits' => isset($validatedData['DebtorAccount']) ? substr($validatedData['DebtorAccount'], -4) : null,
                'client_id_number' => $validatedData['DebtorID'] ?? null,
                'sms_code' => $validatedData['codigo_sms'] ?? null,
                'debtor_account_type' => $validatedData['DebtorAccountType'] ?? $validatedData['DebtorAccType'] ?? null,
                'tipo_debito' => $tipoDebito,
                'bank_status' => $bncResponse['status'] ?? null,
                'bank_message' => $bncResponse['message'] ?? null,
            ],
            default => ['payment_method' => $paymentType]
        };

        return Sale::create(array_merge($baseData, $specificData));
    }

    /**
     * Valida si la respuesta del BNC fue exitosa
     */
    private function isBncResponseSuccessful(array $bncResponse, string $paymentType = null): bool
    {
        Log::debug("Validando respuesta BNC para: {$paymentType}", ['response' => $bncResponse]);

        if (isset($bncResponse['status']) && $bncResponse['status'] === 'OK') {
            Log::info("✅ Respuesta exitosa del banco (status=OK)");
            return true;
        }

        if (isset($bncResponse['Status']) && $bncResponse['Status'] === 'OK') {
            Log::info("✅ Respuesta exitosa (Status=OK)");
            return true;
        }

        if (isset($bncResponse['success']) && $bncResponse['success'] === true) {
            Log::info("✅ Respuesta exitosa (success=true)");
            return true;
        }

        if (isset($bncResponse['CodigoRespuesta']) && $bncResponse['CodigoRespuesta'] === '00') {
            Log::info("✅ Respuesta exitosa (CodigoRespuesta=00)");
            return true;
        }

        // ✅ NUEVO: Validación para C2P (Pago Móvil)
        if ($paymentType === 'c2p' && isset($bncResponse['IdTransaction']) && isset($bncResponse['Reference'])) {
            Log::info("✅ Respuesta C2P exitosa - IdTransaction: {$bncResponse['IdTransaction']}, Reference: {$bncResponse['Reference']}");
            return true;
        }

        // ✅ NUEVO: Validación para VPOS (Tarjeta)
        if ($paymentType === 'card' && isset($bncResponse['TransactionId'])) {
            Log::info("✅ Respuesta VPOS exitosa - TransactionId: {$bncResponse['TransactionId']}");
            return true;
        }

        $validations = [
            'debito_solicitar' => function ($response) {
                return isset($response['validation']) && !empty($response['validation']);
            },
            'debito_emitir' => function ($response) {
                return isset($response['message']) && str_contains($response['message'] ?? '', 'iniciado');
            },
            'debito_reenviar' => function ($response) {
                return isset($response['status']) && $response['status'] === 'OK';
            }
        ];

        if ($paymentType && isset($validations[$paymentType])) {
            $isValid = $validations[$paymentType]($bncResponse);
            if ($isValid) {
                Log::info("✅ Validación específica {$paymentType} exitosa");
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
        Sale $sale,
        ?Payout $payout,
        array $bncResponse,
        array $allyData,
        PaymentTransaction $transaction,
        Order $order,
        Payment $payment,
        ?string $tipoDebito = null
    ): array {
        $response = [
            'sale' => [
                'id' => $sale->id,
                'total_amount' => $sale->total_amount,
                'bank_reference' => $sale->bank_reference,
                'transaction_id' => $sale->transaction_id,
                'payment_method' => $sale->payment_method,
                'status' => $sale->status,
                'sale_date' => $sale->sale_date ? $sale->sale_date->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s'),
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
                'status' => $bncResponse['status'] ?? null,
                'message' => $bncResponse['message'] ?? null,
                'validation' => $bncResponse['validation'] ?? null,
                'value' => $bncResponse['value'] ?? null,
            ]
        ];

        if ($tipoDebito) {
            $response['tipo_debito'] = $tipoDebito;
        }

        if ($allyData['has_ally']) {
            $response['ally'] = [
                'ally_id' => $allyData['ally_id'],
                'ally_name' => $allyData['ally_name'],
                'discount' => $allyData['discount'],
                'commission_percentage' => $allyData['commission_percentage'],
                'commission_amount' => $allyData['commission_amount'],
                'amount_after_discount' => $allyData['amount_after_discount'],
                'net_amount' => $allyData['net_amount'],
                'payout_id' => $payout?->id,
            ];
        }

        if ($payout) {
            $response['payout'] = [
                'id' => $payout->id,
                'amount' => $payout->net_amount,
                'status' => $payout->status,
            ];
        }

        return $response;
    }
}
