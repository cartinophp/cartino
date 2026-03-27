<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_locations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('site_id')->nullable();

            $table->string('name');
            $table->string('code', 50);
            $table->text('description')->nullable();

            $table->jsonb('address');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->boolean('fulfills_online_orders')->default(true);
            $table->boolean('is_legacy')->default(false);

            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('contact_person')->nullable();

            $table->jsonb('operating_hours')->nullable();
            $table->string('timezone')->default('UTC');

            $table->integer('priority')->default(0);
            $table->boolean('track_inventory')->default(true);
            $table->boolean('continues_selling_when_out_of_stock')->default(false);

            $table->timestamps();
            $table->softDeletes();

            $table->jsonb('data')->nullable();

            $table->unique(['code', 'site_id']);
            $table->index(['site_id', 'is_active']);
            $table->index(['is_default', 'is_active']);
            $table->index(['fulfills_online_orders', 'is_active']);
            $table->index(['priority', 'is_active']);

            $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade');
        });

        Schema::create('location_inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->constrained('inventory_locations')->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained('product_variants')->cascadeOnDelete();

            $table->integer('quantity')->default(0);
            $table->integer('reserved_quantity')->default(0);
            $table->integer('available_quantity')->storedAs('quantity - reserved_quantity');

            $table->integer('incoming_quantity')->default(0);
            $table->integer('committed_quantity')->default(0);
            $table->integer('damaged_quantity')->default(0);

            $table->integer('reorder_point')->nullable();
            $table->integer('reorder_quantity')->nullable();
            $table->integer('max_stock_level')->nullable();

            $table->decimal('cost_price', 15, 4)->nullable();
            $table->decimal('average_cost', 15, 4)->nullable();

            $table->timestamp('last_counted_at')->nullable();
            $table->integer('last_counted_quantity')->nullable();
            $table->timestamp('last_received_at')->nullable();
            $table->timestamp('last_sold_at')->nullable();

            $table->timestamps();

            $table->jsonb('data')->nullable()->comment('Custom fields data for inventory tracking');

            $table->unique(['location_id', 'product_variant_id']);
            $table->index(['product_variant_id', 'quantity']);
            $table->index(['location_id', 'quantity']);
            $table->index(['available_quantity']);
            $table->index(['reorder_point']);
            $table->index(['last_counted_at']);
            $table->index(['last_received_at']);
            $table->index(['last_sold_at']);

            $table->index(['location_id', 'available_quantity']);
            $table->index(['product_variant_id', 'available_quantity']);
            $table->index(['location_id', 'reorder_point', 'quantity']);
        });

        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->constrained('inventory_locations')->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained('product_variants')->cascadeOnDelete();

            $table->enum('type', [
                'sale', 'return', 'adjustment', 'transfer_in', 'transfer_out',
                'restock', 'shrinkage', 'damage', 'correction', 'initial',
            ]);

            $table->integer('quantity_change');
            $table->integer('quantity_before');
            $table->integer('quantity_after');

            $table->nullableMorphs('reference');
            $table->string('reference_number')->nullable();

            $table->decimal('unit_cost', 15, 4)->nullable();
            $table->decimal('total_cost', 15, 4)->nullable();

            $table->text('reason')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->timestamps();

            $table->index(['location_id', 'type', 'created_at']);
            $table->index(['product_variant_id', 'type', 'created_at']);
            $table->index(['type', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['type', 'quantity_change', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
        Schema::dropIfExists('location_inventories');
        Schema::dropIfExists('inventory_locations');
    }
};
