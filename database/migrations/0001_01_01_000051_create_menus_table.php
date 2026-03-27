<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->nullable()->constrained('sites')->cascadeOnDelete();
            $table->string('handle');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('location')->nullable(); // header, footer, sidebar, etc.
            $table->jsonb('settings')->nullable();
            $table->jsonb('data')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->unique(['site_id', 'handle']);
            $table->index(['site_id', 'is_active']);
            $table->index(['location', 'is_active']);
            $table->index(['site_id', 'location', 'is_active']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('menus');
    }
};
