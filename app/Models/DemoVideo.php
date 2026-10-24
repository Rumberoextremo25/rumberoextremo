<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class DemoVideo extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'source',
        'video_url',
        'video_path',
        'thumbnail_path',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'display_order' => 'integer',
    ];

    public function getYoutubeIdAttribute(): ?string
    {
        return self::extractYoutubeId($this->video_url);
    }

    public function getEmbedUrlAttribute(): ?string
    {
        $id = $this->youtube_id;

        return $id ? 'https://www.youtube.com/embed/' . $id . '?rel=0' : null;
    }

    public function getVideoSrcAttribute(): ?string
    {
        if ($this->source === 'youtube') {
            return $this->embed_url;
        }

        if (! $this->video_path) {
            return null;
        }

        if (str_starts_with($this->video_path, 'http')) {
            return $this->video_path;
        }

        return Storage::disk('public')->exists($this->video_path)
            ? asset('storage/' . ltrim($this->video_path, '/'))
            : null;
    }

    public function getThumbnailSrcAttribute(): ?string
    {
        if ($this->thumbnail_path) {
            if (str_starts_with($this->thumbnail_path, 'http')) {
                return $this->thumbnail_path;
            }

            if (Storage::disk('public')->exists($this->thumbnail_path)) {
                return asset('storage/' . ltrim($this->thumbnail_path, '/'));
            }
        }

        if ($this->source === 'youtube' && $this->youtube_id) {
            return 'https://img.youtube.com/vi/' . $this->youtube_id . '/hqdefault.jpg';
        }

        return null;
    }

    public function getIsYoutubeAttribute(): bool
    {
        return $this->source === 'youtube';
    }

    public static function extractYoutubeId(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        // Si ya es un ID de YouTube (11 caracteres habituales).
        if (preg_match('/^[A-Za-z0-9_-]{6,20}$/', $value)) {
            return $value;
        }

        if (preg_match(
            '~(?:youtube\.com/(?:watch\?v=|embed/|shorts/|live/)|youtu\.be/)([A-Za-z0-9_-]{6,20})~i',
            $value,
            $matches
        )) {
            return $matches[1];
        }

        return null;
    }
}
