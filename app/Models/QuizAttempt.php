<?php

namespace App\Models;

use App\Services\QuizShuffleService;
use App\Traits\HasHashedRouteKey;
use App\Traits\HasLocalDates;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizAttempt extends Model
{
    use HasFactory;
    use HasHashedRouteKey;
    use HasLocalDates;

    // Toleransi detik setelah batas waktu (lag jaringan, server lambat, auto-submit dari browser)
    public const SUBMIT_GRACE_SECONDS = 120;

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
        'failed_by_deadline',
        'late_override_by',
        'late_override_at',
        'late_override_note',
    ];

    protected $casts = [
        'student_id' => 'integer',
        'quiz_id' => 'integer',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'revised_at' => 'datetime',
        'expelled_by_violation' => 'boolean',
        'late_override_at' => 'datetime',
        'failed_by_deadline' => 'boolean',
    ];

    /**
     * Accessor: Skor efektif dalam skala 0-100 (sama dengan finalScaledScore()).
     */
    public function getEffectiveScoreAttribute()
    {
        return $this->finalScaledScore();
    }

    /**
     * Total skor maksimum kuis (jumlah skor soal).
     */
    public function maxScore(): float
    {
        return (float) $this->quiz?->questions()->sum('score');
    }

    /**
     * Nilai akhir dalam skala 0-100. revised_score adalah SKOR MENTAH dari instruktur (formnya dibatasi skor maksimum
     * kuis), jadi diskalakan terhadap skor maksimum. Tanpa revisi: scaled_score; data lama tanpa scaled_score dihitung
     * dari skor mentah.
     */
    public function finalScaledScore(): ?float
    {
        if ($this->revised_score !== null) {
            $max = $this->maxScore();

            return $max > 0 ? min(100.0, round((float) $this->revised_score / $max * 100, 2)) : 0.0;
        }

        if ($this->scaled_score !== null) {
            return (float) $this->scaled_score;
        }

        if ($this->score === null) {
            return null;
        }

        $max = $this->maxScore();

        return $max > 0 ? min(100.0, round((float) $this->score / $max * 100, 2)) : 0.0;
    }

    /**
     * True jika instruktur boleh meluluskan percobaan ini: gagal hanya karena terlambat dan nilai akhirnya mencapai batas lulus.
     */
    public function canBeApprovedDespiteLate(): bool
    {
        return $this->failedBecauseLate() && (float) $this->finalScaledScore() >= (float) $this->quiz->pass_mark;
    }

    /**
     * Relasi: Instruktur yang meluluskan percobaan yang terlambat
     */
    public function lateOverrideBy()
    {
        return $this->belongsTo(User::class, 'late_override_by');
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
        $shuffleService = new QuizShuffleService;

        return $shuffleService->getShuffledQuestions($this);
    }

    /**
     * Check if this attempt has shuffled order saved
     */
    public function hasShuffledOrder(): bool
    {
        return $this->questionOrders()->exists();
    }

    /**
     * True jika attempt dikirim setelah batas waktu kuis ditambah toleransi.
     * Dipakai saat menilai (StudentQuizController::submit) dan saat menampilkan alasan di halaman hasil.
     */
    public function submittedAfterTimeLimit(): bool
    {
        $limit = (int) $this->quiz?->time_limit;
        if ($limit <= 0 || ! $this->start_time || ! $this->end_time) {
            return false;
        }

        return $this->end_time->greaterThan(
            $this->start_time->copy()->addMinutes($limit)->addSeconds(self::SUBMIT_GRACE_SECONDS)
        );
    }

    /**
     * True jika attempt tidak lulus karena terlambat (kuis tidak mengizinkan jawaban terlambat).
     */
    public function failedBecauseLate(): bool
    {
        if ($this->status !== 'failed') {
            return false;
        }

        // Dicatat saat pengiriman; pengaturan kuis yang diubah sesudahnya tidak berpengaruh
        if ($this->failed_by_deadline !== null) {
            return $this->failed_by_deadline;
        }

        // Data lama (sebelum kolom ada): hitung dari pengaturan kuis saat ini
        return ! $this->quiz?->allow_exceed_time_limit && $this->submittedAfterTimeLimit();
    }
}
