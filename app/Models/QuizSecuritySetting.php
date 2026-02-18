<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizSecuritySetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_id',
        'enable_camera_detection',
        'enable_tab_detection',
        'enable_question_shuffle',
        'camera_violation_threshold',
        'tab_violation_threshold',
        'face_detection_interval_seconds',
    ];

    protected $casts = [
        'enable_camera_detection' => 'boolean',
        'enable_tab_detection' => 'boolean',
        'enable_question_shuffle' => 'boolean',
        'camera_violation_threshold' => 'integer',
        'tab_violation_threshold' => 'integer',
        'face_detection_interval_seconds' => 'integer',
    ];

    /**
     * Relationship: QuizSecuritySetting belongs to Quiz
     */
    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }
}
