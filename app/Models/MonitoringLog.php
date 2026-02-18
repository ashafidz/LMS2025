<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonitoringLog extends Model
{
    use HasFactory;

    // Hanya ada created_at
    public const UPDATED_AT = null;

    protected $fillable = [
        'attempt_id',
        'violation_type',
        'violation_timestamp',
        'duration_seconds',
        'screenshot_path',
        'additional_data',
    ];

    protected $casts = [
        'violation_timestamp' => 'datetime',
        'duration_seconds' => 'integer',
        'additional_data' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Violation types constants
     */
    public const TYPE_TAB_SWITCH = 'tab_switch';
    public const TYPE_FACE_NOT_DETECTED = 'face_not_detected';
    public const TYPE_LOOK_LEFT = 'look_left';
    public const TYPE_LOOK_RIGHT = 'look_right';
    public const TYPE_LOOK_DOWN = 'look_down';
    public const TYPE_LOOK_UP = 'look_up';

    /**
     * Relationship: belongs to QuizAttempt
     */
    public function attempt()
    {
        return $this->belongsTo(QuizAttempt::class, 'attempt_id');
    }

    /**
     * Scope: Filter by violation type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('violation_type', $type);
    }

    /**
     * Scope: Tab switch violations only
     */
    public function scopeTabSwitches($query)
    {
        return $query->where('violation_type', self::TYPE_TAB_SWITCH);
    }

    /**
     * Scope: Face violations only
     */
    public function scopeFaceViolations($query)
    {
        return $query->whereIn('violation_type', [
            self::TYPE_FACE_NOT_DETECTED,
            self::TYPE_LOOK_LEFT,
            self::TYPE_LOOK_RIGHT,
            self::TYPE_LOOK_DOWN,
            self::TYPE_LOOK_UP,
        ]);
    }
}
