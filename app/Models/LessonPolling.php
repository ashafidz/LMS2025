<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LessonPolling extends Model
{
    protected $fillable = ['question', 'description', 'allow_multiple', 'max_choices', 'is_active', 'start_time', 'end_time', 'show_voters', 'show_results'];

    protected $casts = [
        'is_active' => 'boolean',
        'allow_multiple' => 'boolean',
        'max_choices' => 'integer',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'show_voters' => 'boolean',
        'show_results' => 'boolean',
    ];

    public function lesson()
    {
        return $this->morphOne(Lesson::class, 'lessonable');
    }

    public function options()
    {
        return $this->hasMany(LessonPollingOption::class)->orderBy('order');
    }

    public function responses()
    {
        return $this->hasMany(LessonPollingResponse::class);
    }
}
