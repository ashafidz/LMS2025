<?php

namespace App\Models;

use App\Traits\HasHashedRouteKey;
use App\Traits\HasLocalDates;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonDocument extends Model
{
    use HasFactory;
    use HasHashedRouteKey;
    use HasLocalDates;

    protected $fillable = ['file_path'];

    /**
     * Dapatkan pelajaran (lesson) yang memiliki konten dokumen ini.
     */
    public function lesson()
    {
        return $this->morphOne(Lesson::class, 'lessonable');
    }
}
