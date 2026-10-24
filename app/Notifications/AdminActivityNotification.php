<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

/**
 * Notificación de actividad del panel.
 *
 * Es genérica a propósito: cualquier cambio (crear, editar o eliminar un
 * usuario, aliado, promoción, banner…) se describe con un array de datos y se
 * guarda en la tabla `notifications` mediante el canal database.
 */
class AdminActivityNotification extends Notification
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(public array $payload)
    {
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Datos que consume la campana de la toolbar.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return $this->payload;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return $this->payload;
    }
}
