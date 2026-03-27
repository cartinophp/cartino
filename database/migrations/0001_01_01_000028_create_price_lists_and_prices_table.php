<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop old pricing tables if they exist (from older migration)
        Schema::dropIfExists('price_list_items');
        Schema::dropIfExists('price_lists');

        // Create new improved price_lists table
        Schema::create('price_lists', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->string('type')->default('standard'); // standard, promotional, wholesale, tier
            $table->integer('priority')->default(0); // higher priority = applied first
            $table->boolean('is_active')->default(true);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['code', 'is_active']);
            $table->index(['type', 'is_active']);
            $table->index(['starts_at', 'ends_at']);
        });

        Schema::create('prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('market_id')->nullable()->constrained('markets')->cascadeOnDelete();
            $table->foreignId('site_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('channel_id')->nullable()->constrained('channels')->cascadeOnDelete();
            $table->foreignId('price_list_id')->nullable()->constrained()->nullOnDelete();
            $table->string('currency', 3);
            $table->unsignedBigInteger('amount'); // Prezzo in centesimi (INT per precisione)
            $table->unsignedBigInteger('compare_at_amount')->nullable(); // Prezzo confronto (barrato)
            $table->unsignedBigInteger('cost_amount')->nullable(); // Costo per calcolo margini
            $table->boolean('tax_included')->default(false); // Il prezzo include le tasse?
            $table->decimal('tax_rate', 8, 4)->nullable(); // Tax rate % (es: 22.0000)
            $table->integer('min_quantity')->default(1);
            $table->integer('max_quantity')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('is_active')->default(true);

            // Metadata
            $table->jsonb('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // FUNDAMENTAL INDEX - query performance critica (includes market & channel)
            $table->unique(
                ['product_variant_id', 'market_id', 'site_id', 'channel_id', 'price_list_id', 'currency', 'min_quantity'],
                'prices_context_unique'
            );

            $table->index(['product_variant_id', 'site_id', 'currency', 'is_active']);
            $table->index(['market_id', 'currency', 'is_active']);
            $table->index(['channel_id', 'currency', 'is_active']);
            $table->index(['price_list_id', 'is_active']);
            $table->index(['starts_at', 'ends_at', 'is_active']);
            $table->index('amount');
            $table->index(['currency', 'site_id']);
        });

        // Price List assignment to customer groups
        Schema::create('customer_group_price_list', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('price_list_id')->constrained()->cascadeOnDelete();
            $table->integer('priority')->default(0);
            $table->timestamps();

            $table->unique(['customer_group_id', 'price_list_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_group_price_list');
        Schema::dropIfExists('prices');
        Schema::dropIfExists('price_lists');

        // Recreate old price_lists structure for rollback compatibility
        Schema::create('price_lists', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->foreignId('site_id')->nullable()->constrained('sites')->cascadeOnDelete();
            $table->foreignId('channel_id')->nullable()->constrained('channels')->cascadeOnDelete();
            $table->foreignId('customer_group_id')->nullable()->constrained('customer_groups')->cascadeOnDelete();
            $table->string('currency', 3);
            $table->enum('adjustment_type', ['percentage', 'fixed'])->nullable();
            $table->decimal('adjustment_value', 10, 4)->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->jsonb('data')->nullable();
        });

        Schema::create('price_list_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('price_list_id')->constrained('price_lists')->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->decimal('price', 15, 4);
            $table->decimal('compare_at_price', 15, 4)->nullable();
            $table->integer('min_quantity')->default(1);
            $table->timestamps();
        });
    }
};
