<?php

// app/Models/Banner.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'image_url',
        'description',
        'target_url',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'display_order' => 'integer',
    ];

    /**
     * URL pública de la imagen (la columna guarda la ruta relativa del disco).
     * Devuelve null si el archivo no existe para no dejar imágenes rotas.
     */
    public function getImageSrcAttribute(): ?string
    {
        $path = $this->image_url;

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
}
