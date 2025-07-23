<?php

// app/Models/Promotion.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'image_url',
        'discount',
        'price',
        'expires_at',
        'description',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];
}
