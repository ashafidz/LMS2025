<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LessonPolling extends Model
{
    protected $fillable = ['question', 'description', 'is_active', 'start_time', 'end_time'];

    protected $casts = [
        'is_active' => 'boolean',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
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
