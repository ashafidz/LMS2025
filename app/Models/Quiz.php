<?php

namespace App\Models;

use App\Traits\HasLocalDates;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Quiz extends Model
{
    //
    use HasFactory;
    use HasLocalDates;

    protected $fillable = [
        'title',
        'description',
        'pass_mark',
        'time_limit',
        'allow_exceed_time_limit',
        'reveal_answers',
        'max_attempts',
    ];
    protected $casts = [
        'allow_exceed_time_limit' => 'boolean',
        'reveal_answers' => 'boolean', // Tambahkan ini
    ];

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
