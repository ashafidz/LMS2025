<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseUser extends Model
{
    use HasFactory;

    protected $table = 'course_user';

    protected $fillable = [
        'user_id',
        'course_id',
        'points_earned',
        'is_converted_to_diamond',
    ];

    protected $casts = [
        'is_converted_to_diamond' => 'boolean',
    ];



    /**
     * [SOLUSI] Tambahkan relasi ini.
     * Mendapatkan data pengguna (user) yang memiliki catatan poin ini.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Mendapatkan data kursus yang terkait dengan catatan poin ini.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
    }
}
