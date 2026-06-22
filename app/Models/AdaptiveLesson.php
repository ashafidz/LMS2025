<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AdaptiveLesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'adaptive_module_id',
        'title',
        'content',
        'order',
        'ai_generated',
        'ai_prompt_used',
    ];

    protected $casts = [
        'ai_generated' => 'boolean',
    ];

    // ─── Relationships ────────────────────────────────────────

    public function module()
    {
        return $this->belongsTo(AdaptiveModule::class, 'adaptive_module_id');
    }
}
