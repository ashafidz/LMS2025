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
        'lesson_type',          // 'article' | 'assignment' | 'video' | 'quiz' | 'lessonpoin'
        'video_url',
        'lessonpoin_title',
        'lessonpoin_description',
        'content',
        'assignment_instructions',
        'assignment_max_score',
        'quiz_data',
        'order',
        'ai_generated',
        'ai_prompt_used',
    ];

    protected $casts = [
        'ai_generated'         => 'boolean',
        'assignment_max_score' => 'integer',
        'quiz_data'            => 'array',
    ];

    // ─── Relationships ────────────────────────────────────────

    public function module()
    {
        return $this->belongsTo(AdaptiveModule::class, 'adaptive_module_id');
    }
}
