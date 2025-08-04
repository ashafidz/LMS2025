<?php

namespace App\Models;

use App\Traits\HasLocalDates;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lesson extends Model
{
    use HasFactory;
    use HasLocalDates;

    protected $fillable = ['module_id', 'title', 'order', 'lessonable_id', 'lessonable_type'];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    /**
     * Get the parent lessonable model (article, video, quiz, etc.).
     */
    public function lessonable()
    {
        return $this->morphTo();
    }

    /**
     * The users who have completed this lesson.
     */
    public function completedBy()
    {
        return $this->belongsToMany(User::class, 'lesson_user')->withPivot('completed_at');
    }

    /**
     * The discussion threads for this lesson.
     */
    public function discussions()
    {
        return $this->hasMany(LessonDiscussion::class);
    }
}
