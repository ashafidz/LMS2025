<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KmeansClusterAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'run_id',
        'attempt_id',
        'cluster_number',
        'distance_to_centroid',
    ];

    public function run()
    {
        return $this->belongsTo(KmeansRun::class, 'run_id');
    }

    public function attempt()
    {
        return $this->belongsTo(ProfilingAttempt::class, 'attempt_id');
    }
}
