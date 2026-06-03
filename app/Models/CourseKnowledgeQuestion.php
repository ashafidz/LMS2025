<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseKnowledgeQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'question_text',
        'order',
        'is_active',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function options()
    {
        return $this->hasMany(CourseKnowledgeOption::class, 'question_id')->orderBy('order');
    }
}
