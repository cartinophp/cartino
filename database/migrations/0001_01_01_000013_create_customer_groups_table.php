<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->nullable()->constrained('sites')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_enabled')->default(false);
            $table->decimal('discount_percentage', 5, 2)->nullable(); // Group-wide discount
            $table->boolean('tax_exempt')->default(false);
            $table->jsonb('pricing_rules')->nullable(); // Advanced pricing rules

            // Access control
            $table->jsonb('permissions')->nullable(); // What can this group do?
            $table->jsonb('restrictions')->nullable(); // What can't this group do?

            $table->string('status')->default('active');
            $table->jsonb('data')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['site_id', 'slug']);
            $table->index(['site_id', 'status']);
            $table->index(['site_id', 'is_default']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_groups');
    }
};
