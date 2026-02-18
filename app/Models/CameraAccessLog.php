<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CameraAccessLog extends Model
{
    use HasFactory;

    // Hanya ada created_at
    public const UPDATED_AT = null;

    protected $fillable = [
        'attempt_id',
        'permission_requested_at',
        'permission_granted',
        'permission_granted_at',
        'browser_info',
        'error_message',
    ];

    protected $casts = [
        'permission_requested_at' => 'datetime',
        'permission_granted' => 'boolean',
        'permission_granted_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    /**
     * Relationship: belongs to QuizAttempt
     */
    public function attempt()
    {
        return $this->belongsTo(QuizAttempt::class, 'attempt_id');
    }

    /**
     * Scope: Permission granted
     */
    public function scopeGranted($query)
    {
        return $query->where('permission_granted', true);
    }

    /**
     * Scope: Permission denied
     */
    public function scopeDenied($query)
    {
        return $query->where('permission_granted', false);
    }
}
