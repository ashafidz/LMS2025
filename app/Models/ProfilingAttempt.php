<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfilingAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'course_id',
        'status',
        'current_component',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function likertAnswers()
    {
        return $this->hasMany(ProfilingLikertAnswer::class, 'attempt_id');
    }

    public function mcqAnswers()
    {
        return $this->hasMany(ProfilingMcqAnswer::class, 'attempt_id');
    }

    public function componentScores()
    {
        return $this->hasMany(ProfilingComponentScore::class, 'attempt_id');
    }
}
