<?php

declare(strict_types=1);

namespace Cartino\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Vocabulary Model
 *
 * Manages translatable vocabularies for the entire system.
 * Used for: order statuses, payment statuses, shipping statuses, etc.
 *
 * @property int $id
 * @property string $group
 * @property string $code
 * @property array $labels
 * @property int $sort_order
 * @property array|null $meta
 * @property bool $is_system
 * @property bool $is_active
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Vocabulary extends Model
{
    protected $fillable = [
        'group',
        'code',
        'labels',
        'sort_order',
        'meta',
        'is_system',
        'is_active',
    ];

    protected $casts = [
        'labels' => 'array',
        'meta' => 'array',
        'is_system' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        // Clear cache when vocabulary is updated
        static::saved(function () {
            Cache::tags(['vocabularies'])->flush();
        });

        static::deleted(function () {
            Cache::tags(['vocabularies'])->flush();
        });
    }

    /**
     * Scope: filter by group.
     */
    public function scopeGroup(Builder $query, string $group): Builder
    {
        return $query->where('group', $group);
    }

    /**
     * Scope: only active vocabularies.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: ordered by sort_order.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('code');
    }

    /**
     * Get the label for the current locale.
     */
    public function getLabel(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();

        return $this->labels[$locale] ?? $this->labels['en'] ?? $this->code;
    }

    /**
     * Get a meta value.
     */
    public function getMeta(string $key, mixed $default = null): mixed
    {
        return $this->meta[$key] ?? $default;
    }

    /**
     * Get the color meta value.
     */
    public function getColor(): ?string
    {
        return $this->getMeta('color');
    }

    /**
     * Check if this is a final status.
     */
    public function isFinal(): bool
    {
        return (bool) $this->getMeta('is_final', false);
    }

    /**
     * Get allowed transitions.
     */
    public function getAllowedTransitions(): array
    {
        return $this->getMeta('allowed_transitions', []);
    }

    /**
     * Check if transition to another status is allowed.
     */
    public function canTransitionTo(string $code): bool
    {
        $allowed = $this->getAllowedTransitions();

        return empty($allowed) || in_array($code, $allowed);
    }

    /**
     * Get vocabularies for a group as select options.
     *
     * @return array<int, array{value: string, label: string, color: string|null, meta: array|null}>
     */
    public static function getSelectOptions(string $group, ?string $locale = null): array
    {
        $locale = $locale ?? app()->getLocale();
        $cacheKey = "vocabularies.select.{$group}.{$locale}";

        return Cache::tags(['vocabularies'])->remember($cacheKey, 3600, function () use ($group, $locale) {
            return static::query()
                ->group($group)
                ->active()
                ->ordered()
                ->get()
                ->map(fn (Vocabulary $v) => [
                    'value' => $v->code,
                    'label' => $v->getLabel($locale),
                    'color' => $v->getColor(),
                    'meta' => $v->meta,
                ])
                ->toArray();
        });
    }

    /**
     * Get vocabularies for a group as simple key-value pairs.
     *
     * @return array<string, string>
     */
    public static function getOptions(string $group, ?string $locale = null): array
    {
        $locale = $locale ?? app()->getLocale();
        $cacheKey = "vocabularies.options.{$group}.{$locale}";

        return Cache::tags(['vocabularies'])->remember($cacheKey, 3600, function () use ($group, $locale) {
            return static::query()
                ->group($group)
                ->active()
                ->ordered()
                ->get()
                ->pluck(fn (Vocabulary $v) => $v->getLabel($locale), 'code')
                ->toArray();
        });
    }

    /**
     * Get all vocabularies for multiple groups (for Inertia).
     *
     * @param  array  $groups  List of groups to fetch
     * @return array<string, array>
     */
    public static function getMultipleGroups(array $groups, ?string $locale = null): array
    {
        $locale = $locale ?? app()->getLocale();
        $result = [];

        foreach ($groups as $group) {
            $result[$group] = static::getSelectOptions($group, $locale);
        }

        return $result;
    }

    /**
     * Find a vocabulary by group and code.
     */
    public static function findByGroupAndCode(string $group, string $code): ?static
    {
        return static::query()
            ->where('group', $group)
            ->where('code', $code)
            ->first();
    }

    /**
     * Create or update a vocabulary.
     */
    public static function createOrUpdate(string $group, string $code, array $data): static
    {
        return static::updateOrCreate(
            ['group' => $group, 'code' => $code],
            $data
        );
    }
}
