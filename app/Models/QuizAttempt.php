<?php

namespace App\Models;

use App\Traits\HasLocalDates;
use App\Traits\HasHashedRouteKey;
use App\Services\QuizShuffleService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QuizAttempt extends Model
{
    use HasFactory;
    use HasLocalDates;
    use HasHashedRouteKey;

    protected $fillable = [
        'quiz_id',
        'student_id',
        'score',
        'scaled_score',
        'status',
        'start_time',
        'end_time',
        'revised_score',
        'revised_by',
        'revised_at',
        'revision_note',
        'expelled_by_violation',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'revised_at' => 'datetime',
        'expelled_by_violation' => 'boolean',
    ];

    /**
     * Accessor: Skor efektif dalam skala 0-100.
     * Prioritas: revised_score → scaled_score → kalkulasi manual (fallback data lama)
     */
    public function getEffectiveScoreAttribute()
    {
        if ($this->revised_score !== null) {
            return $this->revised_score;
        }
        if ($this->scaled_score !== null) {
            return $this->scaled_score;
        }
        // Fallback untuk data lama yang belum punya scaled_score:
        // Hitung dari skor mentah / skor maksimal quiz × 100
        if ($this->score !== null && $this->relationLoaded('quiz')) {
            $maxScore = $this->quiz->questions->sum('score');
            return ($maxScore > 0) ? min(100, round(($this->score / $maxScore) * 100, 2)) : 0;
        }
        return $this->score;
    }

    /**
     * Relasi: Instruktur yang melakukan revisi skor
     */
    public function revisedBy()
    {
        return $this->belongsTo(User::class, 'revised_by');
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function answers()
    {
        return $this->hasMany(StudentAnswer::class, 'attempt_id');
    }



    /**
     * Relationship: QuizAttempt has one integrity summary
     */
    public function integritySummary()
    {
        return $this->hasOne(QuizAttemptIntegritySummary::class, 'attempt_id');
    }

    /**
     * Relationship: QuizAttempt has many question orders
     */
    public function questionOrders()
    {
        return $this->hasMany(QuizAttemptQuestionOrder::class, 'attempt_id');
    }

    /**
     * Relationship: QuizAttempt has many monitoring logs
     */
    public function monitoringLogs()
    {
        return $this->hasMany(MonitoringLog::class, 'attempt_id');
    }

    /**
     * Relationship: QuizAttempt has many camera access logs
     */
    public function cameraAccessLogs()
    {
        return $this->hasMany(CameraAccessLog::class, 'attempt_id');
    }

    // /**
    //  * Get shuffled questions for this attempt
    //  */
    // public function getShuffledQuestions()
    // {
    //     return $this->questionOrders()
    //         ->with('question')
    //         ->orderBy('shuffled_order')
    //         ->get()
    //         ->pluck('question');
    // }


    /**
     * Get shuffled questions for this attempt
     */
    public function getShuffledQuestions()
    {
        $shuffleService = new QuizShuffleService();
        return $shuffleService->getShuffledQuestions($this);
    }

    /**
     * Check if this attempt has shuffled order saved
     */
    public function hasShuffledOrder(): bool
    {
        return $this->questionOrders()->exists();
    }
}
