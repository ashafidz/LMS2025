<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
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
        // $siteSettings untuk semua view disediakan oleh ViewComposerServiceProvider (sekali per request)

        // Register Blade directives untuk Hashids
        Blade::directive('hashid', function ($expression) {
            return "<?php echo \App\Services\HashIdService::encode($expression); ?>";
        });

        Blade::directive('unhashid', function ($expression) {
            return "<?php echo \App\Services\HashIdService::decode($expression); ?>";
        });
    }
}
