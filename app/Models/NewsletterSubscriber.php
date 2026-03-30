<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewsletterSubscriber extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'ip_address',
        'user_agent',
        'confirmed_at',
        'confirmation_token',
        'subscribed_at',
    ];

    // Opcional: puedes agregar casts para fechas
    protected $casts = [
        'confirmed_at' => 'datetime',
        'subscribed_at' => 'datetime',
    ];

    // Método auxiliar para verificar si el suscriptor está confirmado
    public function isConfirmed(): bool
    {
        return !is_null($this->confirmed_at);
    }

    // Método para confirmar la suscripción
    public function confirm(): void
    {
        $this->update([
            'confirmed_at' => now(),
            'confirmation_token' => null, // Limpiar el token después de confirmar
        ]);
    }
}
