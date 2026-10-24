<?php

namespace App\Services;

use App\Models\PushSubscription;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

/**
 * Envío de notificaciones Web Push (VAPID) a los dispositivos suscritos.
 *
 * Cada administrador puede tener N suscripciones (móvil, escritorio, tablet).
 * Las suscripciones que el navegador da de baja (404/410) se eliminan solas.
 */
class WebPushService
{
    /**
     * ¿Están las claves VAPID configuradas?
     */
    public function isConfigured(): bool
    {
        return filled(config('webpush.vapid.public_key'))
            && filled(config('webpush.vapid.private_key'));
    }

    public function publicKey(): ?string
    {
        return config('webpush.vapid.public_key');
    }

    /**
     * Guarda (o actualiza) la suscripción de un dispositivo.
     *
     * @param  array{endpoint: string, keys: array{publicKey?: string, p256dh?: string, auth: string}}  $payload
     */
    public function subscribe(User $user, array $payload, ?string $userAgent = null): ?PushSubscription
    {
        $endpoint = $payload['endpoint'] ?? null;
        $keys = $payload['keys'] ?? [];

        if (blank($endpoint) || blank($keys['auth'] ?? null)) {
            return null;
        }

        return PushSubscription::updateOrCreate(
            ['user_id' => $user->id, 'endpoint' => $endpoint],
            [
                'public_key' => $keys['p256dh'] ?? $keys['publicKey'] ?? '',
                'auth_token' => $keys['auth'],
                'content_encoding' => $payload['contentEncoding']
                    ?? (str_contains($endpoint, 'mozilla') || str_contains($endpoint, 'google') ? 'aes128gcm' : 'aes128gcm'),
                'user_agent' => $userAgent ? mb_substr($userAgent, 0, 255) : null,
            ]
        );
    }

    /**
     * Elimina la suscripción de un dispositivo (o todas si no se indica).
     */
    public function unsubscribe(User $user, ?string $endpoint = null): int
    {
        $query = $user->pushSubscriptions();

        if (filled($endpoint)) {
            $query->where('endpoint', $endpoint);
        }

        return $query->delete();
    }

    /**
     * Envía un push a todos los dispositivos del usuario.
     *
     * @param  array{title: string, body: string, url?: string, tag?: string}  $message
     * @return int  Número de envíos aceptados por el servicio de push
     */
    public function sendToUser(User $user, array $message): int
    {
        if (! $this->isConfigured()) {
            return 0;
        }

        $subscriptions = $user->pushSubscriptions()->get();

        if ($subscriptions->isEmpty()) {
            return 0;
        }

        $payload = json_encode($this->normalize($message), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $webPush = new WebPush([
            'VAPID' => [
                'subject' => config('webpush.vapid.subject'),
                'publicKey' => config('webpush.vapid.public_key'),
                'privateKey' => config('webpush.vapid.private_key'),
            ],
        ], ['TTL' => (int) config('webpush.ttl', 3600)]);

        $sent = 0;

        foreach ($subscriptions as $subscription) {
            try {
                $report = $webPush->sendOneNotification(
                    Subscription::create($subscription->toWebPushArray()),
                    $payload
                );

                if ($report->isSuccess()) {
                    $sent++;
                }

                if ($report->isSubscriptionExpired()) {
                    $subscription->delete();
                }
            } catch (\Throwable $e) {
                Log::warning('webpush.send_failed', [
                    'user_id' => $user->id,
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $sent;
    }

    /**
     * Estructura que consume el service worker (public/sw.js).
     *
     * @param  array{title: string, body: string, url?: string, tag?: string}  $message
     * @return array<string, mixed>
     */
    private function normalize(array $message): array
    {
        return [
            'title' => mb_substr($message['title'] ?? 'Rumbero Extremo', 0, 120),
            'body' => mb_substr($message['body'] ?? '', 0, 220),
            'icon' => config('webpush.icon'),
            'badge' => config('webpush.icon'),
            'url' => $message['url'] ?? route('admin.notifications.index'),
            'tag' => $message['tag'] ?? 'rumbero-panel',
            'timestamp' => now()->timestamp * 1000,
        ];
    }
}
