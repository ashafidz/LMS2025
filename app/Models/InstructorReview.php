<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstructorReview extends Model
{
    use HasFactory;

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
