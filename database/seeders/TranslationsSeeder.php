<?php

declare(strict_types=1);

namespace Cartino\Database\Seeders;

use Cartino\Models\Product;
use Cartino\Models\Translation;
use Illuminate\Database\Seeder;

class TranslationsSeeder extends Seeder
{
    public function run(): void
    {
        // Get sample products
        $tshirt = Product::where('slug', 'classic-tshirt')->first();
        $sneakers = Product::where('slug', 'sport-sneakers')->first();

        if (! $tshirt || ! $sneakers) {
            $this->command->warn('⚠️  Products not found. Please run MultiMarketPricesSeeder first.');

            return;
        }

        // ===========================================
        // T-SHIRT TRANSLATIONS
        // ===========================================

        // Italian
        Translation::firstOrCreate(
            [
                'translatable_type' => Product::class,
                'translatable_id' => $tshirt->id,
                'locale' => 'it_IT',
                'key' => 'name',
            ],
            [
                'value' => 'Maglietta Classica',
                'is_verified' => true,
                'source' => 'manual',
            ],
        );

        Translation::firstOrCreate(
            [
                'translatable_type' => Product::class,
                'translatable_id' => $tshirt->id,
                'locale' => 'it_IT',
                'key' => 'description',
            ],
            [
                'value' => 'Maglietta comoda in cotone 100%, perfetta per ogni giorno',
                'is_verified' => true,
                'source' => 'manual',
            ],
        );

        // French
        Translation::firstOrCreate(
            [
                'translatable_type' => Product::class,
                'translatable_id' => $tshirt->id,
                'locale' => 'fr_FR',
                'key' => 'name',
            ],
            [
                'value' => 'T-Shirt Classique',
                'is_verified' => true,
                'source' => 'manual',
            ],
        );

        Translation::firstOrCreate(
            [
                'translatable_type' => Product::class,
                'translatable_id' => $tshirt->id,
                'locale' => 'fr_FR',
                'key' => 'description',
            ],
            [
                'value' => 'T-shirt confortable en coton, parfait pour tous les jours',
                'is_verified' => true,
                'source' => 'manual',
            ],
        );

        // German
        Translation::firstOrCreate(
            [
                'translatable_type' => Product::class,
                'translatable_id' => $tshirt->id,
                'locale' => 'de_DE',
                'key' => 'name',
            ],
            [
                'value' => 'Klassisches T-Shirt',
                'is_verified' => true,
                'source' => 'manual',
            ],
        );

        Translation::firstOrCreate(
            [
                'translatable_type' => Product::class,
                'translatable_id' => $tshirt->id,
                'locale' => 'de_DE',
                'key' => 'description',
            ],
            [
                'value' => 'Bequemes Baumwoll-T-Shirt, perfekt für jeden Tag',
                'is_verified' => true,
                'source' => 'manual',
            ],
        );

        // Spanish
        Translation::firstOrCreate(
            [
                'translatable_type' => Product::class,
                'translatable_id' => $tshirt->id,
                'locale' => 'es_ES',
                'key' => 'name',
            ],
            [
                'value' => 'Camiseta Clásica',
                'is_verified' => true,
                'source' => 'manual',
            ],
        );

        Translation::firstOrCreate(
            [
                'translatable_type' => Product::class,
                'translatable_id' => $tshirt->id,
                'locale' => 'es_ES',
                'key' => 'description',
            ],
            [
                'value' => 'Camiseta cómoda de algodón, perfecta para todos los días',
                'is_verified' => true,
                'source' => 'manual',
            ],
        );

        // ===========================================
        // SNEAKERS TRANSLATIONS
        // ===========================================

        // Italian
        Translation::firstOrCreate(
            [
                'translatable_type' => Product::class,
                'translatable_id' => $sneakers->id,
                'locale' => 'it_IT',
                'key' => 'name',
            ],
            [
                'value' => 'Scarpe Sportive',
                'is_verified' => true,
                'source' => 'manual',
            ],
        );

        Translation::firstOrCreate(
            [
                'translatable_type' => Product::class,
                'translatable_id' => $sneakers->id,
                'locale' => 'it_IT',
                'key' => 'description',
            ],
            [
                'value' => 'Scarpe sportive ad alte prestazioni per running e allenamento',
                'is_verified' => true,
                'source' => 'manual',
            ],
        );

        // French
        Translation::firstOrCreate(
            [
                'translatable_type' => Product::class,
                'translatable_id' => $sneakers->id,
                'locale' => 'fr_FR',
                'key' => 'name',
            ],
            [
                'value' => 'Baskets de Sport',
                'is_verified' => true,
                'source' => 'manual',
            ],
        );

        Translation::firstOrCreate(
            [
                'translatable_type' => Product::class,
                'translatable_id' => $sneakers->id,
                'locale' => 'fr_FR',
                'key' => 'description',
            ],
            [
                'value' => 'Baskets haute performance pour le running et l\'entraînement',
                'is_verified' => true,
                'source' => 'manual',
            ],
        );

        // German
        Translation::firstOrCreate(
            [
                'translatable_type' => Product::class,
                'translatable_id' => $sneakers->id,
                'locale' => 'de_DE',
                'key' => 'name',
            ],
            [
                'value' => 'Sport Sneakers',
                'is_verified' => true,
                'source' => 'manual',
            ],
        );

        Translation::firstOrCreate(
            [
                'translatable_type' => Product::class,
                'translatable_id' => $sneakers->id,
                'locale' => 'de_DE',
                'key' => 'description',
            ],
            [
                'value' => 'Hochleistungs-Sportschuhe für Laufen und Training',
                'is_verified' => true,
                'source' => 'manual',
            ],
        );

        // Spanish
        Translation::firstOrCreate(
            [
                'translatable_type' => Product::class,
                'translatable_id' => $sneakers->id,
                'locale' => 'es_ES',
                'key' => 'name',
            ],
            [
                'value' => 'Zapatillas Deportivas',
                'is_verified' => true,
                'source' => 'manual',
            ],
        );

        Translation::firstOrCreate(
            [
                'translatable_type' => Product::class,
                'translatable_id' => $sneakers->id,
                'locale' => 'es_ES',
                'key' => 'description',
            ],
            [
                'value' => 'Zapatillas deportivas de alto rendimiento para running y entrenamiento',
                'is_verified' => true,
                'source' => 'manual',
            ],
        );

        $this->command->info('✅ Translations seeded successfully!');
        $this->command->info('   ✓ T-Shirt: IT, FR, DE, ES');
        $this->command->info('   ✓ Sneakers: IT, FR, DE, ES');
    }
}
