<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class ConfigureSessionDomain
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Get the host from the request
        $host = $request->getHost();

        // Get the configured session domain
        $configuredDomain = config('session.domain');

        // Check if the host matches the configured domain
        // If not, try to adjust the session domain to match the current host
        if ($configuredDomain && !Str::endsWith($host, $configuredDomain)) {
            // Extract the main domain and TLD
            $parts = explode('.', $host);

            if (count($parts) >= 2) {
                // Get the last two parts (domain and TLD)
                $mainDomain = implode('.', array_slice($parts, -2));

                // Set the session domain to include all subdomains of the current domain
                Config::set('session.domain', '.' . $mainDomain);
            }
        }

        return $next($request);
    }
}
