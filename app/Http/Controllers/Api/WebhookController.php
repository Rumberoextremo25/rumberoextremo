<?php

namespace App\Http\Controllers;

use App\Models\Order; // Asumo que actualizas el estado de la Order
use App\Models\PartnerPayout; // Si también se actualiza el PartnerPayout
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
// use Symfony\Component\HttpFoundation\Response; // Para códigos de estado HTTP más legibles

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
        // Debes implementar esta lógica. La mayoría de las pasarelas envían
        // una firma en los headers de la petición (ej. 'X-Bnc-Signature').
        // Esta firma se genera con una clave secreta y los datos del payload.
        // **FALTA IMPLEMENTACIÓN: Obtén la lógica de la documentación del BNC.**
        // if (!$this->verifyBncWebhookSignature($request)) {
        //     Log::warning('Firma de webhook C2P inválida o no autorizada.', ['payload' => $request->all()]);
        //     return response()->json(['message' => 'Unauthorized or invalid webhook source.'], 403);
        // }

        // Envuelve la lógica en una transacción para garantizar la atomicidad
        DB::beginTransaction();
        try {
            // 2. Procesar el payload del webhook del BNC
            // A menudo, el BNC envía un JSON encriptado. Tendrías que desencriptarlo primero.
            // Ejemplo: $decryptedPayload = $this->bncApiService->decryptPayload($request->input('payload'));
            // Por ahora, asumimos que el payload está en texto plano.
            $payload = $request->json()->all();

            $bncTransactionId = $payload['TransactionIdentifier'] ?? null;
            $bncStatus = $payload['Status'] ?? null;
            $bncMessage = $payload['Message'] ?? 'No message provided.';

            // Si el payload es un simple 'OK' o 'ERROR' sin más datos, ajusta esta lógica.
            if (empty($bncTransactionId) || empty($bncStatus)) {
                Log::error('Payload de webhook C2P incompleto o inesperado.', ['payload' => $request->all()]);
                DB::rollBack();
                return response()->json(['message' => 'Bad Request: Missing required payload data.'], 400);
            }

            // 3. Buscar la orden/transacción en tu base de datos
            // Usamos el 'TransactionIdentifier' que obtuvimos del webhook.
            $order = Order::where('transaction_id', $bncTransactionId)->first();

            if (!$order) {
                Log::warning('Webhook C2P recibido, pero no se encontró la orden asociada en la DB.', [
                    'bnc_transaction_id' => $bncTransactionId,
                    'bnc_status' => $bncStatus
                ]);
                DB::rollBack();
                return response()->json(['message' => 'Order not found, but webhook acknowledged.'], 200);
            }

            // 4. Actualizar el estado de la orden y/o pagos internos
            switch ($bncStatus) {
                case 'OK':
                    // Evitar el reprocesamiento de una orden que ya está completada
                    if ($order->status === 'completed') {
                        Log::info('Webhook C2P para orden ya completada, no se requiere acción.', ['order_id' => $order->id]);
                        DB::commit();
                        return response()->json(['message' => 'Webhook already processed.'], 200);
                    }

                    $order->status = 'completed';
                    $order->payment_confirmed_at = now();
                    $order->notes .= " | Confirmación C2P BNC: {$bncMessage}.";
                    $order->save();

                    // Actualizar el estado del pago al aliado
                    $partnerPayout = PartnerPayout::where('order_id', $order->id)->first();
                    if ($partnerPayout && $partnerPayout->status === 'pending') {
                        $partnerPayout->status = 'ready_for_payout';
                        $partnerPayout->notes .= " Confirmación C2P BNC.";
                        $partnerPayout->save();
                    }

                    Log::info('Orden y pago C2P actualizados a "completed" por webhook.', ['order_id' => $order->id, 'bnc_transaction_id' => $bncTransactionId]);
                    break;

                case 'ERROR':
                case 'REJECTED': // Posiblemente otros estados de fallo
                    if ($order->status !== 'failed') {
                        $order->status = 'failed';
                        $order->payment_failed_at = now();
                        $order->notes .= " | Fallo de pago C2P BNC: {$bncMessage}.";
                        $order->save();

                        $partnerPayout = PartnerPayout::where('order_id', $order->id)->first();
                        if ($partnerPayout) {
                            $partnerPayout->status = 'failed';
                            $partnerPayout->notes .= " Pago C2P fallido BNC: {$bncMessage}.";
                            $partnerPayout->save();
                        }
                    }
                    Log::warning('Pago C2P fallido reportado por webhook del BNC.', ['order_id' => $order->id, 'bnc_transaction_id' => $bncTransactionId, 'bnc_message' => $bncMessage]);
                    break;

                default:
                    Log::info('Webhook C2P con estado desconocido o no manejado.', ['order_id' => $order->id, 'bnc_transaction_id' => $bncTransactionId, 'bnc_status' => $bncStatus]);
                    break;
            }

            DB::commit();
            return response()->json(['message' => 'Webhook C2P procesado exitosamente.'], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Excepción al procesar webhook C2P del BNC: ' . $e->getMessage(), ['trace' => $e->getTraceAsString(), 'payload' => $request->all()]);
            return response()->json(['message' => 'Internal server error processing webhook.'], 500);
        }
    }

    /**
     * Maneja las notificaciones (webhooks) para pagos con tarjeta desde el BNC (VPOS).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function handleCardWebhook(Request $request): JsonResponse
    {
        Log::info('Webhook de Tarjeta (VPOS) del BNC recibido.', ['payload' => $request->all()]);

        // --- 1. **VERIFICACIÓN DE SEGURIDAD (CRÍTICO):** ---
        // Implementa aquí la lógica de verificación de firma del webhook.
        // if (!$this->verifyBncWebhookSignature($request)) {
        //     Log::warning('Firma de webhook de tarjeta inválida o no autorizada.', ['payload' => $request->all()]);
        //     return response()->json(['message' => 'Unauthorized or invalid webhook source.'], 403);
        // }

        DB::beginTransaction();
        try {
            // 2. Procesar el payload del webhook de VPOS del BNC.
            // Los nombres de los campos ('TransactionIdentifier', 'Status', etc.) deben
            // coincidir con los que el BNC realmente envía para los webhooks de VPOS.
            $payload = $request->json()->all();

            $bncCardTransactionId = $payload['TransactionIdentifier'] ?? null;
            $bncCardStatus = $payload['Status'] ?? null;
            $bncCardMessage = $payload['Message'] ?? 'No message provided.';

            if (empty($bncCardTransactionId) || empty($bncCardStatus)) {
                Log::error('Payload de webhook de tarjeta incompleto o inesperado.', ['payload' => $request->all()]);
                DB::rollBack();
                return response()->json(['message' => 'Bad Request: Missing required payload data.'], 400);
            }

            // 3. Buscar la orden/transacción en tu base de datos.
            // Asegúrate de que el campo `transaction_id` en tu tabla `orders`
            // contenga el identificador que el BNC devuelve en su respuesta.
            $order = Order::where('transaction_id', $bncCardTransactionId)->first();

            if (!$order) {
                Log::warning('Webhook de tarjeta recibido, pero no se encontró la orden asociada en la DB.', [
                    'bnc_transaction_id' => $bncCardTransactionId,
                    'bnc_status' => $bncCardStatus
                ]);
                DB::rollBack();
                return response()->json(['message' => 'Order not found, but webhook acknowledged.'], 200);
            }

            // 4. Actualizar el estado de la orden y/o pagos internos
            switch ($bncCardStatus) {
                case 'OK':
                    if ($order->status === 'completed') {
                        Log::info('Webhook de tarjeta para orden ya completada, no se requiere acción.', ['order_id' => $order->id, 'bnc_transaction_id' => $bncCardTransactionId]);
                        DB::commit();
                        return response()->json(['message' => 'Webhook already processed.'], 200);
                    }

                    $order->status = 'completed'; // O un estado más específico como 'paid_confirmed_card'
                    $order->payment_confirmed_at = now();
                    $order->notes .= " | Confirmación de pago con tarjeta BNC: {$bncCardMessage}.";
                    $order->save();

                    $partnerPayout = PartnerPayout::where('order_id', $order->id)->first();
                    if ($partnerPayout && $partnerPayout->status === 'pending') {
                        $partnerPayout->status = 'ready_for_payout';
                        $partnerPayout->notes .= " Confirmación pago con tarjeta BNC.";
                        $partnerPayout->save();
                    }

                    Log::info('Orden y pago con tarjeta actualizados a "completed" por webhook.', ['order_id' => $order->id, 'bnc_transaction_id' => $bncCardTransactionId]);
                    break;

                case 'ERROR':
                case 'REJECTED': // Posibles otros estados de fallo
                    if ($order->status !== 'failed') {
                        $order->status = 'failed';
                        $order->payment_failed_at = now();
                        $order->notes .= " | Fallo de pago con tarjeta BNC: {$bncCardMessage}.";
                        $order->save();

                        $partnerPayout = PartnerPayout::where('order_id', $order->id)->first();
                        if ($partnerPayout) {
                            $partnerPayout->status = 'failed';
                            $partnerPayout->notes .= " Pago con tarjeta fallido BNC: {$bncCardMessage}.";
                            $partnerPayout->save();
                        }
                    }
                    Log::warning('Pago con tarjeta fallido reportado por webhook del BNC.', ['order_id' => $order->id, 'bnc_transaction_id' => $bncCardTransactionId, 'bnc_message' => $bncCardMessage]);
                    break;

                default:
                    Log::info('Webhook de tarjeta con estado desconocido o no manejado.', ['order_id' => $order->id, 'bnc_transaction_id' => $bncCardTransactionId, 'bnc_status' => $bncCardStatus]);
                    break;
            }

            DB::commit();
            return response()->json(['message' => 'Webhook de Tarjeta procesado exitosamente.'], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Excepción al procesar webhook de tarjeta del BNC: ' . $e->getMessage(), ['trace' => $e->getTraceAsString(), 'payload' => $request->all()]);
            return response()->json(['message' => 'Internal server error processing webhook.'], 500);
        }
    }

    public function handleP2PWebhook(Request $request): JsonResponse
    {
        Log::info('Webhook P2P del BNC recibido.', ['payload' => $request->all()]);

        // --- 1. **VERIFICACIÓN DE SEGURIDAD (CRÍTICO):** ---
        // Implementa la lógica de verificación de firma del webhook aquí.
        // if (!$this->verifyBncWebhookSignature($request)) {
        //     Log::warning('Firma de webhook P2P inválida o no autorizada.', ['payload' => $request->all()]);
        //     return response()->json(['message' => 'Unauthorized or invalid webhook source.'], 403);
        // }

        DB::beginTransaction();
        try {
            // 2. Procesar el payload del webhook P2P del BNC.
            // Asegúrate de que los nombres de los campos coincidan con la documentación de BNC.
            $payload = $request->json()->all();

            $bncP2PTransactionId = $payload['TransactionIdentifier'] ?? null;
            $bncP2PStatus = $payload['Status'] ?? null;
            $bncP2PMessage = $payload['Message'] ?? 'No message provided.';

            if (empty($bncP2PTransactionId) || empty($bncP2PStatus)) {
                Log::error('Payload de webhook P2P incompleto o inesperado.', ['payload' => $request->all()]);
                DB::rollBack();
                return response()->json(['message' => 'Bad Request: Missing required payload data.'], 400);
            }

            // 3. Buscar la orden/transacción en tu base de datos.
            $order = Order::where('transaction_id', $bncP2PTransactionId)->first();

            if (!$order) {
                Log::warning('Webhook P2P recibido, pero no se encontró la orden asociada en la DB.', [
                    'bnc_transaction_id' => $bncP2PTransactionId,
                    'bnc_status' => $bncP2PStatus
                ]);
                DB::rollBack();
                return response()->json(['message' => 'Order not found, but webhook acknowledged.'], 200);
            }

            // 4. Actualizar el estado de la orden y/o pagos internos.
            switch ($bncP2PStatus) {
                case 'OK':
                    if ($order->status === 'completed') {
                        Log::info('Webhook P2P para orden ya completada, no se requiere acción.', ['order_id' => $order->id, 'bnc_transaction_id' => $bncP2PTransactionId]);
                        DB::commit();
                        return response()->json(['message' => 'Webhook already processed.'], 200);
                    }

                    $order->status = 'completed'; 
                    $order->payment_confirmed_at = now();
                    $order->notes .= " | Confirmación de pago P2P BNC: {$bncP2PMessage}.";
                    $order->save();

                    $partnerPayout = PartnerPayout::where('order_id', $order->id)->first();
                    if ($partnerPayout && $partnerPayout->status === 'pending') {
                        $partnerPayout->status = 'ready_for_payout';
                        $partnerPayout->notes .= " Confirmación pago P2P BNC.";
                        $partnerPayout->save();
                    }

                    Log::info('Orden y pago P2P actualizados a "completed" por webhook.', ['order_id' => $order->id, 'bnc_transaction_id' => $bncP2PTransactionId]);
                    break;

                case 'ERROR':
                case 'REJECTED': 
                    if ($order->status !== 'failed') {
                        $order->status = 'failed';
                        $order->payment_failed_at = now();
                        $order->notes .= " | Fallo de pago P2P BNC: {$bncP2PMessage}.";
                        $order->save();

                        $partnerPayout = PartnerPayout::where('order_id', $order->id)->first();
                        if ($partnerPayout) {
                            $partnerPayout->status = 'failed';
                            $partnerPayout->notes .= " Pago P2P fallido BNC: {$bncP2PMessage}.";
                            $partnerPayout->save();
                        }
                    }
                    Log::warning('Pago P2P fallido reportado por webhook del BNC.', ['order_id' => $order->id, 'bnc_transaction_id' => $bncP2PTransactionId, 'bnc_message' => $bncP2PMessage]);
                    break;

                default:
                    Log::info('Webhook P2P con estado desconocido o no manejado.', ['order_id' => $order->id, 'bnc_transaction_id' => $bncP2PTransactionId, 'bnc_status' => $bncP2PStatus]);
                    break;
            }

            DB::commit();
            return response()->json(['message' => 'Webhook P2P procesado exitosamente.'], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Excepción al procesar webhook P2P del BNC: ' . $e->getMessage(), ['trace' => $e->getTraceAsString(), 'payload' => $request->all()]);
            return response()->json(['message' => 'Internal server error processing webhook.'], 500);
        }
    }
}