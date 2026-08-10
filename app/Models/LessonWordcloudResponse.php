<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonWordcloudResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'lesson_wordcloud_id',
        'user_id',
        'word',
    ];

    public function wordcloud()
    {
        return $this->belongsTo(LessonWordcloud::class, 'lesson_wordcloud_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
