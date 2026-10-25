<?php

namespace App\Models;

use App\Traits\HasHashedRouteKey;
use App\Traits\HasLocalDates;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    use HasFactory;
    use HasHashedRouteKey;
    use HasLocalDates;

    protected $fillable = [
        'title',
        'description',
        'icon_path',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Mendapatkan semua pengguna yang memiliki badge ini.
     */
    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot('seen_at', 'created_at');
    }

    /**
     * Syarat untuk mendapatkan badge ini (semua harus terpenuhi).
     */
    public function rules()
    {
        return $this->hasMany(BadgeRule::class)->orderBy('position')->orderBy('id');
    }
}
