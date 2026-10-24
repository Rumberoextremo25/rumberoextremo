<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class HomeSlide extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'alt_text',
        'image_path',
        'target_url',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'display_order' => 'integer',
    ];

    public function getImageSrcAttribute(): ?string
    {
        $path = $this->image_path;

        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http')) {
            return $path;
        }

        // Imágenes que ya vienen con el proyecto (las iniciales).
        if (str_starts_with($path, 'assets/')) {
            return asset($path);
        }

        return Storage::disk('public')->exists($path)
            ? asset('storage/' . ltrim($path, '/'))
            : null;
    }

    public function getIsStaticAttribute(): bool
    {
        return str_starts_with((string) $this->image_path, 'assets/');
    }
}
