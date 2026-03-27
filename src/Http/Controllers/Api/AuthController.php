<?php

declare(strict_types=1);

namespace Cartino\Http\Controllers\Api;

use Cartino\Http\Controllers\Controller;
use Cartino\Http\Resources\ApiKeyResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $userModel = config('cartino.auth.model', 'App\\Models\\User');
        $user = $userModel::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('cartino-api')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $userModel = config('cartino.auth.model', 'App\\Models\\User');

        $user = $userModel::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('cartino-api')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    public function logout(Request $request): JsonResponse
    {
        $apiKey = $request->input('_api_key');

        // Se autenticato via Sanctum e token presente, revoca il token corrente
        if ($request->user() && method_exists($request->user(), 'currentAccessToken')) {
            $token = $request->user()->currentAccessToken();
            if ($token) {
                $token->delete();
            }
        }

        $capabilities = null;
        if ($apiKey) {
            $capabilities = $this->buildApiKeyCapabilities($apiKey);
        }

        return response()->json([
            'message' => 'Successfully logged out',
            'authenticated_via' => $apiKey ? 'api_key' : 'sanctum',
            'api_key_id' => $apiKey?->id,
            'api_key_type' => $apiKey?->type,
            'api_key_permissions' => $apiKey && $apiKey->type === 'custom' ? $apiKey->permissions : null,
            'api_key' => $apiKey ? new ApiKeyResource($apiKey) : null,
            'api_key_capabilities' => $capabilities,
        ]);
    }

    /**
     * Restituisce le informazioni sull'identità corrente (utente Sanctum o API key).
     */
    public function me(Request $request): JsonResponse
    {
        $apiKey = $request->input('_api_key');
        $authVia = $apiKey ? 'api_key' : 'sanctum';

        if (! $request->user() && ! $apiKey) {
            return response()->json([
                'message' => 'Unauthenticated',
            ], 401);
        }

        $capabilities = null;
        if ($apiKey) {
            $capabilities = $this->buildApiKeyCapabilities($apiKey);
        }

        return response()->json([
            'authenticated_via' => $authVia,
            'user' => $apiKey ? null : $request->user(),
            'api_key_id' => $apiKey?->id,
            'api_key_type' => $apiKey?->type,
            'api_key_permissions' => $apiKey && $apiKey->type === 'custom' ? $apiKey->permissions : null,
            'api_key' => $apiKey ? new ApiKeyResource($apiKey) : null,
            'api_key_capabilities' => $capabilities,
        ]);
    }

    private function buildApiKeyCapabilities($apiKey): array
    {
        $type = $apiKey->type;

        // Default flags
        $canRead = false;
        $canCreate = false;
        $canUpdate = false;
        $canDelete = false;

        if ($type === 'full_access') {
            $canRead = $canCreate = $canUpdate = $canDelete = true;
        } elseif ($type === 'read_only') {
            $canRead = true;
        } elseif ($type === 'custom' && is_array($apiKey->permissions)) {
            foreach ($apiKey->permissions as $perm) {
                if (! is_string($perm)) {
                    continue;
                }

                if (str_contains($perm, 'view') || str_contains($perm, 'read') || str_contains($perm, 'list')) {
                    $canRead = true;
                }
                if (str_contains($perm, 'create') || str_contains($perm, 'write') || str_contains($perm, 'manage')) {
                    $canCreate = true;
                }
                if (
                    str_contains($perm, 'update') ||
                        str_contains($perm, 'edit') ||
                        str_contains($perm, 'write') ||
                        str_contains($perm, 'manage')
                ) {
                    $canUpdate = true;
                }
                if (str_contains($perm, 'delete') || str_contains($perm, 'destroy') || str_contains($perm, 'manage')) {
                    $canDelete = true;
                }
            }
        }

        $allowedHttpMethods = [];

        if ($canRead) {
            $allowedHttpMethods = array_merge($allowedHttpMethods, ['GET', 'HEAD', 'OPTIONS']);
        }
        if ($canCreate) {
            $allowedHttpMethods[] = 'POST';
        }
        if ($canUpdate) {
            $allowedHttpMethods = array_merge($allowedHttpMethods, ['PUT', 'PATCH']);
        }
        if ($canDelete) {
            $allowedHttpMethods[] = 'DELETE';
        }

        $allowedHttpMethods = array_values(array_unique($allowedHttpMethods));

        return [
            'type' => $type,
            'can_read' => $canRead,
            'can_create' => $canCreate,
            'can_update' => $canUpdate,
            'can_delete' => $canDelete,
            'allowed_http_methods' => $allowedHttpMethods,
            'permissions' => $type === 'custom' ? $apiKey->permissions : null,
        ];
    }
}
