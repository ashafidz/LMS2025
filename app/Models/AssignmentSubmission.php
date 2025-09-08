<?php

namespace App\Models;

use App\Traits\HasLocalDates;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AssignmentSubmission extends Model
{
    use HasFactory;
    use HasLocalDates;

    protected $fillable = ['assignment_id', 'user_id', 'file_path', 'submitted_at', 'grade', 'feedback', 'status',];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    public function assignment()
    {
        return $this->belongsTo(LessonAssignment::class, 'assignment_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Accessor untuk mengecek apakah pengumpulan tugas terlambat.
     *
     * @return bool
     */
    public function getIsLateAttribute()
    {
        // Pastikan assignment dan due_date-nya ada untuk menghindari error
        if ($this->assignment && $this->assignment->due_date) {
            // Kembalikan true jika waktu submit lebih besar dari due date
            return $this->submitted_at->isAfter($this->assignment->due_date);
        }

        // Defaultnya tidak terlambat jika tidak ada due date
        return false;
    }
}
