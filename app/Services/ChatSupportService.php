<?php

namespace App\Services;

use App\Models\ChatMessage;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\AdminActivityNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Lógica del chat de soporte.
 *
 * Regla del negocio:
 *  1. Si hay una IA configurada (DeepSeek o Gemini) y está habilitada, responde
 *     automáticamente y la conversación se queda en el chat.
 *  2. Si no hay IA, si falla, o si el usuario pide a una persona, el mensaje se
 *     deriva al Centro de Mensajes: se notifica en la campana del panel, se
 *     envía un correo al buzón de soporte y se ofrece continuar por WhatsApp.
 */
class ChatSupportService
{
    /**
     * Palabras con las que el usuario pide atención humana.
     */
    private const HUMAN_KEYWORDS = [
        'asesor', 'persona', 'humano', 'agente', 'soporte', 'ayuda real',
        'hablar con alguien', 'representante', 'ejecutivo', 'whatsapp', 'telefono',
        'teléfono', 'llamada',
    ];

    public function __construct(private AIService $ai)
    {
    }

    /**
     * ¿Hay alguna IA utilizable? (clave presente y chat IA habilitado)
     */
    public function aiAvailable(): bool
    {
        $enabled = (string) Setting::get('chat_ai_enabled', '1') === '1';

        return $enabled
            && (filled(config('gemini.api_key')) || filled(config('deepseek.api_key')));
    }

    /**
     * Guarda el mensaje del usuario y decide quién responde.
     *
     * @return array{mode: string, reply: string, message_id: int, handoff: bool}
     */
    public function handleIncoming(string $sessionId, string $mensaje, ?User $user): array
    {
        $userMessage = ChatMessage::create([
            'user_id' => $user?->id,
            'session_id' => $sessionId,
            'message' => $mensaje,
            'sender' => 'user',
            'status' => 'pending',
        ]);

        // 1) ¿Pide hablar con una persona?
        if ($this->wantsHuman($mensaje)) {
            $this->handoff($sessionId, $mensaje, $user);

            return [
                'mode' => 'human',
                'reply' => $this->handoffMessage($user),
                'message_id' => $userMessage->id,
                'handoff' => true,
            ];
        }

        // 2) ¿Hay IA? Entonces responde ella
        if ($this->aiAvailable()) {
            $reply = $this->askAi($sessionId, $mensaje, $user);

            if ($reply !== null) {
                $aiMessage = ChatMessage::create([
                    'user_id' => $user?->id,
                    'session_id' => $sessionId,
                    'message' => $reply,
                    'sender' => 'ai',
                    'status' => 'answered',
                    'answered_at' => now(),
                ]);

                $userMessage->update(['status' => 'answered', 'answered_at' => now()]);

                return [
                    'mode' => 'ai',
                    'reply' => $reply,
                    'message_id' => $aiMessage->id,
                    'handoff' => false,
                ];
            }

            Log::warning('chat.ai_failed_fallback_human', ['session_id' => $sessionId]);
        }

        // 3) Sin IA (o falló): al Centro de Mensajes
        $this->handoff($sessionId, $mensaje, $user);

        return [
            'mode' => 'human',
            'reply' => $this->handoffMessage($user),
            'message_id' => $userMessage->id,
            'handoff' => true,
        ];
    }

