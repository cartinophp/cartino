<?php

declare(strict_types=1);

namespace Cartino\GraphQL\Mutations;

use Cartino\Models\Customer;
use GraphQL\Error\Error;
use Illuminate\Support\Facades\Hash;

class AuthMutations
{
    /**
     * Login mutation
     */
    public function login($root, array $args): array
    {
        $customer = Customer::where('email', $args['email'])->first();

        if (! $customer || ! Hash::check($args['password'], $customer->password)) {
            throw new Error('Invalid credentials');
        }

        $token = $customer->createToken('api-token')->plainTextToken;

        return [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => config('sanctum.expiration', 525600),
            'customer' => $customer,
        ];
    }

    /**
     * Register mutation
     */
    public function register($root, array $args): array
    {
        $customer = Customer::create([
            'first_name' => $args['input']['first_name'],
            'last_name' => $args['input']['last_name'],
            'email' => $args['input']['email'],
            'password' => Hash::make($args['input']['password']),
        ]);

        $token = $customer->createToken('api-token')->plainTextToken;

        return [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => config('sanctum.expiration', 525600),
            'customer' => $customer,
        ];
    }

    /**
     * Logout mutation
     */
    public function logout($root, array $args, $context): array
    {
        $context->user()->currentAccessToken()->delete();

        return [
            'message' => 'Successfully logged out',
        ];
    }
}
