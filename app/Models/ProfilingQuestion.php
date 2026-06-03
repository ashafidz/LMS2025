<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfilingQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'component_id',
        'dimension_id',
        'question_text',
        'order',
        'is_active',
    ];

    public function component()
    {
        return $this->belongsTo(ProfilingComponent::class, 'component_id');
    }

    public function dimension()
    {
        return $this->belongsTo(ProfilingDimension::class, 'dimension_id');
    }
}
