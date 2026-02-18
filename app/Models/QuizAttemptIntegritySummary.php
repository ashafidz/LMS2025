<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizAttemptIntegritySummary extends Model
{
    use HasFactory;

    protected $table = 'quiz_attempt_integrity_summary';

    protected $fillable = [
        'attempt_id',
        'total_tab_switches',
        'total_face_violations',
        'face_not_detected_count',
        'look_left_count',
        'look_right_count',
        'look_down_count',
        'look_up_count',
        'integrity_score',
        'risk_level',
        'flagged_for_review',
    ];

    protected $casts = [
        'total_tab_switches' => 'integer',
        'total_face_violations' => 'integer',
        'face_not_detected_count' => 'integer',
        'look_left_count' => 'integer',
        'look_right_count' => 'integer',
        'look_down_count' => 'integer',
        'look_up_count' => 'integer',
        'integrity_score' => 'decimal:2',
        'flagged_for_review' => 'boolean',
    ];

    /**
     * Risk level constants
     */
    public const RISK_LOW = 'low';
    public const RISK_MEDIUM = 'medium';
    public const RISK_HIGH = 'high';

    /**
     * Relationship: belongs to QuizAttempt
     */
    public function attempt()
    {
        return $this->belongsTo(QuizAttempt::class, 'attempt_id');
    }

    /**
     * Scope: High risk attempts
     */
    public function scopeHighRisk($query)
    {
        return $query->where('risk_level', self::RISK_HIGH);
    }

    /**
     * Scope: Flagged for review
     */
    public function scopeFlagged($query)
    {
        return $query->where('flagged_for_review', true);
    }

    /**
     * Accessor: Get risk level badge class
     */
    public function getRiskBadgeClassAttribute()
    {
        return match ($this->risk_level) {
            self::RISK_LOW => 'success',
            self::RISK_MEDIUM => 'warning',
            self::RISK_HIGH => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Accessor: Get risk level text
     */
    public function getRiskLevelTextAttribute()
    {
        return match ($this->risk_level) {
            self::RISK_LOW => 'Risiko Rendah',
            self::RISK_MEDIUM => 'Risiko Sedang',
            self::RISK_HIGH => 'Risiko Tinggi',
            default => 'Tidak Diketahui',
        };
    }
}
