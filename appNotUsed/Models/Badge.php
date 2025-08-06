<?php

namespace App\Models;

use App\Traits\HasLocalDates;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Badge extends Model
{
    use HasFactory;
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
        return $this->belongsToMany(User::class);
    }
}
