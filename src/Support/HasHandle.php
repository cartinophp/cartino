<?php

namespace Cartino\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait HasHandle
{
    /**
     * Get the route key name for Laravel model route binding.
     */
    public function getRouteKeyName(): string
    {
        return 'handle';
    }

    /**
     * Get the route key value for Laravel model route binding.
     */
    public function getRouteKey()
    {
        // Return slug if exists, otherwise return ID
        return $this->slug ?? $this->getKey();
    }

    /**
     * Retrieve the model for a bound value.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        // Try to find by slug first, then by ID
        return $this->where('slug', $value)->orWhere($this->getKeyName(), $value)->first() ?? abort(404);
    }

    /**
     * Generate URL-friendly handle from name/title
     */
    public function generateHandle(?string $value = null): string
    {
        $source = $value ?? $this->name ?? $this->title ?? '';
        $handle = Str::slug($source);

        // Ensure uniqueness
        $original = $handle;
        $count = 1;

        while ($this->where('slug', $handle)->where('id', '!=', $this->id ?? 0)->exists()) {
            $handle = $original.'-'.$count++;
        }

        return $handle;
    }

    /**
     * Boot the trait
     */
    protected static function bootHasHandle()
    {
        static::creating(function (Model $model) {
            if (empty($model->slug) && ($model->name || $model->title)) {
                $model->slug = $model->generateHandle();
            }
        });

        static::updating(function (Model $model) {
            if ($model->isDirty(['name', 'title']) && empty($model->slug)) {
                $model->slug = $model->generateHandle();
            }
        });
    }
}
