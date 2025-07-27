<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LessonAssignment extends Model
{
    use HasFactory;

    protected $fillable = ['instructions', 'due_date', 'pass_mark',];

    protected $casts = [
        'due_date' => 'datetime',
    ];

    public function lesson()
    {
        return $this->morphOne(Lesson::class, 'lessonable');
    }

    public function submissions()
    {
        return $this->hasMany(AssignmentSubmission::class, 'assignment_id');
    }
}
