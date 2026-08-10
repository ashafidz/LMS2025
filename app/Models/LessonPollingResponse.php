<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LessonPollingResponse extends Model
{
    protected $fillable = ['lesson_polling_id', 'user_id', 'lesson_polling_option_id'];

    public function polling()
    {
        return $this->belongsTo(LessonPolling::class, 'lesson_polling_id');
    }

    public function option()
    {
        return $this->belongsTo(LessonPollingOption::class, 'lesson_polling_option_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
