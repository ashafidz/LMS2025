<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonWordcloud extends Model
{
    use HasFactory;

    protected $fillable = [
        'question',
        'description',
        'max_words',
        'is_active',
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function lesson()
    {
        return $this->morphOne(Lesson::class, 'lessonable');
    }

    public function responses()
    {
        return $this->hasMany(LessonWordcloudResponse::class);
    }
}
