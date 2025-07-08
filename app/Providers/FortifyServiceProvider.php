<?php

namespace App\Providers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Laravel\Fortify\Fortify;
use App\Actions\Fortify\CreateNewUser;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use Illuminate\Support\Facades\RateLimiter;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
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
                $user = $request->user();
                $loginPreference = $request->input('login_preference');

                // --- SUPERADMIN ---
                if ($user->hasRole('superadmin')) {
                    session(['active_role' => 'superadmin']); // <-- Add this
                    return redirect()->route('superadmin.dashboard');
                }

                // --- ADMIN ---
                if ($user->hasRole('admin')) {
                    session(['active_role' => 'admin']); // <-- Add this
                    return redirect()->route('admin.dashboard');
                }

                // --- INSTRUCTOR ---
                // Check if user chose "Instructor" AND is an approved instructor
                if (
                    $loginPreference === 'instructor' &&
                    $user->hasRole('instructor') &&
                    $user->instructorProfile?->application_status === 'approved'
                ) {
                    session(['active_role' => 'instructor']); // <-- Add this
                    return redirect()->route('instructor.dashboard');
                }

                // Check if user chose "Instructor" AND is an deative instructor
                if (
                    $loginPreference === 'instructor' &&
                    $user->hasRole('instructor') &&
                    $user->instructorProfile?->application_status === 'deactive'
                ) {

                    session(['active_role' => 'instructor']); // <-- Add this
                    return redirect()->route('instructor.deactive');
                }

                // check if user chose "Instructor" AND is a rejected instructor
                if (
                    $loginPreference === 'instructor' &&
                    $user->hasRole('instructor') &&
                    $user->instructorProfile?->application_status === 'rejected'
                ) {
                    session(['active_role' => 'instructor']);
                    return redirect()->route('instructor.rejected');
                }

                // --- STUDENT (DEFAULT / FALLBACK) ---
                // This will now handle:
                // 1. Users who only have the 'student' role.
                // 2. Users who chose to log in as a 'student'.
                // 3. Instructors whose application is still 'pending' (they can only be a student for now).
                if ($user->hasRole('student')) {
                    session(['active_role' => 'student']); // <-- Add this

                    // If they are a pending instructor, send them to the pending page
                    if ($loginPreference === 'instructor' && $user->hasRole('instructor') && $user->instructorProfile?->application_status === 'pending') {
                        return redirect()->route('instructor.pending');
                    }

                    // If they are a deactive instructor, send them to the deactive page
                    if ($loginPreference === 'instructor' && $user->hasRole('instructor') && $user->instructorProfile?->application_status === 'deactive') {
                        return redirect()->route('instructor.deactive');
                    }

                    // Otherwise, send them to the regular student dashboard
                    return redirect()->route('student.dashboard');
                }

                // Final fallback if a user has no valid roles
                return redirect('/');
            }
        });

        // ! for custom redirect after registration  
        $this->app->instance(RegisterResponse::class, new class implements RegisterResponse {
            public function toResponse($request)
            {
                $user = $request->user();

                // For any new user, their initial active role is 'student'.
                session(['active_role' => 'student']); // <-- Add this line

                // If they registered as an instructor, send them to the pending page.
                if ($user->hasRole('instructor')) {
                    return redirect()->route('instructor.pending');
                }

                // Otherwise, send new students to their dashboard.
                return redirect()->route('student.dashboard');
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
