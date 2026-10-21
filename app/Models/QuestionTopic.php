<?php

namespace App\Models;

use App\Traits\HasHashedRouteKey;
use App\Traits\HasLocalDates;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuestionTopic extends Model
{
    use HasFactory, SoftDeletes;
    use HasHashedRouteKey;
    use HasLocalDates;

    protected $fillable = [
        'instructor_id',
        'name',
        'description',
        'available_for_all_courses', // Tambahkan ini
    ];

    protected $casts = [
        'instructor_id' => 'integer',
        'available_for_all_courses' => 'boolean', // Tambahkan ini
    ];

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function questions()
    {
        return $this->hasMany(Question::class, 'topic_id');
    }

    /**
     * Mendapatkan semua kursus yang terhubung dengan topik ini.
     */
    public function courses()
    {
        return $this->belongsToMany(Course::class, 'course_question_topic');
    }
}
