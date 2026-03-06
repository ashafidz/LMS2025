<?php

namespace App\Models;

use App\Traits\HasLocalDates;
use App\Traits\HasHashedRouteKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LessonLinkCollection extends Model
{
    use HasFactory;
    use HasLocalDates;
    use HasHashedRouteKey;

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
