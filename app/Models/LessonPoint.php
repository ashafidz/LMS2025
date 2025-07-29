<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonPoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
    ];

    public function lesson()
    {
        return $this->morphOne(Lesson::class, 'lessonable');
    }
}
