<?php

namespace App\Models;

use App\Traits\HasLocalDates;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonPoint extends Model
{
    use HasFactory;
    use HasLocalDates;

    protected $fillable = [
        'title',
        'description',
    ];

    public function lesson()
    {
        return $this->morphOne(Lesson::class, 'lessonable');
    }
}
