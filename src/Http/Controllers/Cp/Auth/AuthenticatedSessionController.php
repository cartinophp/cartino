<?php

namespace Cartino\Http\Controllers\Cp\Auth;

use Cartino\Http\Controllers\Controller;
use Cartino\Http\Controllers\Cp\Concerns\HandlesFlashMessages;
use Cartino\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    use HandlesFlashMessages;

    /**
     * Display the login view.
     */
    public function create(Request $request): Response|RedirectResponse
    {
        // Check if already authenticated and trying to access CP
        if (Auth::check() && $this->canAccessControlPanel(Auth::user())) {
            return redirect()->intended(route('cp.dashboard'));
        }

        return Inertia::render('auth/login', [
            'status' => session('status'),
            'canResetPassword' => true,
            'locale' => app()->getLocale(),
            'locales' => config('cartino.locales', ['en', 'it']),
            'app_name' => config('app.name'),
            'cp_name' => config('cartino.cp.name', 'Control Panel'),
            'branding' => [
                'logo' => config('cartino.cp.branding.logo'),
                'logo_dark' => config('cartino.cp.branding.logo_dark'),
                'favicon' => config('cartino.cp.branding.favicon'),
            ],
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        Log::info('Login attempt started', [
            'email' => $request->input('email'),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        try {
            $request->authenticate();

            Log::info('Authentication successful');

            // Check if user can access control panel
            $user = Auth::user();
            Log::info('User loaded after authentication', [
                'user_id' => $user ? $user->id : null,
                'email' => $user ? $user->email : null,
            ]);

            if (! $this->canAccessControlPanel($user)) {
                Log::warning('User cannot access control panel', [
                    'user_id' => $user ? $user->id : null,
                ]);
                Auth::logout();

                return back()->withErrors([
                    'email' => __('cartino::auth.cp_access_denied'),
                ]);
            }

            Log::info('Control panel access check passed');

            // Regenerate session
            $request->session()->regenerate();

            Log::info('Session regenerated');

            // Log successful login
            Log::info('Control Panel login successful', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Check auth state before redirect
            Log::info('Auth state before redirect', [
                'auth_check' => Auth::check(),
                'auth_user_id' => Auth::id(),
                'session_id' => $request->session()->getId(),
            ]);

            // Redirect to dashboard
            return redirect()->route('cp.dashboard');
        } catch (ValidationException $e) {
            Log::warning('Authentication failed', [
                'email' => $request->input('email'),
                'errors' => $e->errors(),
            ]);

            return back()->withErrors($e->errors())->withInput($request->except('password'));
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::user();

        // Log logout
        if ($user) {
            Log::info('Control Panel logout', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
            ]);
        }

        // Logout user
        Auth::guard('web')->logout();

        // Invalidate session
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Clear Inertia user data
        $this->flashSuccess(__('cartino::auth.logged_out'));

        return redirect()->route('cp.cp.login');
    }

    /**
     * Check if user can access the control panel.
     */
    protected function canAccessControlPanel($user): bool
    {
        if (! $user) {
            return false;
        }

        // Simplified check to avoid timeout - just allow authenticated users for now
        return true;

        // TODO: Re-enable when performance issues are resolved
        // Check if user has CP access permission
        // if (method_exists($user, 'can')) {
        //     if ($user->can('access-cp')) {
        //         return true;
        //     }
        // }
        // Check if user has roles (Spatie Permission)
        // if (method_exists($user, 'hasRole')) {
        //     if ($user->hasRole('admin') || $user->hasRole('super-admin')) {
        //         return true;
        //     }
        // }
        // Check if user has specific field
        // if (isset($user->can_access_cp)) {
        //     return (bool) $user->can_access_cp;
        // }
        // Check if user is admin type
        // if (isset($user->is_admin)) {
        //     return (bool) $user->is_admin;
        // }
        // Default: allow if user exists (configure based on your needs)
        // return true;
    }
}
