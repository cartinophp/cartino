<?php

namespace Cartino\Http\Middleware;

use Cartino\Models\Site;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleSiteContext
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Extract site from URL if present
        // Example: /api/sites/main/products or /api/products
        $segments = $request->segments();

        if (count($segments) >= 3 && $segments[0] === 'api' && $segments[1] === 'sites') {
            $siteHandle = $segments[2];
            $site = Site::where('handle', $siteHandle)->enabled()->first();

            if (! $site) {
                return response()->json(['error' => 'Site not found'], 404);
            }

            // Set current site in session for models to use
            session(['current_site_id' => $site->id]);
            $request->merge(['site_id' => $site->id, 'site' => $site->handle]);
        } else {
            // Use default site
            $site = Site::default()->first();
            if ($site) {
                session(['current_site_id' => $site->id]);
                $request->merge(['site_id' => $site->id, 'site' => $site->handle]);
            }
        }

        return $next($request);
    }
}
