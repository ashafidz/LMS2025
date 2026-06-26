<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdaptiveLessonPointAward extends Model
{
    use HasFactory;

    protected $fillable = [
        'adaptive_lesson_id',
        'student_id',
        'instructor_id',
        'points',
    ];

    public function adaptiveLesson()
    {
        return $this->belongsTo(AdaptiveLesson::class);
    }
}
