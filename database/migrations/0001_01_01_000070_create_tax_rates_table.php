<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_rates', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // VAT, Sales Tax, GST, etc.
            $table->string('code')->unique(); // IT_VAT, US_SALES, etc.
            $table->decimal('rate', 5, 4); // 0.2200 for 22%
            $table->enum('type', ['percentage', 'fixed'])->default('percentage');
            $table->boolean('is_compound')->default(false); // Tax on tax
            $table->boolean('is_inclusive')->default(false); // Included in price
            $table->json('countries')->nullable(); // Array of country codes
            $table->json('states')->nullable(); // Array of state codes
            $table->json('postcodes')->nullable(); // Array of postcode patterns
            $table->json('product_collections')->nullable(); // Collections this applies to
            $table->decimal('min_amount', 10, 2)->nullable(); // Minimum order amount
            $table->decimal('max_amount', 10, 2)->nullable(); // Maximum order amount
            $table->string('status')->default('active'); // active, inactive, draft
            $table->date('effective_from')->nullable();
            $table->date('effective_until')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'effective_from', 'effective_until']);
            $table->index(['code', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_rates');
    }
};
