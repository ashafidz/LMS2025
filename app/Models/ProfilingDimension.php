<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfilingDimension extends Model
{
    use HasFactory;

    protected $fillable = [
        'component_id',
        'name',
        'description',
        'order',
    ];

    public function component()
    {
        return $this->belongsTo(ProfilingComponent::class, 'component_id');
    }

    public function questions()
    {
        return $this->hasMany(ProfilingQuestion::class, 'dimension_id')->orderBy('order');
    }
}
