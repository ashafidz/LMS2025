<?php

namespace App\Providers;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use App\Models\Question;
use App\Models\QuestionTopic;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Policies\Instructor\QuestionPolicy;
use App\Policies\Instructor\QuestionTopicPolicy;

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
        // Gunakan caching agar tidak query ke database setiap kali halaman dimuat
        $settings = Cache::rememberForever('site_settings', function () {
            return SiteSetting::first();
        });

        // Kirim variabel $siteSettings ke semua view
        View::share('siteSettings', $settings);
    }
}
