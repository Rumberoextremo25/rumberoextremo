<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AffiliateApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'social_media_link',
        'followers_count',
        'affiliate_type',
        'message',
        'terms',
        'status',
        'admin_notes',
        'reviewed_at',
        'reviewed_by',
    ];

    protected $casts = [
        'terms' => 'boolean',
        'followers_count' => 'integer',
        'reviewed_at' => 'datetime',
    ];
}
