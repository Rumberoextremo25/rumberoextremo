<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Promotion extends Model
{
    use HasFactory;

    protected $table = 'promotions';

    protected $fillable = [
        'ally_id',
        'title',
        'image_url',
        'discount',
        'price',
        'description',
        'terms_conditions',
        'expires_at',
        'status',
        'max_uses',
        'current_uses',
        'metadata'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'max_uses' => 'integer',
        'current_uses' => 'integer',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relación: Una promoción pertenece a un aliado
     */
    public function ally()
    {
        return $this->belongsTo(Ally::class, 'ally_id');
    }

    /**
     * Scope para promociones activas
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                    ->where(function($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    });
    }

    /**
     * Scope para promociones por vencer (próximos 7 días)
     */
    public function scopeExpiringSoon($query)
    {
        return $query->whereNotNull('expires_at')
                    ->where('expires_at', '>', now())
                    ->where('expires_at', '<', now()->addDays(7));
    }

    /**
     * Scope para promociones más usadas
     */
    public function scopeMostUsed($query)
    {
        return $query->orderBy('current_uses', 'desc');
    }

    /**
     * Verificar si la promoción está disponible
     */
    public function isAvailable(): bool
    {
        return $this->status === 'active' 
            && ($this->expires_at === null || $this->expires_at > now())
            && ($this->max_uses === null || $this->current_uses < $this->max_uses);
    }

    /**
     * Verificar si la promoción expira pronto
     */
    public function isExpiringSoon(): bool
    {
        return $this->expires_at 
            && $this->expires_at > now() 
            && $this->expires_at < now()->addDays(7);
    }

    /**
     * Incrementar contador de usos
     */
    public function incrementUses(): bool
    {
        if (!$this->isAvailable()) {
            return false;
        }

        $this->increment('current_uses');
        
        // Si llegó al límite, desactivar automáticamente
        if ($this->max_uses && $this->current_uses >= $this->max_uses) {
            $this->update(['status' => 'inactive']);
        }
        
        return true;
    }

    /**
     * Obtener días restantes para expirar
     */
    public function getDaysRemainingAttribute(): ?int
    {
        if (!$this->expires_at || $this->expires_at < now()) {
            return null;
        }
        
        return now()->diffInDays($this->expires_at);
    }

    /**
     * Obtener descuento formateado con emoji
     */
    public function getFormattedDiscountAttribute(): string
    {
        if (str_contains($this->discount, '%')) {
            return "🔥 {$this->discount} de descuento";
        } elseif (str_contains($this->discount, 'x1')) {
            return "🎁 {$this->discount}";
        } elseif (preg_match('/[€$]/', $this->discount)) {
            return "💰 {$this->discount}";
        }
        return "🏷️ {$this->discount}";
    }

    /**
     * Activar una promoción para un usuario
     */
    public function activateForUser($userId)
    {
        if (!$this->isAvailable()) {
            throw new \Exception('Promoción no disponible');
        }

        // Generar código único
        $code = 'RUM-' . strtoupper(substr(md5($userId . $this->id . time()), 0, 8));

        // Registrar activación
        $activation = DiscountActivation::create([
            'user_id' => $userId,
            'ally_id' => $this->ally_id,
            'promotion_id' => $this->id,
            'code' => $code,
            'discount' => $this->discount,
            'title' => $this->title,
            'status' => 'active',
            'expires_at' => $this->expires_at ?? now()->addDays(7),
        ]);

        // Incrementar contador
        $this->incrementUses();

        return $activation;
    }
}
