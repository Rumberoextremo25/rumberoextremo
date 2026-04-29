<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    protected $fillable = [
        'user_id',
        'session_id',
        'message',
        'sender',
        'status',
        'read_at',
        'answered_at',
        'answered_by'
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'answered_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function answeredBy()
    {
        return $this->belongsTo(User::class, 'answered_by');
    }
}