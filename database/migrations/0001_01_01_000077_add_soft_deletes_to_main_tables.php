<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Aggiungiamo soft deletes alle tabelle principali che ne hanno bisogno

        // Customers
        Schema::table('customers', function (Blueprint $table) {
            if (! Schema::hasColumn('customers', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        // Brands
        Schema::table('brands', function (Blueprint $table) {
            if (! Schema::hasColumn('brands', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        // Product Types
        if (Schema::hasTable('product_types')) {
            Schema::table('product_types', function (Blueprint $table) {
                if (! Schema::hasColumn('product_types', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }

        // Orders
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (! Schema::hasColumn('orders', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }

        // Coupons
        if (Schema::hasTable('coupons')) {
            Schema::table('coupons', function (Blueprint $table) {
                if (! Schema::hasColumn('coupons', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }

        // Gift Cards
        Schema::table('gift_cards', function (Blueprint $table) {
            if (! Schema::hasColumn('gift_cards', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        // Wishlists
        Schema::table('wishlists', function (Blueprint $table) {
            if (! Schema::hasColumn('wishlists', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        // Carts
        if (Schema::hasTable('carts')) {
            Schema::table('carts', function (Blueprint $table) {
                if (! Schema::hasColumn('carts', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }

        // User groups
        if (Schema::hasTable('user_groups')) {
            Schema::table('user_groups', function (Blueprint $table) {
                if (! Schema::hasColumn('user_groups', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }

        // Menus
        if (Schema::hasTable('menus')) {
            Schema::table('menus', function (Blueprint $table) {
                if (! Schema::hasColumn('menus', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }

        // Menu Items
        Schema::table('menu_items', function (Blueprint $table) {
            if (! Schema::hasColumn('menu_items', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        $tables = [
            'customers', 'brands', 'collections', 'product_types',
            'orders', 'coupons', 'gift_cards', 'wishlists',
            'carts', 'user_groups', 'menus', 'menu_items',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->dropSoftDeletes();
                });
            }
        }
    }
};
