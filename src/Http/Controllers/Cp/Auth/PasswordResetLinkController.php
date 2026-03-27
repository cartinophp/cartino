<?php

namespace Cartino\Http\Controllers\Cp\Auth;

use Cartino\Http\Controllers\Controller;
use Cartino\Http\Controllers\Cp\Concerns\HandlesFlashMessages;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class PasswordResetLinkController extends Controller
{
    use HandlesFlashMessages;

    /**
     * Display the password reset link request view.
     */
    public function create(): Response
    {
        return Inertia::render('auth/forgot-password', [
            'status' => session('status'),
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
     * Handle an incoming password reset link request.
     */
    public function store(Request $request): RedirectResponse
    {
        // Rate limiting
        $this->ensureIsNotRateLimited($request);

        // Validate email
        $request->validate(
            [
                'email' => ['required', 'email'],
            ],
            [
                'email.required' => __('cartino::auth.validation.email_required'),
                'email.email' => __('cartino::auth.validation.email_invalid'),
            ],
        );

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink($request->only('email'));

        // Clear rate limiting on success
        if ($status === Password::RESET_LINK_SENT) {
            RateLimiter::clear($this->throttleKey($request));
        } else {
            RateLimiter::hit($this->throttleKey($request));
        }

        if ($status === Password::RESET_LINK_SENT) {
            $this->flashSuccess(__('cartino::auth.password_reset_sent'));

            return back();
        }

        $this->flashError(__('cartino::auth.password_reset_failed'));

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => __('cartino::auth.password_reset_failed')]);
    }

    /**
     * Ensure the password reset request is not rate limited.
     */
    protected function ensureIsNotRateLimited(Request $request): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey($request), 3)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        throw ValidationException::withMessages([
            'email' => __('cartino::auth.password_reset_throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    protected function throttleKey(Request $request): string
    {
        return 'password-reset:'.Str::lower($request->input('email')).'|'.$request->ip();
    }
}
