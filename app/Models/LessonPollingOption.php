<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LessonPollingOption extends Model
{
    protected $fillable = ['lesson_polling_id', 'text', 'order'];

    public function polling()
    {
        return $this->belongsTo(LessonPolling::class, 'lesson_polling_id');
    }

    public function responses()
    {
        return $this->hasMany(LessonPollingResponse::class, 'lesson_polling_option_id');
    }
}
