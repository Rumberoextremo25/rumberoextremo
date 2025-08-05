<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Partner;

class PartnerPayout extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'partner_id',
        'amount',
        'commission_amount',
        'status',
        'bank_name',
        'account_number',
        'account_type',
        'id_document',
        'transaction_reference',
        'processed_at',
        'notes',
    ];

    /**
     * Get the order that owns the payout.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the partner that owns the payout.
     */
    public function partner()
    {
        return $this->belongsTo(Ally::class);
    }
}