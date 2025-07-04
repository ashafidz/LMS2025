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

        // // Check if user is an instructor and their application is pending
        // if ($user && $user->role === 'instructor' && $user->instructorProfile?->application_status === 'pending') {
        //     // If they are already on the pending page, let them stay. Prevents redirect loop.
        //     if ($request->routeIs('instructor.pending')) {
        //         return $next($request);
        //     }
        //     // Redirect them to the pending page
        //     return redirect()->route('instructor.pending');
        // }

        // return $next($request);

        if ($user && $user->hasRole('instructor') && $user->instructorProfile?->application_status === 'pending') {
            if ($request->routeIs('instructor.pending')) {
                return $next($request);
            }
            return redirect()->route('instructor.pending');
        }
        return $next($request);
    }
}
