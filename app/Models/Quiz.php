<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Quiz extends Model
{
    //
    use HasFactory;

    protected $fillable = ['title', 'description', 'pass_mark', 'time_limit', 'allow_exceed_time_limit',];

    public function lesson()
    {
        return $this->morphOne(Lesson::class, 'lessonable');
    }

    public function questions()
    {
        return $this->belongsToMany(Question::class, 'quiz_question')->withPivot('order');
    }

    public function attempts()
    {
        return $this->hasMany(QuizAttempt::class);
    }
}
