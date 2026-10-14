<?php

namespace App\Models;

use App\Traits\HasHashedRouteKey;
use App\Traits\HasLocalDates;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;
    use HasHashedRouteKey;
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

    /**
     * Check if the coupon is currently usable by the given user.
     */
    public function isUsableBy(User $user): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }

        if ($this->max_uses !== null && $this->uses_count >= $this->max_uses) {
            return false;
        }

        if ($this->max_uses_per_user !== null) {
            $userUsage = $user->coupons()->where('coupon_id', $this->id)->first();
            if ($userUsage && $userUsage->pivot->uses_count >= $this->max_uses_per_user) {
                return false;
            }
        }

        if ($this->course_id !== null) {
            if (! $user->carts()->where('course_id', $this->course_id)->exists()) {
                return false;
            }
        }

        return true;
    }
}
