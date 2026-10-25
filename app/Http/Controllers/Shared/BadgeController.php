<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Http\Requests\BadgeRequest;
use App\Http\Requests\BadgeRulesRequest;
use App\Jobs\BackfillBadge;
use App\Models\Badge;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\User;
use App\Services\BadgeService;
use App\Support\Badges\BadgeMetrics;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Kelola badge berbasis aturan. Dipakai bersama oleh admin dan superadmin;
 * awalan route (admin atau superadmin) diambil dari nama route yang sedang berjalan.
 */
class BadgeController extends Controller
{
    public function index(): View
    {
        $badges = Badge::withCount('users')->with('rules')->latest()->simplePaginate(10);

        return view('shared-admin.badges.index', [
            'badges' => $badges,
            'routePrefix' => $this->prefix(),
        ]);
    }

    public function create(): View
    {
        return view('shared-admin.badges.form', $this->formData(new Badge(['is_active' => true]), false));
    }

    public function store(BadgeRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $badge = Badge::create([
                'title' => $request->validated('title'),
                'description' => $request->validated('description'),
                'icon_path' => $request->file('icon')->store('badge-icons', 'public'),
                'is_active' => $request->boolean('is_active'),
            ]);

            $badge->rules()->createMany($request->normalizedRules());
        });

        return redirect()->route($this->prefix().'.badges.index')
            ->with('success', 'Badge berhasil dibuat. Klik "Berikan ke siswa" bila siswa yang sudah memenuhi syarat harus langsung mendapatkannya.');
    }

    public function edit(Badge $badge): View
    {
        $badge->load('rules');

        return view('shared-admin.badges.form', $this->formData($badge, $badge->users()->exists()));
    }

    public function update(BadgeRequest $request, Badge $badge): RedirectResponse
    {
        $locked = $request->isLocked();
        $oldIcon = $badge->icon_path;

        DB::transaction(function () use ($request, $badge, $locked) {
            $data = [
                'title' => $request->validated('title'),
                'description' => $request->validated('description'),
                'is_active' => $request->boolean('is_active'),
            ];

            if ($request->hasFile('icon')) {
                $data['icon_path'] = $request->file('icon')->store('badge-icons', 'public');
            }

            $badge->update($data);

            if (! $locked) {
                $badge->rules()->delete();
                $badge->rules()->createMany($request->normalizedRules());
            }
        });

        if ($request->hasFile('icon') && $oldIcon) {
            Storage::disk('public')->delete($oldIcon);
        }

        $redirect = redirect()->route($this->prefix().'.badges.index');

        // Form dibuka saat badge belum dimiliki siswa tetapi sudah dimiliki ketika disimpan: syarat tidak berubah
        if ($locked && $request->filled('rules')) {
            return $redirect->with('warning', 'Badge diperbarui. Syarat tidak diubah karena badge sudah dimiliki siswa.');
        }

        return $redirect->with('success', 'Badge berhasil diperbarui.');
    }

    public function destroy(Badge $badge): RedirectResponse
    {
        $icon = $badge->icon_path;

        DB::transaction(function () use ($badge) {
            $badge->rules()->delete();
            $badge->delete();
        });

        if ($icon) {
            Storage::disk('public')->delete($icon);
        }

        return back()->with('success', 'Badge berhasil dihapus.');
    }

    /**
     * Buat salinan badge (tanpa pemilik) agar syaratnya bisa diubah.
     */
    public function duplicate(Badge $badge): RedirectResponse
    {
        $badge->load('rules');

        $copy = DB::transaction(function () use ($badge) {
            $copy = Badge::create([
                'title' => Str::limit('Salinan dari '.$badge->title, 255, ''),
                'description' => $badge->description,
                'icon_path' => $this->copyIcon($badge->icon_path),
                'is_active' => false,
            ]);

            $copy->rules()->createMany(
                $badge->rules->map(fn ($rule) => $rule->only(['metric', 'target', 'scope', 'scope_id', 'position']))->all()
            );

            return $copy;
        });

        return redirect()->route($this->prefix().'.badges.edit', $copy)
            ->with('success', 'Salinan dibuat dalam keadaan nonaktif. Ubah syaratnya, lalu aktifkan.');
    }

    /**
     * Beri badge ke siswa yang sudah memenuhi syarat (diproses di antrean).
     */
    public function backfill(Badge $badge): RedirectResponse
    {
        if (! $badge->is_active || ! $badge->rules()->exists()) {
            return back()->with('error', 'Badge harus aktif dan punya syarat sebelum bisa diberikan ke siswa.');
        }

        BackfillBadge::dispatch($badge->id);

        return back()->with('success', 'Sedang diproses. Badge akan muncul di siswa yang memenuhi syarat dalam beberapa menit.');
    }

    /**
     * Pratinjau: berapa siswa yang saat ini memenuhi syarat yang sedang disusun.
     */
    public function previewCount(BadgeRulesRequest $request): JsonResponse
    {
        $result = BadgeService::countQualifying($request->normalizedRules());

        return response()->json($result + ['total_students' => User::role('student')->count()]);
    }

    private function formData(Badge $badge, bool $locked): array
    {
        return [
            'badge' => $badge,
            'locked' => $locked,
            'ownersCount' => $badge->exists ? $badge->users()->count() : 0,
            'catalog' => BadgeMetrics::catalog(),
            'courses' => Course::orderBy('title')->get(['id', 'title'])->map(fn ($c) => ['id' => $c->id, 'name' => $c->title])->all(),
            'categories' => CourseCategory::orderBy('name')->get(['id', 'name'])->map(fn ($c) => ['id' => $c->id, 'name' => $c->name])->all(),
            'initialRules' => old('rules', $badge->exists
                ? $badge->rules->map(fn ($r) => $r->only(['metric', 'target', 'scope', 'scope_id']))->all()
                : [['metric' => 'quiz_passed', 'target' => 1, 'scope' => 'all', 'scope_id' => null]]),
            'routePrefix' => $this->prefix(),
        ];
    }

    private function copyIcon(?string $path): string
    {
        $disk = Storage::disk('public');

        if (! $path || ! $disk->exists($path)) {
            return (string) $path;
        }

        $copy = 'badge-icons/'.Str::uuid().'.'.pathinfo($path, PATHINFO_EXTENSION);
        $disk->copy($path, $copy);

        return $copy;
    }

    private function prefix(): string
    {
        return Str::before((string) Route::currentRouteName(), '.');
    }
}
