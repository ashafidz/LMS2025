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
        'detect_face_not_detected',
        'detect_look_left',
        'detect_look_right',
        'detect_look_up',
        'detect_look_down',
        'violation_duration_seconds',
    ];

    protected $casts = [
        'enable_camera_detection' => 'boolean',
        'enable_tab_detection' => 'boolean',
        'enable_question_shuffle' => 'boolean',
        'camera_violation_threshold' => 'integer',
        'tab_violation_threshold' => 'integer',
        'face_detection_interval_seconds' => 'integer',
        'detect_face_not_detected' => 'boolean',
        'detect_look_left' => 'boolean',
        'detect_look_right' => 'boolean',
        'detect_look_up' => 'boolean',
        'detect_look_down' => 'boolean',
        'violation_duration_seconds' => 'integer',
    ];

    /**
     * Relationship: QuizSecuritySetting belongs to Quiz
     */
    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }
}
