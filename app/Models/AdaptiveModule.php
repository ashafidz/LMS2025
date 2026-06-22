<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AdaptiveModule extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'archetype_name',
        'title',
        'description',
        'order',
        'ai_generated',
        'ai_prompt_used',
    ];

    protected $casts = [
        'ai_generated' => 'boolean',
    ];

    // ─── Relationships ────────────────────────────────────────

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function lessons()
    {
        return $this->hasMany(AdaptiveLesson::class)->orderBy('order');
    }
}
