<?php

declare(strict_types=1);

namespace Cartino\Http\Middleware;

use Cartino\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateApiKey
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip se è già autenticato via Sanctum
        if ($request->user()) {
            return $next($request);
        }

        $apiKey = $request->header('X-API-Key') ?? $request->query('api_key');

        if (! $apiKey) {
            return response()->json([
                'message' => 'API key mancante. Usa header X-API-Key o query parameter api_key',
            ], 401);
        }

        // Hash della key fornita
        $hashedKey = ApiKey::hash($apiKey);

        // Cerca la key nel database
        $key = ApiKey::where('key', $hashedKey)->active()->first();

        // Fallback: chiavi statiche definite via config/.env (sandbox/test)
        if (! $key && $this->isStaticApiKey($apiKey)) {
            $staticKey = $this->makeStaticApiKey($apiKey);
            $request->attributes->set('authenticated_via', 'static_api_key');
            $request->merge(['_api_key' => $staticKey]);

            return $next($request);
        }

        if (! $key || ! $key->isValid()) {
            return response()->json([
                'message' => 'API key non valida o scaduta',
            ], 401);
        }

        // Verifica accesso all'endpoint
        if (! $key->canAccessEndpoint($request->method(), $request->path())) {
            return response()->json([
                'message' => 'Accesso negato per questo endpoint',
            ], 403);
        }

        // Aggiorna last_used_at
        $key->markAsUsed();

        // Imposta l'API key nella request per uso successivo
        $request->merge(['_api_key' => $key]);

        return $next($request);
    }

    /**
     * Verifica se la chiave corrisponde a una static key definita in config.
     */
    private function isStaticApiKey(string $apiKey): bool
    {
        $staticKeys = config('cartino.api_keys.static', []);
        $testKey = config('cartino.api_keys.test');

        if ($testKey) {
            $staticKeys[] = $testKey;
        }

        foreach (array_filter(array_map('trim', (array) $staticKeys)) as $static) {
            if ($static !== '' && hash_equals($static, $apiKey)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Costruisce un'istanza ApiKey "virtuale" full_access per chiavi statiche.
     */
    private function makeStaticApiKey(string $plainKey): ApiKey
    {
        $apiKey = new ApiKey([
            'name' => 'Static Config Key',
            'description' => 'Chiave statica da config/env',
            'type' => 'full_access',
            'permissions' => null,
            'is_active' => true,
            'expires_at' => null,
        ]);

        // Non viene salvata su DB; garantiamo isValid() true
        $apiKey->exists = false;

        // Manteniamo la chiave in chiaro per eventuali log/uso downstream
        $apiKey->setAttribute('plain_key', $plainKey);

        return $apiKey;
    }
}
