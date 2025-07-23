<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ally;
use App\Models\PaymentTransaction;
use App\Services\BankApiClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Jobs\ProcessPaymentSplitJob; // <-- ¡Añade esta línea!

class PaymentController extends Controller
{
    protected $bankApiClient;

    public function __construct(BankApiClient $bankApiClient)
    {
        $this->bankApiClient = $bankApiClient;
    }

    /**
     * Endpoint inicial: El cliente solicita pagar. Se registran los datos y se dan instrucciones.
     * La confirmación del pago se espera por webhook bancario.
     */
    public function processPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'allyId' => 'required|integer|exists:allies,id',
            'originalAmount' => 'required|numeric|min:0.01',
            'paymentMethod' => 'required|string|in:pago_movil,transferencia_bancaria,qr',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de entrada inválidos.',
                'errors' => $validator->errors()
            ], 400);
        }

        $allyId = $request->input('allyId');
        $originalAmount = (float) $request->input('originalAmount');
        $paymentMethod = $request->input('paymentMethod');

        try {
            $ally = Ally::find($allyId);
            if (!$ally) {
                return response()->json(['success' => false, 'message' => 'Aliado no encontrado.'], 404);
            }

            $discountPercentage = 0.0;
            if (!empty($ally->discount)) {
                $discountPercentage = (float) str_replace(['%', ' '], '', $ally->discount);
            }

            $valorDescuento = $originalAmount * ($discountPercentage / 100);
            $montoParaAliado = $originalAmount - $valorDescuento;
            $montoParaPlataforma = $valorDescuento;

            // Generar un ID de transacción único para la referencia de pago del cliente
            // Este es CRÍTICO: el cliente DEBE usar esta referencia para que el webhook bancario la detecte.
            $transactionReference = 'RUM' . Str::upper(Str::random(8));

            // --- 1. Registrar la intención de pago en la base de datos ---
            // Estado inicial: 'pending_bank_confirmation' - esperando webhook del banco
            $transaction = PaymentTransaction::create([
                'ally_id' => $ally->id,
                'original_amount' => $originalAmount,
                'discount_percentage' => $discountPercentage,
                'amount_to_ally' => $montoParaAliado,
                'platform_commission' => $montoParaPlataforma,
                'payment_method' => $paymentMethod,
                'status' => 'pending_bank_confirmation', // Nuevo estado
                'reference_code' => $transactionReference,
                'confirmation_data' => null, // No hay confirmación manual del usuario aquí
            ]);

            // --- 2. Obtener las instrucciones de pago del BankApiClient ---
            // Asegúrate de que este método devuelva los datos de tu cuenta bancaria y la $transactionReference
            $paymentInstructions = $this->bankApiClient->createPaymentRequest(
                $originalAmount,
                $paymentMethod,
                $transactionReference
            );

            if (!$paymentInstructions) {
                $transaction->status = 'payment_request_failed';
                $transaction->save();
                return response()->json(['success' => false, 'message' => 'No se pudieron generar las instrucciones de pago para el cliente.'], 500);
            }

            // --- 3. Devolver las instrucciones al cliente (app) ---
            return response()->json([
                'success' => true,
                'message' => 'Por favor, completa el pago según las instrucciones. Recibirás una confirmación automática.',
                'instructions' => $paymentInstructions,
                'transaction_id' => $transaction->id, // Puedes enviar el ID para referencia futura en la app
            ]);

        } catch (\Exception $e) {
            Log::error("Error en processPayment (solicitud inicial): " . $e->getMessage() . " en " . $e->getFile() . " linea " . $e->getLine());
            return response()->json(['success' => false, 'message' => 'Error interno del servidor al procesar la solicitud de pago.'], 500);
        }
    }

    /**
     * NUEVO ENDPOINT: Maneja las notificaciones (webhooks) que envía el banco.
     * Este método es el que confirma automáticamente los pagos entrantes.
     */
    public function handleBankWebhook(Request $request)
    {
        // 1. Validar la firma o autenticación del webhook
        // Los bancos suelen enviar un encabezado de seguridad (ej. 'X-Bank-Signature')
        // para que verifiques que la petición realmente viene de ellos y no ha sido alterada.
        // **¡Implementa esta validación de seguridad CRÍTICA!**
        // Si la validación falla, retorna un error 403.
        // if (!$this->isValidBankWebhookSignature($request)) {
        //     Log::warning('Webhook bancario recibido con firma inválida.');
        //     return response()->json(['message' => 'Unauthorized'], 403);
        // }

        // 2. Extraer datos relevantes del webhook
        // La estructura de los datos del webhook dependerá COMPLETAMENTE de la API de tu banco.
        $webhookData = $request->all();
        // Reemplazando '??' con el operador ternario 'isset() ? : '
        $eventType = isset($webhookData['event_type']) ? $webhookData['event_type'] : null;
        $transactionAmount = isset($webhookData['transaction_details']['amount']) ? $webhookData['transaction_details']['amount'] : null;
        $transactionReference = isset($webhookData['transaction_details']['reference_code']) ? $webhookData['transaction_details']['reference_code'] : null;

        Log::info("Webhook bancario recibido: ", $webhookData);

        // 3. Procesar solo los eventos de pago recibido relevante
        if ($eventType === 'payment.received' || $eventType === 'transfer.inbound') { // EJEMPLOS de nombres de eventos
            if (!$transactionReference || !$transactionAmount) {
                Log::error('Webhook bancario de pago recibido incompleto.', $webhookData);
                return response()->json(['message' => 'Webhook data incomplete'], 400);
            }

            // Buscar la transacción correspondiente en tu base de datos
            $transaction = PaymentTransaction::where('reference_code', $transactionReference)
                                           ->where('status', 'pending_bank_confirmation')
                                           ->first();

            if (!$transaction) {
                // Posiblemente un webhook duplicado o una referencia no encontrada
                Log::warning("Webhook bancario para referencia {$transactionReference} no encontró transacción pendiente o ya procesada.");
                return response()->json(['message' => 'Transaction not found or already processed'], 200); // 200 OK para evitar reintentos
            }

            // Verificar si el monto coincide
            if (abs($transaction->original_amount - $transactionAmount) > 0.01) { // Pequeña tolerancia flotante
                Log::error("Monto en webhook ({$transactionAmount}) no coincide con el esperado ({$transaction->original_amount}) para transacción {$transaction->id}.");
                $transaction->status = 'amount_mismatch';
                $transaction->save();
                return response()->json(['message' => 'Amount mismatch'], 400);
            }

            // --- 4. Disparar la lógica de división de fondos (en segundo plano) ---
            // Esto es crucial para no bloquear la respuesta del webhook y para reintentos en caso de fallo.
            // Movemos la lógica de las transferencias salientes a un Job/Queue.
            dispatch(new ProcessPaymentSplitJob($transaction->id)); // Uso directo del Job ahora que está importado

            // 5. Responder al banco que el webhook fue recibido con éxito
            return response()->json(['message' => 'Webhook received and processing started'], 200);

        } else {
            // Ignorar otros tipos de eventos de webhook que no sean de pago
            return response()->json(['message' => 'Event type not handled'], 200);
        }
    }

    /**
     * Método público para la lógica de ejecución de transferencias divididas.
     * Este será llamado por un Job/Queue.
     */
    public function executeSplitTransfers(int $transactionId): void
    {
        $transaction = PaymentTransaction::find($transactionId);

        // Reemplazando '??' con el operador ternario 'isset() ? : '
        if (!$transaction || (isset($transaction->status) && $transaction->status !== 'pending_bank_confirmation')) {
            Log::warning("Intento de ejecutar split para transacción no válida o ya procesada: {$transactionId}. Estado: " . (isset($transaction->status) ? $transaction->status : 'N/A'));
            return;
        }

        try {
            // Actualizar el estado de la transacción a confirmado (si el webhook aún no lo hizo)
            $transaction->status = 'incoming_payment_confirmed';
            $transaction->save();

            // --- 1. INICIAR TRANSFERENCIAS SALIENTES (SPLIT) ---
            $platformMainAccount = config('services.platform_bank.main_account');
            $platformCommissionAccount = config('services.platform_bank.commission_account');

            // 1.1 Transferencia al Aliado
            $allyTransferSuccess = false;
            if ($transaction->amount_to_ally > 0 && $ally = $transaction->ally) {
                // Validar que el aliado tenga datos bancarios completos
                if (empty($ally->bank_name) || empty($ally->account_number) || empty($ally->id_number)) {
                    Log::error("Aliado {$ally->id} no tiene datos bancarios completos para transferencia.");
                    $allyTransferSuccess = false;
                } else {
                    $allyTransferData = [
                        'source_account_number' => $platformMainAccount['account_number'],
                        'destination_bank_code' => $ally->bank_name,
                        'destination_account_number' => $ally->account_number,
                        'destination_account_type' => $ally->account_type,
                        'destination_id_number' => $ally->id_number,
                        'destination_holder_name' => $ally->account_holder_name,
                        'amount' => $transaction->amount_to_ally,
                        'concept' => "Pago a {$ally->name} por Rumbero Extremo (Ref: {$transaction->reference_code})",
                        'reference_id' => $transaction->reference_code . '-ALLY',
                    ];
                    $allyTransferResponse = $this->bankApiClient->initiateTransfer($allyTransferData);
                    $allyTransferSuccess = ($allyTransferResponse && isset($allyTransferResponse['status']) && $allyTransferResponse['status'] === 'success');
                }
            } else {
                $allyTransferSuccess = true; // No hay monto para transferir o aliado no existe
            }


            // 1.2 Transferencia de Comisión a Tu Cuenta de Plataforma (si es diferente a la principal)
            $platformCommissionTransferSuccess = false;
            if ($transaction->platform_commission > 0 && $platformMainAccount['account_number'] !== $platformCommissionAccount['account_number']) {
                $platformTransferData = [
                    'source_account_number' => $platformMainAccount['account_number'],
                    'destination_bank_code' => $platformCommissionAccount['bank_name'],
                    'destination_account_number' => $platformCommissionAccount['account_number'],
                    'destination_account_type' => $platformCommissionAccount['account_type'],
                    'destination_id_number' => $platformCommissionAccount['id_number'],
                    'destination_holder_name' => $platformCommissionAccount['account_holder_name'],
                    'amount' => $transaction->platform_commission,
                    'concept' => "Comisión Rumbero Extremo (Ref: {$transaction->reference_code})",
                    'reference_id' => $transaction->reference_code . '-COMM',
                ];
                $platformTransferResponse = $this->bankApiClient->initiateTransfer($platformTransferData);
                $platformCommissionTransferSuccess = ($platformTransferResponse && isset($platformTransferResponse['status']) && $platformTransferResponse['status'] === 'success');
            } else {
                $platformCommissionTransferSuccess = true; // No hay comisión o se queda en la cuenta principal
            }

            // --- 2. ACTUALIZAR EL ESTADO FINAL DE LA TRANSACCIÓN ---
            if ($allyTransferSuccess && $platformCommissionTransferSuccess) {
                $transaction->status = 'completed';
                $transaction->save();
                Log::info("Transacción {$transaction->id} completada y fondos divididos con éxito.");
            } else {
                $transaction->status = 'failed_outgoing_transfer';
                $transaction->save();
                Log::error("Fallo al dividir fondos para transacción {$transaction->id}. Aliado: {$allyTransferSuccess}, Plataforma: {$platformCommissionTransferSuccess}. Requiere intervención.");
                // Aquí podrías enviar notificaciones de error a los administradores
            }

        } catch (\Exception $e) {
            Log::error("Error en executeSplitTransfers para transacción {$transactionId}: " . $e->getMessage() . " en " . $e->getFile() . " linea " . $e->getLine());
            $transaction->status = 'split_processing_error';
            $transaction->save();
            // Notificar a los administradores
        }
    }

    /**
     * Método de ejemplo para validar la firma del webhook.
     * DEBES IMPLEMENTAR ESTO SEGÚN LA DOCUMENTACIÓN DE TU BANCO.
     */
    // private function isValidBankWebhookSignature(Request $request): bool
    // {
    //     $signature = $request->header('X-Bank-Signature'); // O el nombre del encabezado de tu banco
    //     $payload = $request->getContent();
    //     $secret = config('services.platform_bank.api.webhook_secret'); // Define esto en tu .env y config

    //     // Lógica para verificar la firma. Esto podría ser un HMAC, una validación de certificado, etc.
    //     // Ejemplo simple de HMAC (NO usar así en prod sin los detalles exactos del banco):
    //     // $expectedSignature = hash_hmac('sha256', $payload, $secret);
    //     // return hash_equals($expectedSignature, $signature);

    //     return true; // POR AHORA, solo para la demostración
    // }
}