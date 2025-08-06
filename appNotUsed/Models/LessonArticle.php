<?php

namespace App\Models;

use App\Traits\HasLocalDates;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class LessonArticle extends Model
{
    use HasFactory;
    use HasLocalDates;

    protected $fillable = ['content'];

    /**
     * Get the lesson that this article belongs to.
     */
    public function lesson()
    {
        return $this->morphOne(Lesson::class, 'lessonable');
    }
}
