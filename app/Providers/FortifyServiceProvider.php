<?php

namespace App\Providers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Laravel\Fortify\Fortify;
use Filament\Facades\Filament;
// use App\Http\Responses\LoginResponse;
// use Illuminate\Http\RedirectResponse;
use App\Actions\Fortify\CreateNewUser;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use Illuminate\Support\Facades\RateLimiter;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
// use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Contracts\LogoutResponse;
use Laravel\Fortify\Contracts\RegisterResponse;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
        // Bind your custom LoginResponse
        // $this->app->singleton(
        //     LoginResponseContract::class,
        //     LoginResponse::class
        // );

        // ! for custom redirect after logout
        $this->app->instance(LogoutResponse::class, new class implements LogoutResponse {
            public function toResponse($request)
            {
                return redirect('/');
            }
        });

        // ! for custom redirect after login
        $this->app->instance(LoginResponse::class, new class implements LoginResponse {
            public function toResponse($request)
            {
                // $user = $request->user();

                // if ($user->role === 'admin') {
                //     // return new RedirectResponse(Filament::getPanel('admin')->getUrl());
                //     return redirect(Filament::getPanel('admin')->getUrl());
                // }

                // if ($user->role === 'instructor') {
                //     // return new RedirectResponse(Filament::getPanel('instructor')->getUrl());
                //     return redirect(Filament::getPanel('instructor')->getUrl());
                // }

                // // return new RedirectResponse(route('dashboard'));
                // return redirect()->route('dashboard');
                // ================================================================================================== V2
                // $user = $request->user();

                // if ($user->hasRole('instructor') && $user->instructorProfile?->application_status === 'pending') {
                //     return redirect()->route('instructor.pending');
                // }

                // if ($user->hasRole('admin')) { // <-- Use hasRole()
                //     return redirect(Filament::getPanel('admin')->getUrl());
                // }

                // if ($user->hasRole('instructor')) { // <-- Use hasRole()
                //     return redirect(Filament::getPanel('instructor')->getUrl());
                // }

                // return redirect()->route('dashboard');
                // ================================================================================================== V3
                $user = $request->user();
                $loginPreference = $request->input('login_preference');

                // --- NEW LOGIC WITH LOGIN PREFERENCE ---

                if ($user->hasRole('superadmin')) {
                    return redirect(Filament::getPanel('superadmin')->getUrl());
                }

                if ($user->hasRole('admin')) {
                    return redirect(Filament::getPanel('admin')->getUrl());
                }

                // Check user's choice: If they chose "Instructor" AND have the role
                if ($loginPreference === 'instructor' && $user->hasRole('instructor')) {
                    // Check if their application is pending
                    if ($user->instructorProfile?->application_status === 'pending') {
                        return redirect()->route('instructor.pending');
                    }
                    // If approved, go to the instructor panel
                    return redirect(Filament::getPanel('instructor')->getUrl());
                }

                // Default to student dashboard if they chose "Student", have the role,
                // or as a general fallback.
                if ($user->hasRole('student')) {
                    return redirect()->route('dashboard');
                }

                // Final fallback if a user has no valid roles (should not happen in normal flow)
                return redirect('/');
            }
        });

        // ! for custom redirect after registration  
        $this->app->instance(RegisterResponse::class, new class implements RegisterResponse {
            public function toResponse($request)
            {
                // $user = $request->user();

                // if ($user->role === 'admin') {
                //     // return new RedirectResponse(Filament::getPanel('admin')->getUrl());
                //     return redirect(Filament::getPanel('admin')->getUrl());
                // }

                // if ($user->role === 'instructor') {
                //     // return new RedirectResponse(Filament::getPanel('instructor')->getUrl());
                //     return redirect(Filament::getPanel('instructor')->getUrl());
                // }

                // // return new RedirectResponse(route('dashboard'));
                // return redirect()->route('dashboard');

                $user = $request->user();

                if ($user->hasRole('instructor')) {
                    return redirect()->route('instructor.pending');
                }

                // if ($user->hasRole('instructor')) { // <-- Use hasRole()
                //     return redirect(Filament::getPanel('instructor')->getUrl());
                // }

                return redirect()->route('dashboard');
            }
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::redirectUserForTwoFactorAuthenticationUsing(RedirectIfTwoFactorAuthenticatable::class);

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())) . '|' . $request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });



        // Add all your view bindings here
        Fortify::loginView(function () {
            return view('auth.login');
        });

        Fortify::registerView(function () {
            return view('auth.register');
        });

        Fortify::requestPasswordResetLinkView(function () {
            return view('auth.forgot-password');
        });

        Fortify::resetPasswordView(function (object $request) {
            return view('auth.reset-password', ['request' => $request]);
        });

        Fortify::verifyEmailView(function () {
            return view('auth.verify-email');
        });
    }
}
