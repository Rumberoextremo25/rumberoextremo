<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Sale extends Model
{
    use HasFactory;

    protected $table = 'sales';

    protected $fillable = [
        'client_id',
        'branch_id',
        'ally_id',
        'total_amount',
        'paid_amount',
        'payment_method',
        'bank_reference',
        'transaction_id',
        'status',
        'sale_date',
        'payment_date',
        'terminal',
        'destination_bank',
        'client_phone',
        'client_id_number',
        'description',
        'authorization_code',
        'bank_response'
    ];

    protected $casts = [
        'sale_date' => 'datetime',
        'payment_date' => 'datetime',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'bank_response' => 'array',
    ];

    /**
     * Relationship with client
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Relationship with branch
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Relationship with ally
     */
    public function ally(): BelongsTo
    {
        return $this->belongsTo(Ally::class);
    }

    /**
     * Relationship with payout
     */
    public function payout(): HasOne
    {
        return $this->hasOne(Payout::class);
    }

    /**
     * Scope for completed sales
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for sales between dates
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('sale_date', [$startDate, $endDate]);
    }
}
