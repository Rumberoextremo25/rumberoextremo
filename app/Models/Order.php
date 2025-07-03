<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        // 'user_id',
        'total',
        'status',
        // otros campos que tengas en tu tabla
    ];

    protected $casts = [
        'created_at' => 'datetime', // Para manejar las fechas de creación fácilmente
        // otros campos si necesitas castearlos
    ];
}
