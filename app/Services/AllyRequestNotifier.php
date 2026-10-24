<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\User;
use App\Notifications\AdminActivityNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Avisos de solicitudes de aliados/afiliados:
 *  - correo al buzón de ventas configurado (Ajustes → Correo de ventas)
 *  - notificación en la campana del panel, con enlace a Solicitudes
 *
 * Centralizado para que el formulario público y el registro usen la misma
 * lógica.
 */
class AllyRequestNotifier
{
    /**
     * @param  array<string, mixed>  $fields  etiqueta => valor (se muestran en el correo)
     */
    public function send(string $kind, array $fields, ?string $email = null, ?string $name = null): void
    {
        $recipient = Setting::get('sales_email', 'soporteitsolutech@gmail.com');

        $mail = function () use ($recipient, $kind, $fields) {
            try {
                Mail::to($recipient)->send(new \App\Mail\AllyRequestMail($fields, $kind));
            } catch (\Throwable $e) {
                Log::warning('ally_request.mail_failed', [
                    'error' => $e->getMessage(),
                    'to' => $recipient,
                ]);
            }
        };

        // Tras enviar la respuesta al usuario
        function_exists('defer') ? defer($mail) : $mail();

        try {
            $payload = [
                'action' => 'created',
                'entity' => 'solicitud',
                'entity_label' => $kind === 'afiliado' ? 'Solicitud de afiliado' : 'Solicitud de local',
                'entity_id' => null,
                'title' => $kind === 'afiliado' ? 'Nueva solicitud de afiliado' : 'Nueva solicitud de local',
                'subject' => $name,
                'message' => ($name ?? 'Solicitud') . ($email ? ' · ' . $email : ''),
                'changes' => [],
                'icon' => 'fa-solid fa-file-signature',
                'color' => 'warning',
                'url' => route('admin.requests.index'),
                'actor_name' => $name,
            ];

            User::query()
                ->where(function ($q) {
                    $q->where('role', 'admin')->orWhere('user_type', 'admin');
                })
                ->get()
                ->each(fn ($admin) => $admin->notify(new AdminActivityNotification($payload)));
        } catch (\Throwable $e) {
            Log::warning('ally_request.notify_failed', ['error' => $e->getMessage()]);
        }
    }
}
