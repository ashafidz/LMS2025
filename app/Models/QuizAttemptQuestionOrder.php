<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizAttemptQuestionOrder extends Model
{
    use HasFactory;

    // Karena hanya ada created_at, tidak ada updated_at
    public const UPDATED_AT = null;

    protected $table = 'quiz_attempt_question_order';

    protected $fillable = [
        'attempt_id',
        'question_id',
        'shuffled_order',
    ];

    protected $casts = [
        'shuffled_order' => 'integer',
        'created_at' => 'datetime',
    ];

    /**
     * Relationship: belongs to QuizAttempt
     */
    public function attempt()
    {
        return $this->belongsTo(QuizAttempt::class, 'attempt_id');
    }

    /**
     * Relationship: belongs to Question
     */
    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
