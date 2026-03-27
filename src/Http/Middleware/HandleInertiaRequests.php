<?php

namespace Cartino\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class HandleInertiaRequests
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Share auth data globally with Inertia
        Inertia::share([
            'csrf_token' => csrf_token(),
            // Authentication
            'auth' => function () {
                try {
                    $user = Auth::user();
                    Log::info('HandleInertiaRequests - Auth data', [
                        'auth_check' => Auth::check(),
                        'user_id' => $user ? $user->id : null,
                        'user_email' => $user ? $user->email : null,
                    ]);

                    if (! $user) {
                        return ['user' => null];
                    }

                    // Get user name safely
                    $name =
                        $user->name ??
                        (isset($user->first_name) ? trim($user->first_name.' '.($user->last_name ?? '')) : null) ??
                            'User';

                    // Safely check CP access without causing issues
                    $canAccessCP = true; // Default to true to avoid blocking

                    try {
                        if (method_exists($user, 'can') && method_exists($user, 'hasRole')) {
                            $canAccessCP =
                                $user->can('access-cp') || $user->hasRole('admin') || $user->hasRole('super-admin');
                        } elseif (isset($user->can_access_cp)) {
                            $canAccessCP = (bool) $user->can_access_cp;
                        }
                    } catch (\Exception $e) {
                        Log::warning('HandleInertiaRequests - Permission check failed', [
                            'error' => $e->getMessage(),
                            'user_id' => $user->id,
                        ]);

                        // Keep default true value
                    }

                    return [
                        'user' => [
                            'id' => $user->id,
                            'name' => $name,
                            'email' => $user->email,
                            'can_access_cp' => $canAccessCP,
                        ],
                    ];
                } catch (\Exception $e) {
                    Log::error('HandleInertiaRequests - Critical error', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);

                    return ['user' => null];
                }
            },
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
                'info' => fn () => $request->session()->get('info'),
                'status' => fn () => $request->session()->get('status'),
            ],
            'errors' => function () use ($request) {
                return $request->session()->get('errors')
                    ? $request->session()->get('errors')->getBag('default')->getMessages()
                    : [];
            },
            'locale' => app()->getLocale(),
            'locales' => config('cartino.locales', ['en', 'it']),
            'app' => [
                'name' => config('app.name'),
                'url' => config('app.url'),
                'debug' => config('app.debug'),
            ],
            'cp' => [
                'name' => config('cartino.cp.name', 'Control Panel'),
                'url' => config('cartino.cp.url', '/cp'),
                'branding' => [
                    'logo' => config('cartino.cp.branding.logo'),
                    'logo_dark' => config('cartino.cp.branding.logo_dark'),
                    'favicon' => config('cartino.cp.branding.favicon'),
                ],
            ],
        ]);

        return $next($request);
    }

    /**
     * Check if user can access control panel.
     */
    protected function canUserAccessCP($user): bool
    {
        if (! $user) {
            return false;
        }

        // Check if user has CP access permission or role
        if (method_exists($user, 'can')) {
            if ($user->can('access-cp') || $user->hasRole('admin') || $user->hasRole('super-admin')) {
                return true;
            }
        }

        // Check if user has specific field
        if (isset($user->can_access_cp)) {
            return (bool) $user->can_access_cp;
        }

        // Default: allow if user exists
        return true;
    }

    /**
     * Generate Gravatar URL.
     */
    protected function getGravatar(string $email): string
    {
        return 'https://www.gravatar.com/avatar/'.md5(strtolower(trim($email))).'?s=64&d=mp';
    }
}
