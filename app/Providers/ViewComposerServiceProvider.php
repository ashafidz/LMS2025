<?php

namespace App\Providers;

use App\Models\SiteSetting;
use App\Models\CourseCategory;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
// Jika Anda butuh ShopStatus juga, tambahkan use-nya
// use App\Models\ShopStatus;

class ViewComposerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Gunakan composer untuk semua view ('*')
        View::composer('*', function ($view) {
            // Bungkus logika dengan try-catch untuk keamanan
            try {
                // Cek dulu apakah tabelnya ada sebelum query
                if (Schema::hasTable('site_settings')) {
                    // Ambil data dari cache, jika tidak ada, query lalu simpan selamanya
                    $siteSettings = Cache::rememberForever('site_settings', function () {
                        return SiteSetting::first() ?? new SiteSetting();
                    });

                    // Kirim data ke view yang sedang dirender
                    $view->with('siteSettings', $siteSettings);
                }
            } catch (\Exception $e) {
                // Abaikan error agar tidak mengganggu proses `artisan`
            }
        });

        // Menggunakan View Composer untuk mengirim data kategori ke layout home
        // Kode ini akan berjalan setiap kali view 'layouts.home-layout' akan dirender
        View::composer('layouts.home-layout', function ($view) {
            // Ambil 4 atau 5 kategori untuk ditampilkan di footer
            $footerCategories = CourseCategory::latest()->get();

            // Kirim data ke view dengan nama variabel 'footerCategories'
            $view->with('footerCategories', $footerCategories);
        });
    }
}
