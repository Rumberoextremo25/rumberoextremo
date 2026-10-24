<?php

// app/Models/CommercialAlly.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CommercialAlly extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'logo_url',
        'rating',
        'description',
        'website_url',
        'is_active',
    ];

    protected $casts = [
        'rating' => 'double',
        'is_active' => 'boolean',
    ];

    /**
     * URL pública del logo (la columna guarda la ruta relativa del disco).
     * Devuelve null si el archivo no existe para no dejar imágenes rotas.
     */
    public function getLogoSrcAttribute(): ?string
    {
        $path = $this->logo_url;

        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http')) {
            return $path;
        }

        return Storage::disk('public')->exists($path)
            ? asset('storage/' . ltrim($path, '/'))
            : null;
    }

    public function ally()
    {
        return $this->belongsTo(CommercialAlly::class, 'ally_id');
    }
}
