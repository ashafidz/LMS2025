<?php

namespace App\Models;

use App\Traits\HasLocalDates;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Coupon extends Model
{
    use HasFactory;
    use HasLocalDates;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'description',
        'type',
        'value',
        'is_public',
        'max_uses_per_user',
        'course_id',
        'max_uses',
        'starts_at',
        'expires_at',
        'is_active',

    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'value' => 'decimal:2',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'is_public' => 'boolean', // Tambahkan ini
    ];

    /**
     * Mendapatkan kursus yang terkait dengan kupon ini (jika ada).
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Mendapatkan semua pengguna yang telah menggunakan kupon ini.
     */
    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot('uses_count')->withTimestamps();
    }
}
