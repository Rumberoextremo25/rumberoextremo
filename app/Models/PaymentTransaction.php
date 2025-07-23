<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    use HasFactory;

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

    protected $casts = [
        'confirmation_data' => 'array',
        'original_amount' => 'float',
        'discount_percentage' => 'float',
        'amount_to_ally' => 'float',
        'platform_commission' => 'float',
    ];

    public function ally()
    {
        return $this->belongsTo(Ally::class);
    }
    // Si tienes usuarios:
    // public function user()
    // {
    //     return $this->belongsTo(User::class);
    // }
}