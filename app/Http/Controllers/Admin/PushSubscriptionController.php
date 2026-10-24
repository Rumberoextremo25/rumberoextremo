<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\WebPushService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Gestión de la suscripción Web Push del navegador actual.
 */
class PushSubscriptionController extends Controller
{
    public function __construct(private WebPushService $webPush)
    {
    }

    /**
     * Clave pública VAPID (la necesita el navegador para suscribirse).
     */
    public function publicKey(): JsonResponse
    {
        return response()->json([
            'publicKey' => $this->webPush->publicKey(),
            'configured' => $this->webPush->isConfigured(),
        ]);
    }

    /**
     * Guarda la suscripción enviada por el navegador.
     */
    public function subscribe(Request $request): JsonResponse
    {
        $data = $request->validate([
            'endpoint' => ['required', 'string', 'max:500'],
            'keys.auth' => ['required', 'string'],
            'keys.p256dh' => ['required', 'string'],
        ]);

        $subscription = $this->webPush->subscribe(
            $request->user(),
            $data,
            $request->userAgent()
        );

        if (! $subscription) {
            return response()->json(['success' => false, 'message' => 'Suscripción inválida.'], 422);
        }

        $request->user()->forceFill(['push_enabled' => true])->save();

        return response()->json([
            'success' => true,
            'message' => 'Notificaciones push activadas en este dispositivo.',
            'devices' => $request->user()->pushSubscriptions()->count(),
        ]);
    }

    /**
     * Elimina la suscripción del dispositivo actual (o todas).
     */
    public function unsubscribe(Request $request): JsonResponse
    {
        $endpoint = $request->input('endpoint');
        $this->webPush->unsubscribe($request->user(), $endpoint);

        $remaining = $request->user()->pushSubscriptions()->count();

        if ($remaining === 0) {
            $request->user()->forceFill(['push_enabled' => false])->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Notificaciones push desactivadas.',
            'devices' => $remaining,
        ]);
    }

    /**
     * Envía una notificación de prueba al usuario autenticado.
     */
    public function test(Request $request): JsonResponse
    {
        if (! $this->webPush->isConfigured()) {
            return response()->json([
                'success' => false,
                'message' => 'Faltan las claves VAPID en el .env (php artisan webpush:vapid).',
            ], 422);
        }

        if ($request->user()->pushSubscriptions()->count() === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Este dispositivo no está suscrito todavía.',
            ], 422);
        }

        $sent = $this->webPush->sendToUser($request->user(), [
            'title' => 'Notificación de prueba',
            'body' => 'Si ves este mensaje, las notificaciones push funcionan en ' . now()->format('d/m/Y H:i'),
            'url' => route('admin.notifications.index'),
            'tag' => 'rumbero-test',
        ]);

        return response()->json([
            'success' => $sent > 0,
            'message' => $sent > 0
                ? 'Notificación enviada a ' . $sent . ' dispositivo(s).'
                : 'El servicio de push rechazó el envío (revisa los logs).',
            'sent' => $sent,
        ]);
    }
}
