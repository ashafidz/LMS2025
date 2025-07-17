<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonVideo extends Model
{
    use HasFactory;

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var array
     */
    protected $fillable = [
        'source_type', // 'upload' atau 'youtube'
        'video_path',  // Path file lokal atau URL YouTube
        'duration',
    ];

    /**
     * Dapatkan pelajaran (lesson) yang memiliki konten video ini.
     */
    public function lesson()
    {
        return $this->morphOne(Lesson::class, 'lessonable');
    }
}
