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
     * Mendapatkan semua pesanan yang menggunakan kupon ini.
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public const RESERVATION_HOURS = 24; // Midtrans Snap default expiry

    public function reservedCount(?User $user = null): int
    {
        $query = $this->orders()
            ->where('status', 'pending')
            ->where('created_at', '>=', now()->subHours(self::RESERVATION_HOURS));

        if ($user !== null) {
            $query->where('user_id', $user->id);
        }

        return $query->count();
    }

    /**
     * Mendapatkan alasan mengapa kupon tidak dapat digunakan, atau null jika dapat digunakan.
     */
    public function unusableReasonFor(User $user): ?string
    {
        if (! $this->is_active || ($this->expires_at && $this->expires_at->isPast()) || ($this->starts_at && $this->starts_at->isFuture())) {
            return 'Kode kupon tidak valid atau sudah tidak berlaku.';
        }

        if ($this->max_uses !== null && ($this->uses_count + $this->reservedCount()) >= $this->max_uses) {
            return 'Kuota kupon ini sudah habis.';
        }

        if ($this->max_uses_per_user !== null) {
            $userUsage = $user->coupons()->where('coupon_id', $this->id)->first();
            $paidCount = $userUsage ? (int) $userUsage->pivot->uses_count : 0;
            if (($paidCount + $this->reservedCount($user)) >= $this->max_uses_per_user) {
                return 'Anda telah mencapai batas maksimal penggunaan untuk kupon ini.';
            }
        }

        if ($this->course_id !== null) {
            if (! $user->carts()->where('course_id', $this->course_id)->exists()) {
                return 'Kupon ini tidak berlaku untuk kursus yang ada di keranjang Anda.';
            }
        }

        return null;
    }

    /**
     * Check if the coupon is currently usable by the given user.
     */
    public function isUsableBy(User $user): bool
    {
        return $this->unusableReasonFor($user) === null;
    }
}
