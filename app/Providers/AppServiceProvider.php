<?php

namespace App\Providers;

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
        //
        // Gate::policy(QuestionTopic::class, QuestionTopicPolicy::class);
        // Gate::policy(Question::class, QuestionPolicy::class);
    }
}
