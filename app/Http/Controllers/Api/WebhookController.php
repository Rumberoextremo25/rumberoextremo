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
        // ¡DEBES IMPLEMENTAR ESTO SEGÚN LA DOCUMENTACIÓN DEL BNC!
        // Sin esto, tu endpoint es vulnerable.
        if (!$this->verifyBncWebhook($request)) {
            Log::warning('Firma de webhook C2P inválida o no autorizada.', ['payload' => $request->all()]);
            // Retorna 403 Forbidden si la verificación de seguridad falla
            return response()->json(['message' => 'Unauthorized or invalid webhook source.'], 403);
        }

        // Envuelve la lógica en una transacción de base de datos para garantizar la atomicidad
        DB::beginTransaction();
        try {
            // 2. Procesar el payload del webhook del BNC
            // **IMPORTANTE**: Ajusta estos nombres de campos según el payload REAL del BNC.
            $bncTransactionId = $request->input('value'); // Si el BNC usa 'value' como ID de transacción
            $bncStatus = $request->input('status');     // 'OK', 'ERROR'
            $bncMessage = $request->input('message');   // Mensaje del BNC
            // ... otros campos relevantes que el BNC envíe (ej. monto, referencia, fecha, etc.)

            if (empty($bncTransactionId) || empty($bncStatus)) {
                Log::error('Payload de webhook C2P incompleto o inesperado.', ['payload' => $request->all()]);
                // Retorna 400 Bad Request si el payload no tiene los datos esperados
                DB::rollBack();
                return response()->json(['message' => 'Bad Request: Missing required payload data.'], 400);
            }

            // 3. Buscar la orden/transacción en tu base de datos asociada a este pago.
            // Es CRÍTICO que el ID que uses para buscar sea el mismo que enviaste al BNC
            // en la petición inicial y que el BNC te devuelve en el webhook.
            // En tu PaymentController, usas $order->transaction_id = $bncResponse['value'].
            // Así que deberías buscar por ese campo.
            $order = Order::where('transaction_id', $bncTransactionId)->first();

            if (!$order) {
                Log::warning('Webhook C2P recibido, pero no se encontró la orden asociada en la DB.', [
                    'bnc_transaction_id' => $bncTransactionId,
                    'bnc_status' => $bncStatus,
                    'payload' => $request->all()
                ]);
                // Podría ser una notificación duplicada o para una orden antigua/inexistente.
                // Retornar 200 OK para que el BNC no reintente, ya que no es un error de procesamiento.
                DB::rollBack(); // Aunque no hay cambios, buena práctica mantenerlo
                return response()->json(['message' => 'Order not found, but webhook acknowledged.'], 200);
            }

            // 4. Actualizar el estado de la orden y/o pagos internos
            switch ($bncStatus) {
                case 'OK':
                    // Verifica si la orden ya está en el estado 'completed' para evitar re-procesamiento
                    if ($order->status === 'completed') {
                        Log::info('Webhook C2P para orden ya completada, no se requiere acción.', ['order_id' => $order->id, 'bnc_transaction_id' => $bncTransactionId]);
                        DB::commit();
                        return response()->json(['message' => 'Webhook already processed.'], 200);
                    }

                    $order->status = 'completed'; // O un estado más específico como 'paid_confirmed_c2p'
                    $order->payment_confirmed_at = now(); // Registrar la fecha de confirmación
                    $order->save();

                    // Si tienes un registro de PartnerPayout, también podrías actualizarlo aquí,
                    // por ejemplo, cambiando su estado de 'pending' a 'ready_for_payout' si aplica.
                    $partnerPayout = PartnerPayout::where('order_id', $order->id)->first();
                    if ($partnerPayout && $partnerPayout->status === 'pending') {
                         $partnerPayout->status = 'ready_for_payout'; // O el siguiente estado de tu flujo de pagos al aliado
                         $partnerPayout->notes .= " Confirmación C2P BNC: {$bncMessage}.";
                         $partnerPayout->save();
                    }

                    Log::info('Orden y pago C2P actualizados a "completed" por webhook.', ['order_id' => $order->id, 'bnc_transaction_id' => $bncTransactionId]);
                    break;

                case 'ERROR':
                    // Si el BNC envía 'ERROR', actualiza el estado de la orden a 'failed_payment'
                    if ($order->status !== 'failed_payment') { // Evitar actualizaciones redundantes
                        $order->status = 'failed_payment';
                        $order->payment_failed_at = now();
                        $order->notes .= " Fallo de pago C2P BNC: {$bncMessage}.";
                        $order->save();

                        // También podrías actualizar el PartnerPayout a un estado 'cancelled' o 'failed'
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
                    // Si hay otros estados que el BNC pueda enviar y debas manejar
                    Log::info('Webhook C2P con estado desconocido o no manejado.', ['order_id' => $order->id, 'bnc_transaction_id' => $bncTransactionId, 'bnc_status' => $bncStatus]);
                    break;
            }

            DB::commit(); // Confirma los cambios en la base de datos
            return response()->json(['message' => 'Webhook C2P procesado exitosamente.'], 200); // Retorna 200 OK

        } catch (\Exception $e) {
            DB::rollBack(); // Revierte si algo falla durante el procesamiento
            Log::error('Excepción al procesar webhook C2P del BNC: ' . $e->getMessage(), ['trace' => $e->getTraceAsString(), 'payload' => $request->all()]);
            // Retorna 500 Internal Server Error para indicar un problema en tu procesamiento
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
        if (!$this->verifyBncWebhook($request)) {
            Log::warning('Firma de webhook de tarjeta inválida o no autorizada.', ['payload' => $request->all()]);
            return response()->json(['message' => 'Unauthorized or invalid webhook source.'], 403);
        }

        DB::beginTransaction();
        try {
            // 2. Procesar el payload del webhook de Tarjeta del BNC
            // **IMPORTANTE**: Ajusta estos nombres de campos según el payload REAL del BNC.
            $bncCardTransactionId = $request->input('value'); // Si el BNC usa 'value' como ID de transacción
            $bncCardStatus = $request->input('status');      // 'OK', 'ERROR'
            $bncCardMessage = $request->input('message');    // Mensaje del BNC
            // ... otros campos relevantes (ej. CardType, AuthorizationCode)

            if (empty($bncCardTransactionId) || empty($bncCardStatus)) {
                Log::error('Payload de webhook de tarjeta incompleto o inesperado.', ['payload' => $request->all()]);
                DB::rollBack();
                return response()->json(['message' => 'Bad Request: Missing required payload data.'], 400);
            }

            // 3. Buscar la orden/transacción en tu base de datos.
            $order = Order::where('transaction_id', $bncCardTransactionId)->first();

            if (!$order) {
                Log::warning('Webhook de tarjeta recibido, pero no se encontró la orden asociada en la DB.', [
                    'bnc_transaction_id' => $bncCardTransactionId,
                    'bnc_status' => $bncCardStatus,
                    'payload' => $request->all()
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

                    $order->status = 'completed'; // O 'paid_confirmed_card'
                    $order->payment_confirmed_at = now();
                    $order->save();

                    $partnerPayout = PartnerPayout::where('order_id', $order->id)->first();
                    if ($partnerPayout && $partnerPayout->status === 'pending') {
                         $partnerPayout->status = 'ready_for_payout';
                         $partnerPayout->notes .= " Confirmación tarjeta BNC: {$bncCardMessage}.";
                         $partnerPayout->save();
                    }

                    Log::info('Orden y pago con tarjeta actualizados a "completed" por webhook.', ['order_id' => $order->id, 'bnc_transaction_id' => $bncCardTransactionId]);
                    break;

                case 'ERROR':
                    if ($order->status !== 'failed_payment') {
                        $order->status = 'failed_payment';
                        $order->payment_failed_at = now();
                        $order->notes .= " Fallo de pago con tarjeta BNC: {$bncCardMessage}.";
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

    /**
     * Función para verificar la autenticidad del webhook del BNC.
     * Esta implementación es un PLACEHOLDER y DEBE ser reemplazada por la lógica REAL
     * de verificación según la documentación de la API de Webhooks del BNC.
     *
     * Métodos comunes de verificación:
     * 1. Verificar una firma (hash) en un encabezado (ej. 'X-BNC-Signature') o en el cuerpo.
     * Necesitarás una "clave secreta" compartida entre tu app y el BNC.
     * 2. Verificar la IP de origen: Asegurarte de que la solicitud proviene de una IP conocida del BNC.
     * 3. Un "token secreto" como parámetro en la URL del webhook (menos seguro pero posible).
     *
     * @param Request $request
     * @return bool
     */
    private function verifyBncWebhook(Request $request): bool
    {
        // --- EJEMPLO DE VERIFICACIÓN CON FIRMA (la más común y segura) ---
        // 1. Obtén la clave secreta del webhook desde tu configuración (ej. config/bnc.php)
        // Asegúrate de que esta clave esté definida en tu .env y en config/bnc.php
        $secretKey = config('bnc.webhook_secret_key');
        if (empty($secretKey)) {
            Log::error('BNC_WEBHOOK_SECRET_KEY no configurada. La verificación del webhook está deshabilitada.');
            // En producción, esto debería lanzar una excepción o retornar false.
            // Para desarrollo, podrías permitirlo si estás probando manualmente.
            return true; // Solo para TESTING, NO en producción real.
        }

        // 2. Obtén la firma que el BNC debe enviar en los encabezados.
        // El nombre del encabezado (ej. 'X-BNC-Signature') debe ser provisto por la documentación del BNC.
        $bncSignature = $request->header('X-BNC-Signature'); // Ejemplo: cambia 'X-BNC-Signature' al header real
        if (empty($bncSignature)) {
            Log::warning('Webhook BNC recibido sin encabezado de firma. Rechazado.', ['headers' => $request->headers->all()]);
            return false;
        }

        // 3. Genera tu propia firma a partir del cuerpo de la petición y tu clave secreta.
        // El algoritmo de hash (ej. 'sha256') y cómo se forma la cadena para el hash
        // (ej. el cuerpo RAW de la petición) DEBE estar en la documentación del BNC.
        $payload = $request->getContent(); // Obtiene el cuerpo RAW de la petición
        $expectedSignature = hash_hmac('sha256', $payload, $secretKey); // Ejemplo: usar sha256 y el cuerpo raw

        // 4. Compara la firma generada con la firma recibida. Usa hash_equals para evitar ataques de temporización.
        if (!hash_equals($expectedSignature, $bncSignature)) {
            Log::warning('Firma de webhook BNC no coincide. Posible intento no autorizado.', [
                'received_signature' => $bncSignature,
                'expected_signature' => $expectedSignature,
                'payload_hash' => hash('sha256', $payload), // Para depuración, no en producción
            ]);
            return false;
        }

        Log::info('Webhook BNC verificado exitosamente por firma.');
        return true; // La firma es válida.

        // --- EJEMPLO DE VERIFICACIÓN POR IP (menos segura, pero una capa adicional) ---
        // $allowedIps = config('bnc.webhook_allowed_ips', []); // Array de IPs permitidas en config/bnc.php
        // if (!in_array($request->ip(), $allowedIps)) {
        //     Log::warning('Webhook BNC recibido de IP no autorizada.', ['ip' => $request->ip()]);
        //     return false;
        // }
        // return true;

        // --- RETORNA ESTO SOLO EN DESARROLLO, NUNCA EN PRODUCCIÓN SIN VERIFICACIÓN REAL ---
        // return true;
    }
}