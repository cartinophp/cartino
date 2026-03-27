<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Default variant - riferimento semplice senza foreign key per evitare circular dependency
            $table->unsignedBigInteger('default_variant_id')->nullable()->after('id');

            // Conteggi per ottimizzazione query
            $table->integer('variants_count')->default(0)->after('published_scope');
            $table->integer('images_count')->default(0)->after('variants_count');

            // Price range per display (calcolato dalle varianti)
            $table->decimal('price_min', 15, 2)->nullable()->after('images_count');
            $table->decimal('price_max', 15, 2)->nullable()->after('price_min');

            // Indici per performance
            $table->index(['default_variant_id', 'status']);
            $table->index(['price_min', 'price_max']);
            $table->index(['variants_count', 'images_count']);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'default_variant_id',
                'variants_count',
                'images_count',
                'price_min',
                'price_max',
            ]);
        });
    }
};
