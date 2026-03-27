<?php

declare(strict_types=1);

namespace Cartino\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPreference extends Model
{
    protected $fillable = [
        'user_id',
        'preference_type',
        'preference_key',
        'preference_value',
    ];

    protected $casts = [
        'preference_value' => 'array',
    ];

    public function __construct(array $attributes = [])
    {
        $this->table = 'user_preferences';
        parent::__construct($attributes);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('cartino.auth.model', 'App\\Models\\User'));
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('preference_type', $type);
    }

    public function scopeByKey($query, string $key)
    {
        return $query->where('preference_key', $key);
    }

    public function scopeByTypeAndKey($query, string $type, string $key)
    {
        return $query->where('preference_type', $type)->where('preference_key', $key);
    }

    public static function setForUser($userId, string $type, string $key, mixed $value): self
    {
        return self::updateOrCreate(
            [
                'user_id' => $userId,
                'preference_type' => $type,
                'preference_key' => $key,
            ],
            [
                'preference_value' => $value,
            ],
        );
    }

    public static function getForUser($userId, string $type, string $key, mixed $default = null): mixed
    {
        $preference = self::where('user_id', $userId)
            ->where('preference_type', $type)
            ->where('preference_key', $key)
            ->first();

        return $preference ? $preference->preference_value : $default;
    }

    public static function getAllForUser($userId, ?string $type = null): array
    {
        $query = self::where('user_id', $userId);

        if ($type) {
            $query->where('preference_type', $type);
        }

        return $query
            ->get()
            ->groupBy('preference_type')
            ->map(function ($preferences) {
                return $preferences->mapWithKeys(function (self $preference) {
                    return [$preference->preference_key => $preference->preference_value];
                });
            })
            ->toArray();
    }

    public static function deleteForUser($userId, string $type, string $key): bool
    {
        return self::where('user_id', $userId)
            ->where('preference_type', $type)
            ->where('preference_key', $key)
            ->delete() > 0;
    }

    // Predefined preference types and methods
    public static function setTableColumns($userId, string $table, array $columns): self
    {
        return self::setForUser($userId, 'table_columns', $table, $columns);
    }

    public static function getTableColumns($userId, string $table, array $default = []): array
    {
        return self::getForUser($userId, 'table_columns', $table, $default);
    }

    public static function setDashboardWidgets($userId, array $widgets): self
    {
        return self::setForUser($userId, 'dashboard_widgets', 'layout', $widgets);
    }

    public static function getDashboardWidgets($userId, array $default = []): array
    {
        return self::getForUser($userId, 'dashboard_widgets', 'layout', $default);
    }

    public static function setThemePreferences($userId, array $theme): self
    {
        return self::setForUser($userId, 'theme', 'settings', $theme);
    }

    public static function getThemePreferences($userId, array $default = []): array
    {
        return self::getForUser($userId, 'theme', 'settings', $default);
    }

    public static function setFilters($userId, string $page, array $filters): self
    {
        return self::setForUser($userId, 'filters', $page, $filters);
    }

    public static function getFilters($userId, string $page, array $default = []): array
    {
        return self::getForUser($userId, 'filters', $page, $default);
    }

    public static function setSorting($userId, string $table, array $sorting): self
    {
        return self::setForUser($userId, 'sorting', $table, $sorting);
    }

    public static function getSorting($userId, string $table, array $default = []): array
    {
        return self::getForUser($userId, 'sorting', $table, $default);
    }
}
