<?php

namespace Cartino\Http\Controllers\Auth;

use Cartino\Http\Controllers\Controller;
use Cartino\Models\SocialAccount;
use Cartino\Models\User;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    /**
     * Show social login options.
     */
    public function index(): Response
    {
        return Inertia::render('Auth/Social', [
            'providers' => $this->getEnabledProviders(),
            'intendedUrl' => session('url.intended'),
        ]);
    }

    /**
     * Redirect to OAuth provider.
     */
    public function redirect(Request $request, string $provider): RedirectResponse
    {
        if (! $this->isProviderSupported($provider)) {
            return redirect()->route('cp.login')->withErrors(['provider' => __('auth.social.unsupported_provider')]);
        }

        try {
            // Store the intended URL for after authentication
            if ($request->has('intended')) {
                session(['url.intended' => $request->get('intended')]);
            }

            // Configure provider-specific scopes and parameters
            $driver = Socialite::driver($provider);

            switch ($provider) {
                case 'google':
                    $driver->scopes(['openid', 'profile', 'email']);
                    break;
                case 'facebook':
                    $driver->scopes(['email', 'public_profile']);
                    break;
                case 'github':
                    $driver->scopes(['user:email']);
                    break;
                case 'linkedin':
                    $driver->scopes(['r_liteprofile', 'r_emailaddress']);
                    break;
                case 'microsoft':
                    $driver->scopes(['openid', 'profile', 'email']);
                    break;
                case 'discord':
                    $driver->scopes(['identify', 'email']);
                    break;
            }

            return $driver->redirect();
        } catch (Exception $e) {
            Log::error("OAuth redirect failed for provider {$provider}: ".$e->getMessage());

            return redirect()->route('cp.login')->withErrors(['provider' => __('auth.social.redirect_failed')]);
        }
    }

    /**
     * Handle OAuth callback.
     */
    public function callback(Request $request, string $provider): RedirectResponse
    {
        if (! $this->isProviderSupported($provider)) {
            return redirect()->route('cp.login')->withErrors(['provider' => __('auth.social.unsupported_provider')]);
        }

        try {
            // Handle OAuth errors
            if ($request->has('error')) {
                $error = $request->get('error');
                $errorDescription = $request->get('error_description', 'Authentication was cancelled or failed');

                Log::warning("OAuth error for provider {$provider}: {$error} - {$errorDescription}");

                return redirect()
                    ->route('cp.login')
                    ->withErrors(['oauth' => __('auth.social.authentication_cancelled')]);
            }

            // Get user info from provider
            $providerUser = Socialite::driver($provider)->user();

            if (! $providerUser->getEmail()) {
                return redirect()->route('cp.login')->withErrors(['oauth' => __('auth.social.email_required')]);
            }

            // Create or update user
            $user = User::createOrUpdateFromProvider($provider, $providerUser);

            // Log the user in
            Auth::login($user, true);

            // Log successful authentication
            Log::info("User {$user->id} authenticated via {$provider}");

            // Redirect to intended URL or dashboard
            $intendedUrl = session('url.intended', route('dashboard'));
            session()->forget('url.intended');

            return redirect()
                ->to($intendedUrl)
                ->with('success', __('auth.social.login_success', ['provider' => Str::title($provider)]));
        } catch (Exception $e) {
            Log::error("OAuth callback failed for provider {$provider}: ".$e->getMessage(), [
                'exception' => $e,
                'request_data' => $request->except(['state']),
            ]);

            return redirect()->route('cp.login')->withErrors(['oauth' => __('auth.social.authentication_failed')]);
        }
    }

    /**
     * Link a new provider to existing account.
     */
    public function link(Request $request, string $provider): RedirectResponse
    {
        if (! Auth::check()) {
            return redirect()->route('cp.login');
        }

        if (! $this->isProviderSupported($provider)) {
            return redirect()->back()->withErrors(['provider' => __('auth.social.unsupported_provider')]);
        }

        // Check if user already has this provider linked
        if (Auth::user()->hasSocialAccount($provider)) {
            return redirect()
                ->back()
                ->withErrors(['provider' => __('auth.social.already_linked', ['provider' => Str::title($provider)])]);
        }

        try {
            // Store linking flag in session
            session(['linking_provider' => $provider]);

            return Socialite::driver($provider)->redirect();
        } catch (Exception $e) {
            Log::error("OAuth linking failed for provider {$provider}: ".$e->getMessage());

            return redirect()->back()->withErrors(['provider' => __('auth.social.link_failed')]);
        }
    }

    /**
     * Handle linking callback.
     */
    public function linkCallback(Request $request, string $provider): RedirectResponse
    {
        if (! Auth::check()) {
            return redirect()->route('cp.login');
        }

        $linkingProvider = session('linking_provider');
        if ($linkingProvider !== $provider) {
            return redirect()->back()->withErrors(['provider' => __('auth.social.invalid_linking_session')]);
        }

        session()->forget('linking_provider');

        try {
            $providerUser = Socialite::driver($provider)->user();
            $user = Auth::user();

            // Check if this provider account is already linked to another user
            $existingAccount = SocialAccount::where('provider', $provider)
                ->where('provider_id', $providerUser->getId())
                ->first();

            if ($existingAccount && $existingAccount->user_id !== $user->id) {
                return redirect()->back()->withErrors(['provider' => __('auth.social.account_already_linked')]);
            }

            // Create or update social account
            $user->socialAccounts()->updateOrCreate(
                [
                    'provider' => $provider,
                    'provider_id' => $providerUser->getId(),
                ],
                [
                    'provider_token' => $providerUser->token,
                    'provider_refresh_token' => $providerUser->refreshToken,
                    'provider_data' => [
                        'name' => $providerUser->getName(),
                        'email' => $providerUser->getEmail(),
                        'avatar' => $providerUser->getAvatar(),
                        'raw' => $providerUser->getRaw(),
                        'linked_at' => now(),
                    ],
                ],
            );

            Log::info("User {$user->id} linked {$provider} account");

            return redirect()
                ->back()
                ->with('success', __('auth.social.link_success', ['provider' => Str::title($provider)]));
        } catch (Exception $e) {
            Log::error("OAuth link callback failed for provider {$provider}: ".$e->getMessage());

            return redirect()->back()->withErrors(['provider' => __('auth.social.link_failed')]);
        }
    }

    /**
     * Unlink a provider from user account.
     */
    public function unlink(Request $request, string $provider): RedirectResponse
    {
        if (! Auth::check()) {
            return redirect()->route('cp.login');
        }

        $user = Auth::user();
        $socialAccount = $user->getSocialAccount($provider);

        if (! $socialAccount) {
            return redirect()
                ->back()
                ->withErrors(['provider' => __('auth.social.not_linked', ['provider' => Str::title($provider)])]);
        }

        // Prevent unlinking if it's the only authentication method
        if (! $user->hasPassword() && $user->socialAccounts()->count() <= 1) {
            return redirect()->back()->withErrors(['provider' => __('auth.social.cannot_unlink_only_method')]);
        }

        try {
            $socialAccount->delete();

            // Clear primary provider if this was it
            if ($user->provider === $provider) {
                $user->update([
                    'provider' => null,
                    'provider_id' => null,
                    'provider_token' => null,
                    'provider_refresh_token' => null,
                ]);
            }

            Log::info("User {$user->id} unlinked {$provider} account");

            return redirect()
                ->back()
                ->with('success', __('auth.social.unlink_success', ['provider' => Str::title($provider)]));
        } catch (Exception $e) {
            Log::error("OAuth unlink failed for provider {$provider}: ".$e->getMessage());

            return redirect()->back()->withErrors(['provider' => __('auth.social.unlink_failed')]);
        }
    }

    /**
     * Get enabled OAuth providers.
     */
    private function getEnabledProviders(): array
    {
        $allProviders = SocialAccount::getSupportedProviders();
        $enabledProviders = [];

        foreach ($allProviders as $key => $provider) {
            if ($this->isProviderConfigured($key)) {
                $enabledProviders[$key] = $provider;
            }
        }

        return $enabledProviders;
    }

    /**
     * Check if provider is supported.
     */
    private function isProviderSupported(string $provider): bool
    {
        return SocialAccount::isProviderSupported($provider);
    }

    /**
     * Check if provider is configured with credentials.
     */
    private function isProviderConfigured(string $provider): bool
    {
        $config = config("services.{$provider}");

        return $config && ! empty($config['client_id']) && ! empty($config['client_secret']);
    }
}
