<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonDocument extends Model
{
    use HasFactory;

    protected $fillable = ['file_path'];

    /**
     * Dapatkan pelajaran (lesson) yang memiliki konten dokumen ini.
     */
    public function lesson()
    {
        return $this->morphOne(Lesson::class, 'lessonable');
    }
}
