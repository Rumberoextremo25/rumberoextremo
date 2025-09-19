<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payout extends Model
{
    use HasFactory;

    protected $table = 'payouts';

    protected $fillable = [
        'sale_id',
        'ally_id',
        'sale_amount',
        'commission_percentage',
        'commission_amount',
        'status',
        'generation_date',
        'payment_date',
        'sale_reference',
        'payment_reference',
        'ally_payment_method',
        'ally_account_number',
        'ally_bank',
        'payment_proof',
        'notes'
    ];

    protected $casts = [
        'generation_date' => 'datetime',
        'payment_date' => 'datetime',
        'sale_amount' => 'decimal:2',
        'commission_percentage' => 'decimal:2',
        'commission_amount' => 'decimal:2',
    ];

    // Possible statuses
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_PAID = 'paid';
    const STATUS_ERROR = 'error';

    /**
     * Relationship with sale
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Relationship with ally
     */
    public function ally(): BelongsTo
    {
        return $this->belongsTo(Ally::class);
    }

    /**
     * Scope for pending payouts
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for processing payouts
     */
    public function scopeProcessing($query)
    {
        return $query->where('status', self::STATUS_PROCESSING);
    }

    /**
     * Scope for paid payouts
     */
    public function scopePaid($query)
    {
        return $query->where('status', self::STATUS_PAID);
    }

    /**
     * Scope for payouts between dates
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('generation_date', [$startDate, $endDate]);
    }

    /**
     * Mark as pending
     */
    public function markAsPending(): void
    {
        $this->update(['status' => self::STATUS_PENDING]);
    }

    /**
     * Mark as processing
     */
    public function markAsProcessing(): void
    {
        $this->update(['status' => self::STATUS_PROCESSING]);
    }

    /**
     * Mark as paid
     */
    public function markAsPaid($paymentReference, $paymentDate): void
    {
        $this->update([
            'status' => self::STATUS_PAID,
            'payment_reference' => $paymentReference,
            'payment_date' => $paymentDate
        ]);
    }

    /**
     * Get status in readable format
     */
    public function getStatusTextAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'Pending',
            self::STATUS_PROCESSING => 'Processing',
            self::STATUS_PAID => 'Paid',
            self::STATUS_ERROR => 'Error',
            default => 'Unknown'
        };
    }
}