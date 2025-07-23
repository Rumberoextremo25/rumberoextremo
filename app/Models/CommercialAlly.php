<?php

// app/Models/CommercialAlly.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommercialAlly extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'logo_url',
        'rating',
        'description',
        'website_url',
    ];

    protected $casts = [
        'rating' => 'double',
    ];
}
