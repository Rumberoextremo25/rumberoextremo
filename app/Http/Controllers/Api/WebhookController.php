<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
// Ya no necesitamos inyectar BncApiService aquí a menos que el webhook requiera
// hacer una llamada de vuelta al BNC para confirmar algo. Generalmente, un webhook
// solo recibe y procesa datos.

class WebhookController extends Controller
{
    /**
     * Maneja las notificaciones (webhooks) para pagos C2P desde el BNC.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function handleC2PWebhook(Request $request): JsonResponse
    {
        Log::info('Webhook C2P del BNC recibido.', ['payload' => $request->all()]);

        // --- 1. **VERIFICACIÓN DE SEGURIDAD (CRÍTICO):** ---
        // Aquí debes implementar la lógica para verificar que el webhook realmente proviene del BNC.
        // Esto podría implicar:
        // - Verificar una IP permitida del BNC.
        // - Validar una firma o hash enviado en los headers o en el cuerpo de la petición.
        // - Un "token secreto" pre-compartido.
        // Si la verificación falla, debes retornar un error y no procesar la notificación.
        // Ejemplo (pseudo-código):
        // if (!$this->verifyBncWebhookSignature($request)) {
        //     Log::warning('Firma de webhook C2P inválida. Posible ataque o notificación no autorizada.');
        //     return response()->json(['message' => 'Unauthorized'], 403);
        // }

        // 2. Procesar el payload del webhook
        // El formato del payload dependerá de cómo el BNC envíe las notificaciones.
        // Ejemplo asumiendo un payload simple:
        $transactionId = $request->input('transaction_id');
        $status = $request->input('status'); // 'success', 'failed', 'pending', etc.
        $reference = $request->input('reference');
        $amount = $request->input('amount');

        if (empty($transactionId) || empty($status)) {
            Log::error('Payload de webhook C2P incompleto o inesperado.', ['payload' => $request->all()]);
            return response()->json(['message' => 'Bad Request'], 400);
        }

        // 3. Actualizar el estado de la transacción en tu base de datos
        // Aquí deberías buscar la transacción usando $transactionId (o algún ID de referencia que el BNC te devuelva)
        // y actualizar su estado a 'success', 'failed', etc.
        // Ejemplo:
        // $payment = Payment::where('bnc_transaction_id', $transactionId)->first();
        // if ($payment) {
        //     $payment->status = $status;
        //     $payment->bnc_reference = $reference; // Si BNC da una referencia
        //     $payment->save();
        //     Log::info("Estado de pago C2P actualizado para transaction ID: $transactionId a $status.");
        // } else {
        //     Log::warning("Webhook C2P recibido para transaction ID: $transactionId, pero no se encontró la transacción en DB.");
        //     // Podrías registrarlo como una transacción nueva si es el caso, o simplemente loguear el warning
        // }

        // 4. Retornar una respuesta HTTP 200 OK para confirmar la recepción del webhook
        // Es CRÍTICO retornar 200 OK rápidamente para que el BNC no reintente enviar la notificación.
        return response()->json(['message' => 'Webhook C2P recibido y procesado.'], 200);
    }

    /**
     * Maneja las notificaciones (webhooks) para pagos con tarjeta desde el BNC.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function handleCardWebhook(Request $request): JsonResponse
    {
        Log::info('Webhook de Tarjeta (VPOS) del BNC recibido.', ['payload' => $request->all()]);

        // --- 1. **VERIFICACIÓN DE SEGURIDAD (CRÍTICO):** ---
        // Similar al C2P, verifica la autenticidad de la notificación.
        // if (!$this->verifyBncWebhookSignature($request)) {
        //     Log::warning('Firma de webhook de tarjeta inválida. Posible ataque o notificación no autorizada.');
        //     return response()->json(['message' => 'Unauthorized'], 403);
        // }

        // 2. Procesar el payload del webhook
        $cardTransactionId = $request->input('card_transaction_id');
        $cardStatus = $request->input('card_status');

        if (empty($cardTransactionId) || empty($cardStatus)) {
            Log::error('Payload de webhook de tarjeta incompleto o inesperado.', ['payload' => $request->all()]);
            return response()->json(['message' => 'Bad Request'], 400);
        }

        // 3. Actualizar el estado de la transacción con tarjeta en tu base de datos
        // $payment = Payment::where('bnc_card_transaction_id', $cardTransactionId)->first();
        // if ($payment) {
        //     $payment->status = $cardStatus;
        //     $payment->save();
        //     Log::info("Estado de pago con tarjeta actualizado para ID: $cardTransactionId a $cardStatus.");
        // } else {
        //     Log::warning("Webhook de tarjeta recibido para ID: $cardTransactionId, pero no se encontró la transacción en DB.");
        // }

        // 4. Retornar una respuesta HTTP 200 OK.
        return response()->json(['message' => 'Webhook de Tarjeta recibido y procesado.'], 200);
    }

    // /**
    //  * Función de ejemplo para verificar la firma del webhook del BNC.
    //  * ¡IMPLEMENTA LA LÓGICA DE VERIFICACIÓN REAL SEGÚN LA DOCUMENTACIÓN DEL BNC!
    //  *
    //  * @param Request $request
    //  * @return bool
    //  */
    // private function verifyBncWebhookSignature(Request $request): bool
    // {
    //     // Esto es un placeholder. La lógica real depende de la documentación del BNC.
    //     // Podría ser un header 'X-BNC-Signature' que contiene un hash del payload,
    //     // o un token secreto en el URL del webhook.
    //     $secretKey = config('bnc.webhook_secret_key'); // Asegúrate de definir esto en config/bnc.php y .env
    //     $signature = $request->header('X-BNC-Signature'); // Ejemplo de un header de firma

    //     if (!$signature || !$secretKey) {
    //         return false;
    //     }

    //     // Ejemplo: Generar un hash del cuerpo de la petición y compararlo con la firma
    //     // $expectedSignature = hash_hmac('sha256', $request->getContent(), $secretKey);
    //     // return hash_equals($expectedSignature, $signature);

    //     return true; // Simulación: siempre true. ¡NO USAR EN PRODUCCIÓN!
    // }
}