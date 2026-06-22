<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiChatMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'ai_chat_session_id',
        'role',
        'content',
        'has_action',
        'action_data',
    ];

    protected $casts = [
        'has_action' => 'boolean',
        'action_data' => 'array',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(AiChatSession::class, 'ai_chat_session_id');
    }
}
