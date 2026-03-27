<?php

namespace Cartino\Database\Seeders;

use Cartino\Models\ApiKey;
use Illuminate\Database\Seeder;

class ApiKeySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Chiave di test pubblica (full access)
        $testKey = 'ck_test_public_key_1234567890abcdef';

        ApiKey::create([
            'name' => 'Test Public Key',
            'key' => ApiKey::hash($testKey),
            'description' => 'Chiave di test per sviluppo con accesso completo. NON USARE IN PRODUZIONE!',
            'type' => 'full_access',
            'permissions' => null,
            'is_active' => true,
            'expires_at' => now()->addYears(10),
            'created_by' => 1, // Super admin
        ]);

        echo "✓ API Key di test creata: {$testKey}\n";
        echo "  Aggiungi al tuo .env: CARTINO_TEST_API_KEY={$testKey}\n\n";

        // Chiave read-only per analytics
        $analyticsKey = ApiKey::generate();

        ApiKey::create([
            'name' => 'Analytics Read-Only',
            'key' => ApiKey::hash($analyticsKey),
            'description' => 'Chiave read-only per servizi di analytics esterni',
            'type' => 'read_only',
            'permissions' => null,
            'is_active' => true,
            'expires_at' => null,
            'created_by' => 1,
        ]);

        echo "✓ API Key Analytics creata: {$analyticsKey}\n\n";

        // Chiave custom per integrazioni specifiche
        $integrationKey = ApiKey::generate();

        ApiKey::create([
            'name' => 'Custom Integration',
            'key' => ApiKey::hash($integrationKey),
            'description' => 'Chiave custom per integrazioni con permessi specifici',
            'type' => 'custom',
            'permissions' => [
                'view products',
                'create orders',
                'view orders',
                'view customers',
            ],
            'is_active' => true,
            'expires_at' => now()->addYear(),
            'created_by' => 1,
        ]);

        echo "✓ API Key Custom creata: {$integrationKey}\n";
        echo "  Permessi: view products, create/view orders, view customers\n\n";
    }
}
