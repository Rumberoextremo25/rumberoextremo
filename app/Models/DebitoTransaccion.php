<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DebitoTransaccion extends Model
{
    protected $fillable = [
        'user_id',
        'ally_id',
        'request_id',
        'transaction_id',
        'reference',
        'amount',
        'debtor_account',
        'debtor_bank',
        'debtor_id',
        'codigo_sms',
        'status',
        'bnc_response'
    ];
}
