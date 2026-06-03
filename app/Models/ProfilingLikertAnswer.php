<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfilingLikertAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'attempt_id',
        'question_id',
        'answer_value',
    ];

    public function attempt()
    {
        return $this->belongsTo(ProfilingAttempt::class, 'attempt_id');
    }

    public function question()
    {
        return $this->belongsTo(ProfilingQuestion::class, 'question_id');
    }
}
