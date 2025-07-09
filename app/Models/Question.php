<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'topic_id',
        'question_text',
        'question_type',
        'score',
    ];

    /**
     * The topic this question belongs to.
     */
    public function topic()
    {
        return $this->belongsTo(QuestionTopic::class, 'topic_id');
    }

    /**
     * The options available for this question.
     */
    public function options()
    {
        return $this->hasMany(QuestionOption::class);
    }

    /**
     * The quizzes that include this question.
     */
    public function quizzes()
    {
        return $this->belongsToMany(Quiz::class, 'quiz_question');
    }
}
