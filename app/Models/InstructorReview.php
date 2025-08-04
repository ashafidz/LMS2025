<?php

namespace App\Models;

use App\Traits\HasLocalDates;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InstructorReview extends Model
{
    use HasFactory;
    use HasLocalDates;

    protected $fillable = [
        'user_id',
        'instructor_id',
        'course_id',
        'rating',
        'comment',
    ];

    /**
     * Mendapatkan pengguna (siswa) yang memberikan ulasan.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mendapatkan instruktur yang diulas.
     */
    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    /**
     * Mendapatkan kursus yang menjadi konteks ulasan.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
