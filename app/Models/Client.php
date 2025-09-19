<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasFactory;

    protected $table = 'clients';

    protected $fillable = [
        'name',
        'document_type',
        'id_number',
        'phone',
        'email',
        'address',
        'status'
    ];

    // Document types
    const DOCUMENT_V = 'V';
    const DOCUMENT_E = 'E';
    const DOCUMENT_J = 'J';

    // Possible statuses
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    /**
     * Relationship with sales
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * Scope for active clients
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Get document type in readable format
     */
    public function getDocumentTypeTextAttribute(): string
    {
        return match($this->document_type) {
            self::DOCUMENT_V => 'Venezuelan',
            self::DOCUMENT_E => 'Foreign',
            self::DOCUMENT_J => 'Legal',
            default => 'Unknown'
        };
    }

    /**
     * Get status in readable format
     */
    public function getStatusTextAttribute(): string
    {
        return match($this->status) {
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
            default => 'Unknown'
        };
    }
}