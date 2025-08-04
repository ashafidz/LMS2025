<?php

namespace App\Models;

use App\Traits\HasLocalDates;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LikertQuestion extends Model
{
    use HasFactory;
    use HasLocalDates;

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
