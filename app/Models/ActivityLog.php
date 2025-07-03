<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'activity_type',
        'description',
        'user_id',
        'performed_by',
        'status',
        'data'
    ];

    // Si estás usando la columna 'data' para JSON, Laravel lo maneja automáticamente,
    // pero puedes especificar el cast si quieres mayor control.
    protected $casts = [
        'data' => 'array',
    ];

    // Define la relación con el usuario (opcional, si user_id está en tu tabla)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
