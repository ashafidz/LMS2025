<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KmeansRun extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'triggered_by',
        'k_value',
        'status',
        'algorithm_config',
        'result_summary',
        'notes',
        'ai_labels',
        'ai_labeling_status',
        'ai_labeling_requested_at',
        'ai_labeling_completed_at',
    ];

    protected $casts = [
        'algorithm_config' => 'array',
        'result_summary' => 'array',
        'ai_labels' => 'array',
        'ai_labeling_requested_at' => 'datetime',
        'ai_labeling_completed_at' => 'datetime',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function triggeredBy()
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }

    public function clusterAssignments()
    {
        return $this->hasMany(KmeansClusterAssignment::class, 'run_id');
    }

    /**
     * Check if AI labels exist and the process is completed.
     */
    public function hasAiLabels(): bool
    {
        return $this->ai_labeling_status === 'completed' && !empty($this->ai_labels);
    }

    /**
     * Get a specific cluster label by its number
     */
    public function getClusterLabel(int $clusterNum): ?array
    {
        if (!$this->hasAiLabels() || empty($this->ai_labels['clusters'])) {
            return null;
        }

        foreach ($this->ai_labels['clusters'] as $cluster) {
            if (isset($cluster['cluster_number']) && $cluster['cluster_number'] == $clusterNum) {
                return $cluster;
            }
        }

        return null;
    }
}
