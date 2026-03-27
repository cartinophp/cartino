<?php

namespace Cartino\Support;

use Cartino\Models\Site;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait HasSite
{
    /**
     * Boot the trait
     */
    protected static function bootHasSite()
    {
        // Auto-assign current site when creating records
        static::creating(function (Model $model) {
            if (empty($model->site_id) && ($currentSite = static::getCurrentSite())) {
                $model->site_id = $currentSite->id;
            }
        });

        // Apply site scope by default
        static::addGlobalScope('site', function (Builder $builder) {
            if ($currentSite = static::getCurrentSite()) {
                $builder->where(function ($query) use ($currentSite) {
                    $query->where('site_id', $currentSite->id)->orWhereNull('site_id'); // Allow global records
                });
            }
        });
    }

    /**
     * Get current site from context
     */
    protected static function getCurrentSite()
    {
        // Try to get site from request context, session, or default
        if (request()->has('site')) {
            return Site::where('handle', request('site'))->first();
        }

        if (session()->has('current_site_id')) {
            return Site::find(session('current_site_id'));
        }

        // Return default site
        return Site::where('is_enabled', true)->orderBy('order')->first();
    }

    /**
     * Relationship with site
     */
    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Scope to specific site
     */
    public function scopeForSite(Builder $query, $site)
    {
        if (is_string($site)) {
            return $query->whereHas('site', fn ($q) => $q->where('handle', $site));
        }

        if (is_numeric($site)) {
            return $query->where('site_id', $site);
        }

        if ($site instanceof Site) {
            return $query->where('site_id', $site->id);
        }

        return $query;
    }

    /**
     * Scope to global records (no site)
     */
    public function scopeGlobal(Builder $query)
    {
        return $query->whereNull('site_id');
    }

    /**
     * Scope without site restrictions
     */
    public function scopeWithoutSiteScope(Builder $query)
    {
        return $query->withoutGlobalScope('site');
    }

    /**
     * Get all sites this record belongs to
     */
    public function getSitesAttribute()
    {
        if ($this->site_id) {
            return collect([$this->site]);
        }

        // Global records belong to all sites
        return Site::where('is_enabled', true)->get();
    }

    /**
     * Check if record is available for given site
     */
    public function isAvailableForSite($site): bool
    {
        if (is_null($this->site_id)) {
            return true; // Global record
        }

        if (is_string($site)) {
            return $this->site && $this->site->handle === $site;
        }

        if (is_numeric($site)) {
            return $this->site_id == $site;
        }

        if ($site instanceof Site) {
            return $this->site_id == $site->id;
        }

        return false;
    }
}
