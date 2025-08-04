<?php

namespace App\Models;

use App\Traits\HasLocalDates;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LikertAnswer extends Model
{
    use HasFactory;
    use HasLocalDates;

    protected $fillable = [
        'user_id',
        'course_id',
        'likert_question_id',
        'answer',
    ];

    /**
     * Mendapatkan pengguna yang memberikan jawaban.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mendapatkan pertanyaan yang dijawab.
     */
    public function likertQuestion()
    {
        return $this->belongsTo(LikertQuestion::class);
    }
}
