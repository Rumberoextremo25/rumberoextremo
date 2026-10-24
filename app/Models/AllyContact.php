<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AllyContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_name',
        'contact_person',
        'email',
        'phone',
        'partnership_type',
        'website',
        'message',
        'status',
        'admin_notes',
        'reviewed_at',
        'reviewed_by',
    ];

    /**
     * reviewed_at debe ser Carbon: antes llegaba como string y rompía
     * diffForHumans() en la vista de solicitudes.
     */
    protected $casts = [
        'reviewed_at' => 'datetime',
    ];
}