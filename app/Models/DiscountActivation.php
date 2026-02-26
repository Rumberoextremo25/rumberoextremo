<?php
// app/Models/DiscountActivation.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiscountActivation extends Model
{
    use HasFactory;

    /**
     * La tabla asociada al modelo.
     * ¡Importante! Usa el nombre correcto de tu tabla
     */
    protected $table = 'discount_activations';

    /**
     * Los atributos que se pueden asignar masivamente.
     */
    protected $fillable = [
        'user_id',
        'ally_id',
        'promotion_id',
        'code',
        'discount',
        'title',
        'status',
        'expires_at',
        'used_at',
        'metadata'
    ];

    /**
     * Los atributos que deben ser convertidos.
     */
    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relación con el usuario
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con el aliado
     */
    public function ally(): BelongsTo
    {
        return $this->belongsTo(Ally::class);
    }

    /**
     * Relación con la promoción
     */
    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    /**
     * Scope para activaciones activas
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                    ->where('expires_at', '>', now());
    }

    /**
     * Verificar si está activa
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && $this->expires_at > now();
    }

    /**
     * Marcar como usado
     */
    public function markAsUsed(): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        return $this->update([
            'status' => 'used',
            'used_at' => now()
        ]);
    }
}
