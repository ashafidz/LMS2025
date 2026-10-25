<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use App\Services\BadgeService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class StudentBadgeController extends Controller
{
    /**
     * Menampilkan semua badge aktif beserta progres siswa menuju tiap syarat.
     */
    public function index()
    {
        $user = Auth::user();

        $badges = Badge::where('is_active', true)->with('rules')->orderBy('id')->get();

        // Tanggal diperoleh (disimpan UTC) per badge milik siswa
        $earnedAt = $user->badges()->pluck('badge_user.created_at', 'badges.id');
        $timezone = $user->timezone ?: config('app.timezone');

        foreach ($badges as $badge) {
            $badge->has_badge = $earnedAt->has($badge->id);
            $badge->earned_at = $badge->has_badge && $earnedAt[$badge->id]
                ? Carbon::parse($earnedAt[$badge->id], 'UTC')->setTimezone($timezone)
                : null;

            // Progres hanya dihitung untuk badge yang belum dimiliki dan punya syarat
            $badge->progress = (! $badge->has_badge && $badge->rules->isNotEmpty())
                ? BadgeService::progressFor($user, $badge, true)
                : null;
        }

        return view('student.my-badges.index', ['allBadges' => $badges]);
    }
}