    /**
     * Consulta a la IA con un poco de contexto de la conversación.
     *
     * Cadena de respaldo: Gemini → DeepSeek. Si ninguno responde de verdad
     * (clave inválida, sin saldo, cuota…) devuelve null y el chat deriva a un
     * asesor en lugar de guardar un mensaje de error como respuesta.
     */
    private function askAi(string $sessionId, string $mensaje, ?User $user): ?string
    {
        $history = ChatMessage::where('session_id', $sessionId)
            ->latest()
            ->limit(6)
            ->get()
            ->reverse()
            ->map(fn (ChatMessage $m) => ($m->sender === 'user' ? 'Cliente' : 'Asistente') . ': ' . $m->message)
            ->implode("\n");

        $prompt = $history !== ''
            ? "Contexto de la conversación:\n{$history}\n\nNuevo mensaje del cliente: {$mensaje}"
            : $mensaje;

        // 1) Gemini (a través de AIService)
        try {
            $result = $this->ai->chat($prompt, $user?->id);
            $reply = trim((string) ($result['respuesta'] ?? ''));
            $used = $result['ia_utilizada'] ?? 'gemini';

            if ($used !== 'local' && ! $this->isErrorReply($reply)) {
                return $reply;
            }

            Log::warning('chat.gemini_without_valid_reply', ['used' => $used]);
        } catch (\Throwable $e) {
            Log::warning('chat.gemini_error', ['error' => $e->getMessage()]);
        }

        // 2) DeepSeek como respaldo
        try {
            $deepseek = app(\App\Services\DeepSeekService::class)->chat($prompt, $user?->id);
            $reply = trim((string) ($deepseek['respuesta'] ?? ''));

            if (! $this->isErrorReply($reply)) {
                return $reply;
            }
        } catch (\Throwable $e) {
            Log::warning('chat.deepseek_error', ['error' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * ¿El texto es en realidad un mensaje de error del proveedor?
     */
    private function isErrorReply(string $reply): bool
    {
        if (mb_strlen($reply) < 2) {
            return true;
        }

        $needles = [
            'error en el servicio',
            'problemas técnicos',
            'problemas tecnicos',
            'configuración de ia no disponible',
            'configuracion de ia no disponible',
        ];

        $lower = Str::lower($reply);

        foreach ($needles as $needle) {
            if (str_contains($lower, $needle)) {
                return true;
            }
        }

        return false;
    }

    private function wantsHuman(string $mensaje): bool
    {
        $text = Str::lower(Str::ascii($mensaje));

        foreach (self::HUMAN_KEYWORDS as $keyword) {
            if (str_contains($text, $keyword)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Avisa al equipo: campana del panel + correo al buzón de soporte.
     */
    public function handoff(string $sessionId, string $mensaje, ?User $user): void
    {
        $this->notifyPanel($sessionId, $mensaje, $user);

        // El envío del correo no debe hacer esperar al usuario en el chat:
        // se ejecuta después de devolver la respuesta.
        $send = fn () => $this->sendEmail($sessionId, $mensaje, $user);

        if (function_exists('defer')) {
            defer($send);
        } else {
            $send();
        }
    }

    /**
     * Notificación interna (campana de la toolbar) para los administradores.
     */
    private function notifyPanel(string $sessionId, string $mensaje, ?User $user): void
    {
        try {
            $payload = [
                'action' => 'created',
                'entity' => 'chat',
                'entity_label' => 'Mensaje de chat',
                'entity_id' => $sessionId,
                'title' => 'Nuevo mensaje en el chat',
                'subject' => $user?->name ?? 'Visitante',
                'message' => Str::limit($mensaje, 90) . ($user ? ' · ' . $user->name : ' · visitante'),
                'changes' => [],
                'icon' => 'fa-solid fa-headset',
                'color' => 'info',
                'url' => route('admin.chat', ['session' => $sessionId]),
                'actor_name' => $user?->name ?? 'Visitante',
            ];

            User::query()
                ->where(function ($query) {
                    $query->where('role', 'admin')->orWhere('user_type', 'admin');
                })
                ->get()
                ->each(function (User $admin) use ($payload) {
                    $admin->notify(new AdminActivityNotification($payload));
                });
        } catch (\Throwable $e) {
            Log::warning('chat.notify_panel_failed', ['error' => $e->getMessage()]);
        }

        $this->recordActivity($sessionId, $mensaje, $user);
    }

    /**
     * Correo al buzón de soporte configurado.
     */
    private function sendEmail(string $sessionId, string $mensaje, ?User $user): void
    {
        $email = Setting::get('support_email', 'soporteitsolutech@gmail.com');

        if (blank($email)) {
            return;
        }

        try {
            Mail::to($email)->send(new \App\Mail\ChatSupportMail(
                sessionId: $sessionId,
                body: $mensaje,
                userName: $user?->name ?? 'Visitante',
                userEmail: $user?->email,
                chatUrl: route('admin.chat', ['session' => $sessionId]),
                whatsappUrl: $this->whatsappUrl($mensaje),
            ));
        } catch (\Throwable $e) {
            Log::warning('chat.support_email_failed', [
                'error' => $e->getMessage(),
                'to' => $email,
            ]);
        }
    }

    /**
     * Deja constancia en activity_logs (alimenta el dashboard).
     */
    private function recordActivity(string $sessionId, string $mensaje, ?User $user): void
    {
        try {
            \App\Models\ActivityLog::create([
                'activity_type' => 'chat.mensaje',
                'description' => 'Nuevo mensaje de chat de ' . ($user?->name ?? 'visitante') . ': ' . Str::limit($mensaje, 80),
                'user_id' => $user?->id,
                'performed_by' => null,
                'status' => 'pending',
                'data' => ['session_id' => $sessionId],
            ]);
        } catch (\Throwable) {
            // silencioso: la auditoría no debe romper el chat
        }
    }

    /**
     * Enlace de WhatsApp con el mensaje precargado.
     */
    public function whatsappUrl(?string $context = null): ?string
    {
        // Sin valor por defecto vacío: así se aplica el número de Setting::DEFAULTS
        $number = preg_replace('/\D+/', '', (string) Setting::get('support_whatsapp'));

        if (blank($number)) {
            return null;
        }

        $text = $context
            ? 'Hola, vengo del chat de Rumbero Extremo. Consulta: ' . Str::limit($context, 200)
            : 'Hola, vengo del chat de Rumbero Extremo y necesito ayuda.';

        return 'https://wa.me/' . $number . '?text=' . rawurlencode($text);
    }

    /**
     * Texto que ve el usuario cuando la conversación pasa a un asesor.
     */
    public function handoffMessage(?User $user): string
    {
        $whatsapp = $this->whatsappUrl();

        $message = "Recibí tu mensaje y ya lo envié al equipo de soporte.\n\n"
            . "Un asesor va a responderte por este mismo chat en breve. También recibirás la respuesta en tu correo si lo dejaste registrado.";

        if ($whatsapp) {
            $message .= "\n\nSi prefieres atención inmediata, puedes continuar por WhatsApp.";
        }

        return $message;
    }
}
