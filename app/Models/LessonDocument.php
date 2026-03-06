<?php

namespace App\Models;

use App\Traits\HasLocalDates;
use App\Traits\HasHashedRouteKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LessonDocument extends Model
{
    use HasFactory;
    use HasLocalDates;
    use HasHashedRouteKey;

    protected $fillable = ['file_path'];

    /**
     * Dapatkan pelajaran (lesson) yang memiliki konten dokumen ini.
     */
    public function lesson()
    {
        return $this->morphOne(Lesson::class, 'lessonable');
    }
}
