<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $addProvider = ! Schema::hasColumn('users', 'provider');
        $addProviderId = ! Schema::hasColumn('users', 'provider_id');
        $addProviderToken = ! Schema::hasColumn('users', 'provider_token');
        $addProviderRefreshToken = ! Schema::hasColumn('users', 'provider_refresh_token');
        $addAvatar = ! Schema::hasColumn('users', 'avatar');

        $providerWillExist = ! $addProvider ? true : true;
        $providerIdWillExist = ! $addProviderId ? true : true;
        $shouldAddIndex = ($addProvider || $addProviderId) && $providerWillExist && $providerIdWillExist;

        Schema::table('users', function (Blueprint $table) use (
            $addProvider,
            $addProviderId,
            $addProviderToken,
            $addProviderRefreshToken,
            $addAvatar,
            $shouldAddIndex
        ) {
            // Keep this migration compatible with projects that already extended the users table.
            // Also avoid `change()` calls to not require doctrine/dbal.

            if ($addProvider) {
                $table->string('provider')->nullable()->after('email');
            }

            if ($addProviderId) {
                $table->string('provider_id')->nullable()->after('provider');
            }

            if ($addProviderToken) {
                $table->text('provider_token')->nullable()->after('provider_id');
            }

            if ($addProviderRefreshToken) {
                $table->text('provider_refresh_token')->nullable()->after('provider_token');
            }

            if ($addAvatar) {
                $table->string('avatar')->nullable()->after('provider_refresh_token');
            }

            if ($shouldAddIndex) {
                $table->index(['provider', 'provider_id'], 'users_provider_provider_id_idx');
            }
        });

        // Create user_social_accounts table for multiple provider support per user
        Schema::create('user_social_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('provider'); // google, facebook, twitter, etc.
            $table->string('provider_id');
            $table->text('provider_token')->nullable();
            $table->text('provider_refresh_token')->nullable();
            $table->json('provider_data')->nullable(); // Store additional provider data
            $table->timestamps();

            $table->unique(['provider', 'provider_id']);
            $table->index(['user_id', 'provider']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Best-effort rollback (avoid requiring doctrine/dbal)
            if (Schema::hasColumn('users', 'provider') && Schema::hasColumn('users', 'provider_id')) {
                try {
                    $table->dropIndex('users_provider_provider_id_idx');
                } catch (Throwable) {
                    // ignore
                }
            }

            $columnsToDrop = array_values(array_filter([
                Schema::hasColumn('users', 'provider') ? 'provider' : null,
                Schema::hasColumn('users', 'provider_id') ? 'provider_id' : null,
                Schema::hasColumn('users', 'provider_token') ? 'provider_token' : null,
                Schema::hasColumn('users', 'provider_refresh_token') ? 'provider_refresh_token' : null,
                Schema::hasColumn('users', 'avatar') ? 'avatar' : null,
            ]));

            if (! empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });

        Schema::dropIfExists('user_social_accounts');
    }
};
