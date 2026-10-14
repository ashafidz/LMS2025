<?php

namespace App\Providers;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        try {
            // Gunakan caching agar tidak query ke database setiap kali halaman dimuat
            $settings = Cache::rememberForever('site_settings', function () {
                return SiteSetting::firstOrNew();
            });

            // Kirim variabel $siteSettings ke semua view
            View::share('siteSettings', $settings);
        } catch (\Exception $e) {
            // Ignore exception if tables don't exist yet during initial setup or migration
        }

        // Register Blade directives untuk Hashids
        Blade::directive('hashid', function ($expression) {
            return "<?php echo \App\Services\HashIdService::encode($expression); ?>";
        });

        Blade::directive('unhashid', function ($expression) {
            return "<?php echo \App\Services\HashIdService::decode($expression); ?>";
        });
    }
}
