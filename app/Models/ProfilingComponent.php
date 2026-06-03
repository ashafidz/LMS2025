<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfilingComponent extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'theory_reference',
        'mechanics_type',
        'scale_min',
        'scale_max',
        'order',
        'is_active',
    ];

    public function dimensions()
    {
        return $this->hasMany(ProfilingDimension::class, 'component_id')->orderBy('order');
    }

    public function questions()
    {
        return $this->hasMany(ProfilingQuestion::class, 'component_id')->orderBy('order');
    }
}
