<?php

namespace App\Models;

use App\Traits\HasLocalDates;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QuestionOption extends Model
{
    use HasFactory, SoftDeletes;
    use HasLocalDates;

    // We'll keep timestamps for potential future auditing
    protected $fillable = [
        'question_id',
        'option_text',
        'is_correct',
        'correct_gap_identifier', // Added this field
    ];

    /**
     * The question this option belongs to.
     */
    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
