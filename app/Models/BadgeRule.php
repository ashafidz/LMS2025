<?php

namespace App\Models;

use App\Support\Badges\BadgeMetrics;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BadgeRule extends Model
{
    use HasFactory;

    protected $fillable = ['badge_id', 'metric', 'target', 'scope', 'scope_id', 'position'];

    protected $casts = [
        'target' => 'integer',
        'scope_id' => 'integer',
        'position' => 'integer',
    ];

    public function badge(): BelongsTo
    {
        return $this->belongsTo(Badge::class);
    }

    /**
     * Kalimat awam untuk syarat ini, contoh: "Lulus kuis minimal 10 kuis di semua kursus".
     */
    public function sentence(): string
    {
        return BadgeMetrics::sentence($this->metric, (int) $this->target, $this->scope, $this->scope_id);
    }
}
