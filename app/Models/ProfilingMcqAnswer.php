<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfilingMcqAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'attempt_id',
        'question_id',
        'selected_option_id',
        'is_correct',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
    ];

    public function attempt()
    {
        return $this->belongsTo(ProfilingAttempt::class, 'attempt_id');
    }

    public function question()
    {
        return $this->belongsTo(CourseKnowledgeQuestion::class, 'question_id');
    }

    public function selectedOption()
    {
        return $this->belongsTo(CourseKnowledgeOption::class, 'selected_option_id');
    }
}
