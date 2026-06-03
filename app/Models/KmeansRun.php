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
    ];

    protected $casts = [
        'algorithm_config' => 'array',
        'result_summary' => 'array',
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
}
