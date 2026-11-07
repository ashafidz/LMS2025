<?php

namespace App\Http\Middleware;

use App\Services\Recaptcha;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * reCAPTCHA v3 (and, for register, a per-IP limit) on the Fortify auth forms. Fortify's routes live in vendor/, so this runs
 * in the web group and acts on the route name. Login keeps Fortify's own throttle and fails open when Google is unreachable.
 */
class ProtectAuthForms
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethod('POST')) {
            return $next($request);
        }

        $name = $request->route()?->getName();

        return match ($name) {
            // Limit first, so a flood of bots never triggers calls to Google
            'register.store' => app(ThrottleRequests::class)->handle(
                $request,
                fn (Request $r) => $this->checkCaptcha($r, $next, 'register', false, $name),
                'register'
            ),
            'login.store' => $this->checkCaptcha($request, $next, 'login', true, $name),
            'password.email' => $this->checkCaptcha($request, $next, 'forgot', false, $name),
            default => $next($request),
        };
    }

    private function checkCaptcha(Request $request, Closure $next, string $actionKey, bool $failOpen, string $routeName): Response
    {
        if (! Recaptcha::enabled()) {
            return $next($request);
        }

        $action = config('services.recaptcha.auth_actions.'.$actionKey);
        $token = $request->input('recaptcha-response');
        $reason = Recaptcha::failure($token, $action, $request->ip());

        if ($reason === null) {
            return $next($request);
        }

        if ($reason === 'unreachable' && $failOpen) {
            Log::info('Captcha auth dilewati karena Google tidak bisa dihubungi', ['route' => $routeName, 'ip' => $request->ip()]);

            return $next($request);
        }

        Log::info('Captcha auth ditolak', ['route' => $routeName, 'reason' => $reason, 'ip' => $request->ip()]);

        $message = match ($reason) {
            'missing' => Recaptcha::MISSING,
            'unreachable' => Recaptcha::UNREACHABLE,
            default => Recaptcha::SUSPICIOUS,
        };

        return back()
            ->withInput($request->except('password', 'password_confirmation', 'recaptcha-response'))
            ->withErrors(['email' => $message]);
    }
}
