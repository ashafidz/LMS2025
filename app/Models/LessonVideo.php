<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LessonVideo extends Model
{
    use HasFactory;

    protected $fillable = ['video_s3_key', 'duration'];

    public function lesson()
    {
        return $this->morphOne(Lesson::class, 'lessonable');
    }
}
