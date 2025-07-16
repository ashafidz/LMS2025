<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonLinkCollection extends Model
{
    use HasFactory;

    protected $fillable = ['links'];

    /**
     * Casts atribut Eloquent.
     *
     * @var array
     */
    protected $casts = [
        'links' => 'array', // Secara otomatis mengubah JSON menjadi array dan sebaliknya
    ];

    /**
     * Dapatkan pelajaran (lesson) yang memiliki kumpulan link ini.
     */
    public function lesson()
    {
        return $this->morphOne(Lesson::class, 'lessonable');
    }
}
