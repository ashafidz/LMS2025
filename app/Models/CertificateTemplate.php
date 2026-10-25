<?php

namespace App\Models;

use App\Traits\HasLocalDates;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CertificateTemplate extends Model
{
    use HasFactory;
    use HasLocalDates;

    protected $fillable = [
        'name',
        'version',
        'based_on_id',
        'background_path',
        'orientation',
        'elements',
        'change_note',
        'is_active',
        'activated_at',
        'created_by',
        'activated_by',
    ];

    protected $casts = [
        'version' => 'integer',
        'elements' => 'array',
        'is_active' => 'boolean',
        'activated_at' => 'datetime',
    ];

    /** URL gambar latar mengikuti host yang sedang dipakai (tidak bergantung pada APP_URL). */
    public function backgroundUrl(): string
    {
        return asset('storage/'.ltrim($this->background_path, '/'));
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function basedOn()
    {
        return $this->belongsTo(self::class, 'based_on_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function activator()
    {
        return $this->belongsTo(User::class, 'activated_by');
    }
}
