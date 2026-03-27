<?php

namespace Cartino\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ControlPanelMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (! Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect()->guest(route('cp.login'));
        }

        $user = Auth::user();

        // Check if user can access control panel
        if (! $this->canAccessControlPanel($user)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Access denied.'], 403);
            }

            Auth::logout();

            return redirect()->route('cp.login')->withErrors(['email' => __('cartino::auth.cp_access_denied')]);
        }

        return $next($request);
    }

    /**
     * Check if user can access the control panel.
     */
    protected function canAccessControlPanel($user): bool
    {
        if (! $user) {
            return false;
        }

        try {
            // Check if user has CP access permission or is admin
            if (method_exists($user, 'can') && method_exists($user, 'hasRole')) {
                return $user->can('access-cp') || $user->hasRole('admin') || $user->hasRole('super-admin');
            }

            // Fallback: check if user has specific field
            if (isset($user->can_access_cp)) {
                return (bool) $user->can_access_cp;
            }

            // Default: allow if user exists (configure based on your needs)
            return true;
        } catch (\Exception $e) {
            Log::warning('ControlPanelMiddleware - Permission check failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id ?? null,
            ]);

            // Default to true to avoid blocking access
            return true;
        }
    }
}
