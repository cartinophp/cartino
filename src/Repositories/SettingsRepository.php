<?php

namespace Cartino\Repositories;

use Cartino\Models\Setting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class SettingsRepository extends BaseRepository
{
    protected string $cachePrefix = 'settings';

    protected function makeModel(): Model
    {
        return new Setting;
    }

    /**
     * Get all settings grouped by group
     */
    public function getAllGrouped(): Collection
    {
        $cacheKey = $this->getCacheKey('all_grouped', 'all');

        return Cache::remember($cacheKey, $this->cacheTtl, function () {
            return $this->model
                ->orderBy('group')
                ->orderBy('key')
                ->get()
                ->groupBy('group');
        });
    }

    /**
     * Get settings by group
     */
    public function getByGroup(string $group): Collection
    {
        $cacheKey = $this->getCacheKey('group', $group);

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($group) {
            return $this->model
                ->where('group', $group)
                ->orderBy('key')
                ->get();
        });
    }

    /**
     * Get setting value by key
     */
    public function getValue(string $key, $default = null)
    {
        $cacheKey = $this->getCacheKey('value', $key);

        $setting = Cache::remember($cacheKey, $this->cacheTtl, function () use ($key) {
            return $this->model->where('key', $key)->first();
        });

        if (! $setting) {
            return $default;
        }

        return $this->castValue($setting->value, $setting->type);
    }

    /**
     * Set setting value
     */
    public function setValue(
        string $key,
        $value,
        string $type = 'string',
        ?string $group = null,
        ?string $description = null,
    ): Setting {
        $this->clearCache();

        return $this->model->updateOrCreate(
            ['key' => $key],
            [
                'value' => $this->prepareValue($value, $type),
                'type' => $type,
                'group' => $group,
                'description' => $description,
            ],
        );
    }

    /**
     * Set multiple settings at once
     */
    public function setMultiple(array $settings): void
    {
        $this->clearCache();

        foreach ($settings as $key => $data) {
            if (is_array($data)) {
                $this->setValue(
                    $key,
                    $data['value'],
                    $data['type'] ?? 'string',
                    $data['group'] ?? null,
                    $data['description'] ?? null,
                );
            } else {
                $this->setValue($key, $data);
            }
        }
    }

    /**
     * Delete setting by key
     */
    public function deleteByKey(string $key): bool
    {
        $this->clearCache();

        return $this->model->where('key', $key)->delete() > 0;
    }

    /**
     * Get settings for a specific page (like general, checkout, etc.)
     */
    public function getSettingsForPage(string $page): Collection
    {
        $groupMappings = [
            'general' => ['general', 'store', 'contact'],
            'checkout' => ['checkout', 'payment', 'order'],
            'shipping' => ['shipping'],
            'taxes' => ['taxes', 'tax'],
            'notifications' => ['notifications', 'email', 'sms'],
            'advanced' => ['advanced', 'api', 'integration'],
        ];

        $groups = $groupMappings[$page] ?? [$page];

        return $this->model
            ->whereIn('group', $groups)
            ->orderBy('group')
            ->orderBy('key')
            ->get();
    }

    /**
     * Prepare value for storage
     */
    protected function prepareValue($value, string $type): string
    {
        return match ($type) {
            'boolean' => $value ? '1' : '0',
            'array', 'json' => json_encode($value),
            'integer' => (string) ((int) $value),
            'float' => (string) ((float) $value),
            default => (string) $value,
        };
    }

    /**
     * Cast value from storage
     */
    protected function castValue(string $value, string $type)
    {
        return match ($type) {
            'boolean' => $value === '1',
            'array', 'json' => json_decode($value, true),
            'integer' => (int) $value,
            'float' => (float) $value,
            default => $value,
        };
    }

    /**
     * Clear repository cache
     */
    protected function clearCache(): void
    {
        $tags = [$this->cachePrefix];
        Cache::tags($tags)->flush();
    }
}
