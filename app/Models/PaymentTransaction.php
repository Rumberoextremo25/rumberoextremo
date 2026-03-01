<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'ally_id',
        'original_amount',
        'discount_percentage',
        'amount_to_ally',
        'platform_commission',
        'payment_method',
        'status',
        'reference_code',
        'confirmation_data',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'confirmation_data' => 'array',
        'original_amount' => 'decimal:2',
        'amount_to_ally' => 'decimal:2',
        'platform_commission' => 'decimal:2',
    ];

    /**
     * Get the user that made the transaction.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the ally that receives the transaction.
     */
    public function ally()
    {
        return $this->belongsTo(Ally::class, 'ally_id');
    }
}