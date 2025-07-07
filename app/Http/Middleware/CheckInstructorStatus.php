<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckInstructorStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && $user->hasRole('instructor') && $user->instructorProfile?->application_status === 'pending') {
            if ($request->routeIs('instructor.pending')) {
                return $next($request);
            }
            return redirect()->route('instructor.pending');
        }
        return $next($request);
    }
}
