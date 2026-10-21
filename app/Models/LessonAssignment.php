<?php

namespace App\Models;

use App\Traits\HasHashedRouteKey;
use App\Traits\HasLocalDates;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonAssignment extends Model
{
    use HasFactory;
    use HasHashedRouteKey;
    use HasLocalDates;

    protected $fillable = ['instructions', 'due_date', 'pass_mark'];

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
