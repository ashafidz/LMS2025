<?php

namespace App\Models;

use App\Traits\HasLocalDates;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;
    use HasLocalDates;

    protected $fillable = [
        'user_id',
        'course_id',
    ];

    /**
     * Hapus item keranjang milik user yang kursusnya tidak lagi bisa dibeli dengan uang
     * (belum terbit, bukan kursus uang, sudah dihapus). Dengan $dropEnrolled, kursus yang
     * sudah diikuti user juga dikeluarkan. Mengembalikan jumlah item yang dikeluarkan.
     */
    public static function pruneUnbuyableFor(User $user, bool $dropEnrolled = false): int
    {
        $enrolledIds = $dropEnrolled ? $user->enrollments()->pluck('courses.id') : collect();
        $removed = 0;

        foreach ($user->carts()->with('course')->get() as $item) {
            if (! $item->course || ! $item->course->isBuyableWithMoney() || $enrolledIds->contains($item->course_id)) {
                $item->delete();
                $removed++;
            }
        }

        if ($removed > 0 && $user->carts()->count() === 0) {
            session()->forget('coupon');
        }

        return $removed;
    }

    /**
     * Mendapatkan pengguna yang memiliki item keranjang ini.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mendapatkan kursus yang ada di keranjang ini.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
