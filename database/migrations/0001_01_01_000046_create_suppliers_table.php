<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('site_id')->nullable();
            $table->string('name');
            $table->string('code', 50)->nullable()->unique(); // Supplier code
            $table->string('slug')->unique();
            $table->text('description')->nullable();

            // Contact Information
            $table->string('contact_person')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('mobile', 20)->nullable();
            $table->string('fax', 20)->nullable();
            $table->string('website')->nullable();

            // Address Information
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('country_code', 2)->nullable();

            // Business Information
            $table->string('tax_number')->nullable(); // VAT/Tax ID
            $table->string('company_registration')->nullable();
            $table->decimal('credit_limit', 15, 2)->nullable();
            $table->integer('payment_terms_days')->default(30); // NET 30, etc.
            $table->string('currency', 3)->default('EUR');

            // Status and Settings
            $table->string('status')->default('active'); // active, inactive, suspended
            $table->boolean('is_preferred')->default(false);
            $table->integer('priority')->default(0); // For sourcing priority
            $table->decimal('minimum_order_amount', 15, 2)->nullable();
            $table->integer('lead_time_days')->nullable(); // Default lead time

            // Additional Information
            $table->text('notes')->nullable();
            $table->jsonb('metadata')->nullable(); // Custom fields
            $table->jsonb('certifications')->nullable(); // Quality certifications
            $table->decimal('rating', 3, 2)->nullable(); // 1.00 to 5.00

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['slug', 'site_id']);
            $table->index(['site_id', 'status']);
            $table->index(['status', 'is_preferred']);
            $table->index(['country_code', 'status']);
            $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
