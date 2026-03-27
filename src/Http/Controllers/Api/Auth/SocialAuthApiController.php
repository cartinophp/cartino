<?php

namespace Cartino\Http\Controllers\Api\Auth;

use Cartino\Http\Controllers\Api\ApiController;
use Cartino\Models\SocialAccount;
use Cartino\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthApiController extends ApiController
{
    /**
     * Get available OAuth providers.
     */
    public function providers(): JsonResponse
    {
        $providers = [];
        $allProviders = SocialAccount::getSupportedProviders();

        foreach ($allProviders as $key => $provider) {
            if ($this->isProviderConfigured($key)) {
                $providers[$key] = [
                    'name' => $provider['name'],
                    'icon' => $provider['icon'],
                    'color' => $provider['color'],
                    'auth_url' => route('api.auth.social.redirect', $key),
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'providers' => $providers,
                'enabled_count' => count($providers),
            ],
        ]);
    }

    /**
     * Get OAuth redirect URL for SPA authentication.
     */
    public function getRedirectUrl(Request $request, string $provider): JsonResponse
    {
        if (! $this->isProviderSupported($provider)) {
            return response()->json([
                'success' => false,
                'message' => 'Unsupported OAuth provider',
            ], 400);
        }

        if (! $this->isProviderConfigured($provider)) {
            return response()->json([
                'success' => false,
                'message' => 'OAuth provider not configured',
            ], 400);
        }

        try {
            // Generate state for CSRF protection
            $state = base64_encode(json_encode([
                'provider' => $provider,
                'timestamp' => time(),
                'nonce' => bin2hex(random_bytes(16)),
                'intended_url' => $request->get('intended_url'),
                'api_auth' => true,
            ]));

            // Build OAuth URL
            $driver = Socialite::driver($provider);

            // Configure scopes based on provider
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
            }

            $authUrl = $driver->stateless()->redirect()->getTargetUrl();

            return response()->json([
                'success' => true,
                'data' => [
                    'auth_url' => $authUrl,
                    'state' => $state,
                    'provider' => $provider,
                ],
            ]);
        } catch (Exception $e) {
            Log::error("Failed to generate OAuth URL for {$provider}: ".$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate authentication URL',
            ], 500);
        }
    }

    /**
     * Handle OAuth callback and return API token.
     */
    public function callback(Request $request, string $provider): JsonResponse
    {
        if (! $this->isProviderSupported($provider)) {
            return response()->json([
                'success' => false,
                'message' => 'Unsupported OAuth provider',
            ], 400);
        }

        // Validate request
        $validator = Validator::make($request->all(), [
            'code' => 'required|string',
            'state' => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OAuth callback data',
                'errors' => $validator->errors(),
            ], 400);
        }

        try {
            // Get user from provider using the authorization code
            $providerUser = Socialite::driver($provider)->stateless()->user();

            if (! $providerUser->getEmail()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email address is required for registration',
                ], 400);
            }

            // Create or update user
            $user = User::createOrUpdateFromProvider($provider, $providerUser);

            // Generate API token
            $tokenName = "oauth-{$provider}-".now()->timestamp;
            $token = $user->createToken($tokenName, ['*'], now()->addDays(30));

            // Log successful authentication
            Log::info("API OAuth authentication successful for user {$user->id} via {$provider}");

            return response()->json([
                'success' => true,
                'message' => 'Authentication successful',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'avatar_url' => $user->avatar_url,
                        'provider' => $provider,
                        'email_verified' => ! is_null($user->email_verified_at),
                        'connected_providers' => $user->connected_providers,
                    ],
                    'token' => [
                        'access_token' => $token->plainTextToken,
                        'token_type' => 'Bearer',
                        'expires_at' => $token->accessToken->expires_at?->toISOString(),
                    ],
                ],
            ]);
        } catch (Exception $e) {
            Log::error("API OAuth callback failed for {$provider}: ".$e->getMessage(), [
                'exception' => $e,
                'request_data' => $request->except(['code', 'state']),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Authentication failed',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Link OAuth provider to authenticated user.
     */
    public function link(Request $request, string $provider): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required',
            ], 401);
        }

        if (! $this->isProviderSupported($provider)) {
            return response()->json([
                'success' => false,
                'message' => 'Unsupported OAuth provider',
            ], 400);
        }

        if ($user->hasSocialAccount($provider)) {
            return response()->json([
                'success' => false,
                'message' => "Your account is already linked to {$provider}",
            ], 400);
        }

        // Validate request
        $validator = Validator::make($request->all(), [
            'code' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OAuth data',
                'errors' => $validator->errors(),
            ], 400);
        }

        try {
            $providerUser = Socialite::driver($provider)->stateless()->user();

            // Check if this provider account is already linked to another user
            $existingAccount = SocialAccount::where('provider', $provider)
                ->where('provider_id', $providerUser->getId())
                ->first();

            if ($existingAccount && $existingAccount->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'This account is already linked to another user',
                ], 400);
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

            Log::info("User {$user->id} linked {$provider} account via API");

            return response()->json([
                'success' => true,
                'message' => "Successfully linked your {$provider} account",
                'data' => [
                    'connected_providers' => $user->fresh()->connected_providers,
                ],
            ]);
        } catch (Exception $e) {
            Log::error("API OAuth linking failed for {$provider}: ".$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to link account',
            ], 500);
        }
    }

    /**
     * Unlink OAuth provider from authenticated user.
     */
    public function unlink(Request $request, string $provider): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required',
            ], 401);
        }

        $socialAccount = $user->getSocialAccount($provider);

        if (! $socialAccount) {
            return response()->json([
                'success' => false,
                'message' => "Your account is not linked to {$provider}",
            ], 400);
        }

        // Prevent unlinking if it's the only authentication method
        if (! $user->hasPassword() && $user->socialAccounts()->count() <= 1) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot unlink the only authentication method. Please set a password first.',
            ], 400);
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

            Log::info("User {$user->id} unlinked {$provider} account via API");

            return response()->json([
                'success' => true,
                'message' => "Successfully unlinked your {$provider} account",
                'data' => [
                    'connected_providers' => $user->fresh()->connected_providers,
                ],
            ]);
        } catch (Exception $e) {
            Log::error("API OAuth unlinking failed for {$provider}: ".$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to unlink account',
            ], 500);
        }
    }

    /**
     * Get user's connected OAuth providers.
     */
    public function connectedProviders(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required',
            ], 401);
        }

        $socialAccounts = $user->socialAccounts()->get();
        $providers = [];

        foreach ($socialAccounts as $account) {
            $providerConfig = SocialAccount::getSupportedProviders()[$account->provider] ?? null;

            if ($providerConfig) {
                $providers[] = [
                    'provider' => $account->provider,
                    'name' => $providerConfig['name'],
                    'icon' => $providerConfig['icon'],
                    'color' => $providerConfig['color'],
                    'display_name' => $account->display_name,
                    'linked_at' => $account->created_at,
                    'can_unlink' => $user->hasPassword() || $socialAccounts->count() > 1,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'connected_providers' => $providers,
                'has_password' => $user->hasPassword(),
                'total_count' => count($providers),
            ],
        ]);
    }

    /**
     * Refresh OAuth token for provider.
     */
    public function refreshToken(Request $request, string $provider): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required',
            ], 401);
        }

        $socialAccount = $user->getSocialAccount($provider);

        if (! $socialAccount) {
            return response()->json([
                'success' => false,
                'message' => "No {$provider} account linked",
            ], 400);
        }

        try {
            // This would need provider-specific refresh logic
            // For now, return the current token status
            return response()->json([
                'success' => true,
                'data' => [
                    'provider' => $provider,
                    'token_expired' => $socialAccount->isTokenExpired(),
                    'last_updated' => $socialAccount->updated_at,
                ],
            ]);
        } catch (Exception $e) {
            Log::error("OAuth token refresh failed for {$provider}: ".$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to refresh token',
            ], 500);
        }
    }

    /**
     * Check if provider is supported.
     */
    private function isProviderSupported(string $provider): bool
    {
        return SocialAccount::isProviderSupported($provider);
    }

    /**
     * Check if provider is configured.
     */
    private function isProviderConfigured(string $provider): bool
    {
        $config = config("services.{$provider}");

        return $config && ! empty($config['client_id']) && ! empty($config['client_secret']);
    }
}
