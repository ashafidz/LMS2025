<?php

namespace App\Models;

use App\Traits\HasLocalDates;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PointHistory extends Model
{
    use HasFactory;
    use HasLocalDates;

    protected $fillable = [
        'user_id',
        'course_id',
        'lesson_id',
        'points',
        'description',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Tambahkan relasi baru ini
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Mendapatkan pelajaran yang terkait dengan riwayat poin ini (jika ada).
     */
    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }
}
