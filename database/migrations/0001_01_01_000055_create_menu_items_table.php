<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained('menus')->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('menu_items')->onDelete('cascade');
            $table->string('title');
            $table->string('url')->nullable();
            $table->string('type')->default('link'); // link, collection, entry, external
            $table->string('reference_type')->nullable(); // App\Models\Product, etc.
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->json('data')->nullable(); // custom fields, attributes
            $table->string('status')->default('active'); // active, inactive, draft
            $table->boolean('opens_in_new_window')->default(false);
            $table->string('css_class')->nullable();
            $table->integer('sort_order')->default(0);
            $table->integer('depth')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['menu_id', 'parent_id', 'sort_order']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('menu_items');
    }
};
