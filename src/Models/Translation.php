<?php

declare(strict_types=1);

namespace Cartino\Models;

use Database\Factories\TranslationFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Translation extends Model
{
    use HasFactory;

    protected $fillable = [
        'translatable_type',
        'translatable_id',
        'locale',
        'key',
        'value',
        'is_verified',
        'source',
        'translated_by',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
    ];

    // Relations

    public function translatable(): MorphTo
    {
        return $this->morphTo();
    }

    public function translator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'translated_by');
    }

    // Scopes

    public function scopeForLocale($query, string $locale)
    {
        return $query->where('locale', $locale);
    }

    public function scopeForKey($query, string $key)
    {
        return $query->where('key', $key);
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    // Helpers

    public static function set(Model $model, string $key, string $value, string $locale, array $options = []): self
    {
        return static::updateOrCreate(
            [
                'translatable_type' => $model->getMorphClass(),
                'translatable_id' => $model->getKey(),
                'locale' => $locale,
                'key' => $key,
            ],
            array_merge([
                'value' => $value,
            ], $options),
        );
    }

    public static function get(Model $model, string $key, string $locale, $default = null): ?string
    {
        $translation = static::query()
            ->where('translatable_type', $model->getMorphClass())
            ->where('translatable_id', $model->getKey())
            ->where('locale', $locale)
            ->where('key', $key)
            ->first();

        return $translation?->value ?? $default;
    }

    public static function remove(Model $model, string $key, ?string $locale = null): int
    {
        $query = static::query()
            ->where('translatable_type', $model->getMorphClass())
            ->where('translatable_id', $model->getKey())
            ->where('key', $key);

        if ($locale) {
            $query->where('locale', $locale);
        }

        return $query->delete();
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): Factory
    {
        // Prefer package factory
        if (class_exists(\Cartino\Database\Factories\TranslationFactory::class)) {
            return \Cartino\Database\Factories\TranslationFactory::new();
        }

        // Fallback to application factory namespace
        if (class_exists(TranslationFactory::class)) {
            return TranslationFactory::new();
        }

        throw new \RuntimeException('TranslationFactory not found');
    }
}
