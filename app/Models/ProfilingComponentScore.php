<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfilingComponentScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'attempt_id',
        'component_id',
        'dimension_id',
        'average_score',
        'contribution_pct',
        'category',
    ];

    public function attempt()
    {
        return $this->belongsTo(ProfilingAttempt::class, 'attempt_id');
    }

    public function component()
    {
        return $this->belongsTo(ProfilingComponent::class, 'component_id');
    }

    public function dimension()
    {
        return $this->belongsTo(ProfilingDimension::class, 'dimension_id');
    }
}
