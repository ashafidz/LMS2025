<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonPointAward extends Model
{
    use HasFactory;

    protected $fillable = [
        'lesson_id',
        'student_id',
        'instructor_id',
        'points',
    ];

    // Relasi ke model lain bisa ditambahkan di sini jika perlu
}
