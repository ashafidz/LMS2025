<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LikertAnswer extends Model
{
    use HasFactory;

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
