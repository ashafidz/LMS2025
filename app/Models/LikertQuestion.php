<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LikertQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_text',
        'category',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Mendapatkan semua jawaban untuk pertanyaan ini.
     */
    public function answers()
    {
        return $this->hasMany(LikertAnswer::class);
    }
}
