<?php
// app/Models/Payout.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payout extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'ally_id',
        'sale_amount',
        'commission_percentage',
        'commission_amount',
        'net_amount',
        'ally_discount',
        'amount_after_discount',
        'company_transfer_amount',
        'company_commission',
        'company_account',
        'company_bank',
        'company_transfer_reference',
        'company_transfer_status',
        'company_transfer_date',
        'company_transfer_response',
        'status',
        'generation_date',
        'payment_date',
        'confirmed_at',
        'reverted_at',
        'sale_reference',
        'payment_reference',
        'ally_payment_method',
        'payment_proof_path',
        'batch_reference',
        'batch_processed_at',
        'reversion_reason'
    ];

    protected $casts = [
        'sale_amount' => 'decimal:2',
        'commission_percentage' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'ally_discount' => 'decimal:2',
        'amount_after_discount' => 'decimal:2',
        'company_transfer_amount' => 'decimal:2',
        'company_commission' => 'decimal:2',
        'company_transfer_date' => 'datetime',
        'generation_date' => 'datetime',
        'payment_date' => 'datetime',
        'confirmed_at' => 'datetime',
        'reverted_at' => 'datetime',
        'batch_processed_at' => 'datetime',
        'company_transfer_response' => 'array'
    ];

    // Relaciones
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function ally(): BelongsTo
    {
        return $this->belongsTo(Ally::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeReverted($query)
    {
        return $query->where('status', 'reverted');
    }

    // Métodos de utilidad
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function canBeReverted(): bool
    {
        return $this->isCompleted();
    }
}