<?php

namespace App\Providers;

use App\Models\CourseCategory;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

// Jika Anda butuh ShopStatus juga, tambahkan use-nya
// use App\Models\ShopStatus;

class ViewComposerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    /**
     * Site settings resolved once per request and reused for every view.
     */
    private ?SiteSetting $siteSettings = null;

    public function boot(): void
    {
        // Kirim $siteSettings ke semua view. Nilai diambil sekali per request (bukan per view)
        // dan tanpa Schema::hasTable, karena composer ini berjalan untuk setiap partial.
        View::composer('*', function ($view) {
            try {
                $this->siteSettings ??= Cache::rememberForever('site_settings', fn () => SiteSetting::firstOrNew());
                $view->with('siteSettings', $this->siteSettings);
            } catch (\Exception $e) {
                // Abaikan error (misalnya tabel belum ada saat instalasi awal)
            }
        });

        // Menggunakan View Composer untuk mengirim data kategori ke layout home
        // Kode ini akan berjalan setiap kali view 'layouts.home-layout' akan dirender
        View::composer('layouts.home-layout', function ($view) {
            // Ambil 4 kategori dengan jumlah student terbanyak
            $footerCategories = Cache::remember('footer_categories', now()->addMinutes(10), function () {
                return CourseCategory::withCount([
                    'courses as total_students' => function ($query) {
                        $query->join('course_enrollments', 'courses.id', '=', 'course_enrollments.course_id');
                    },
                ])
                    ->orderByDesc('total_students')
                    ->limit(4)
                    ->get();
            });

            // 2. Ambil semua data pengaturan situs (nilai yang sama dengan composer '*')
            $siteSettings = $this->siteSettings ??= Cache::rememberForever('site_settings', fn () => SiteSetting::firstOrNew());

            // Kirim kedua set data ke view
            $view->with('footerCategories', $footerCategories)
                ->with('siteSettings', $siteSettings);
        });
    }
}
