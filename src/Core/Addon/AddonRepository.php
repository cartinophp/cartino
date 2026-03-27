<?php

declare(strict_types=1);

namespace Cartino\Core\Addon;

use Illuminate\Support\Facades\DB;

class AddonRepository
{
    protected string $table = 'addons';

    /**
     * Check if addon exists
     */
    public function exists(string $id): bool
    {
        return DB::table($this->table)->where('id', $id)->exists();
    }

    /**
     * Check if addon is active
     */
    public function isActive(string $id): bool
    {
        return DB::table($this->table)
            ->where('id', $id)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Get addon version
     */
    public function getVersion(string $id): ?string
    {
        return DB::table($this->table)->where('id', $id)->value('version');
    }

    /**
     * Create addon record
     */
    public function create(array $data): void
    {
        DB::table($this->table)->insert(array_merge($data, [
            'created_at' => now(),
            'updated_at' => now(),
        ]));
    }

    /**
     * Delete addon record
     */
    public function delete(string $id): void
    {
        DB::table($this->table)->where('id', $id)->delete();
    }

    /**
     * Activate plugin
     */
    public function activate(string $id): void
    {
        DB::table($this->table)
            ->where('id', $id)
            ->update([
                'is_active' => true,
                'updated_at' => now(),
            ]);
    }

    /**
     * Deactivate plugin
     */
    public function deactivate(string $id): void
    {
        DB::table($this->table)
            ->where('id', $id)
            ->update([
                'is_active' => false,
                'updated_at' => now(),
            ]);
    }

    /**
     * Update addon version
     */
    public function updateVersion(string $id, string $version): void
    {
        DB::table($this->table)
            ->where('id', $id)
            ->update([
                'version' => $version,
                'updated_at' => now(),
            ]);
    }

    /**
     * Get all addons
     */
    public function all(): array
    {
        return DB::table($this->table)
            ->orderBy('name')
            ->get()
            ->toArray();
    }
}
