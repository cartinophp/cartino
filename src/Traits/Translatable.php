<?php

declare(strict_types=1);

namespace Cartino\Traits;

use Cartino\Models\Translation;
use Cartino\Services\LocaleResolver;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;

trait Translatable
{
    /**
     * Boot the trait.
     */
    public static function bootTranslatable(): void
    {
        static::deleting(function ($model) {
            if (method_exists($model, 'isForceDeleting') && ! $model->isForceDeleting()) {
                return;
            }

            $model->translations()->delete();
        });
    }

    /**
     * Get all translations for this model.
     */
    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'translatable');
    }

    /**
     * Get translations for a specific locale.
     */
    public function translationsForLocale(string $locale): MorphMany
    {
        return $this->translations()->where('locale', $locale);
    }

    /**
     * Get a translated value for a specific field and locale.
     * Uses fallback chain if translation is not found.
     */
    public function translate(string $key, ?string $locale = null, bool $useFallback = true): ?string
    {
        $locale = $locale ?? app()->getLocale();

        // Try exact locale
        $translation = $this->getTranslation($key, $locale);

        if ($translation !== null) {
            return $translation;
        }

        // Use fallback chain if enabled
        if ($useFallback) {
            $localeResolver = app(LocaleResolver::class);
            $fallbackLocales = $localeResolver->getFallbackChain($locale);

            foreach ($fallbackLocales as $fallbackLocale) {
                $translation = $this->getTranslation($key, $fallbackLocale);

                if ($translation !== null) {
                    return $translation;
                }
            }
        }

        // Return original value if no translation found
        return $this->getAttribute($key);
    }

    /**
     * Get translation without fallback.
     */
    public function getTranslation(string $key, string $locale): ?string
    {
        return $this->translations()
            ->where('key', $key)
            ->where('locale', $locale)
            ->value('value');
    }

    /**
     * Set a translation for a specific field and locale.
     */
    public function setTranslation(string $key, string $value, string $locale, array $options = []): Translation
    {
        return Translation::set($this, $key, $value, $locale, $options);
    }

    /**
     * Set multiple translations at once.
     *
     * @param  array  $translations  ['locale' => ['key' => 'value']]
     */
    public function setTranslations(array $translations): void
    {
        foreach ($translations as $locale => $fields) {
            foreach ($fields as $key => $value) {
                $this->setTranslation($key, $value, $locale);
            }
        }
    }

    /**
     * Remove translation for a specific field and locale.
     */
    public function removeTranslation(string $key, ?string $locale = null): int
    {
        return Translation::remove($this, $key, $locale);
    }

    /**
     * Get all translations grouped by locale.
     *
     * @return Collection ['locale' => ['key' => 'value']]
     */
    public function getAllTranslations(): Collection
    {
        return $this->translations()
            ->get()
            ->groupBy('locale')
            ->map(function ($translations) {
                return $translations->pluck('value', 'key');
            });
    }

    /**
     * Check if translation exists for a specific field and locale.
     */
    public function hasTranslation(string $key, string $locale): bool
    {
        return $this->translations()
            ->where('key', $key)
            ->where('locale', $locale)
            ->exists();
    }

    /**
     * Get available locales for this model.
     */
    public function getAvailableLocales(): Collection
    {
        return $this->translations()->distinct('locale')->pluck('locale');
    }

    /**
     * Check if model is fully translated for a specific locale.
     *
     * @param  array  $requiredFields  Fields that must be translated
     */
    public function isFullyTranslated(string $locale, array $requiredFields = []): bool
    {
        if (empty($requiredFields)) {
            $requiredFields = $this->getTranslatableAttributes();
        }

        foreach ($requiredFields as $field) {
            if (! $this->hasTranslation($field, $locale)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get translatable attributes for this model.
     * Override this method in your model to define which fields are translatable.
     */
    public function getTranslatableAttributes(): array
    {
        return $this->translatable ?? [];
    }

    /**
     * Magic getter for translated attributes.
     * Usage: $product->name_it or $product->{'name:it'}
     */
    public function getAttribute($key)
    {
        // Check for locale suffix pattern: field_locale (e.g., name_it)
        if (str_contains($key, '_') && strlen($key) > 3) {
            $parts = explode('_', $key);
            $locale = array_pop($parts);
            $field = implode('_', $parts);

            if (in_array($field, $this->getTranslatableAttributes()) && strlen($locale) === 2) {
                return $this->translate($field, $locale, false);
            }
        }

        // Check for colon pattern: field:locale (e.g., name:it)
        if (str_contains($key, ':')) {
            [$field, $locale] = explode(':', $key, 2);

            if (in_array($field, $this->getTranslatableAttributes())) {
                return $this->translate($field, $locale, false);
            }
        }

        return parent::getAttribute($key);
    }
}
