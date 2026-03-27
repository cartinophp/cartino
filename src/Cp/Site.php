<?php

namespace Cartino\Cp;

class Site
{
    protected $handle;

    protected $name;

    protected $url;

    protected $locale;

    protected $attributes = [];

    public function __construct($handle, $attributes = [])
    {
        $this->handle = $handle;
        $this->attributes = $attributes;
        $this->name = $attributes['name'] ?? $handle;
        $this->url = $attributes['url'] ?? null;
        $this->locale = $attributes['locale'] ?? 'en';
    }

    public static function make($handle, $attributes = [])
    {
        return new static($handle, $attributes);
    }

    public function handle()
    {
        return $this->handle;
    }

    public function name()
    {
        return $this->name;
    }

    public function url()
    {
        return $this->url;
    }

    public function locale()
    {
        return $this->locale;
    }

    public function get($key, $default = null)
    {
        return $this->attributes[$key] ?? $default;
    }

    public function set($key, $value)
    {
        $this->attributes[$key] = $value;

        return $this;
    }

    public function toArray()
    {
        return [
            'handle' => $this->handle,
            'name' => $this->name,
            'url' => $this->url,
            'locale' => $this->locale,
            'attributes' => $this->attributes,
        ];
    }
}

class Sites
{
    protected static $sites = [];

    protected static $default = 'default';

    protected static $current = null;

    public static function make()
    {
        return new static;
    }

    public static function setSites($sites)
    {
        static::$sites = [];

        foreach ($sites as $handle => $config) {
            static::$sites[$handle] = Site::make($handle, $config);
        }
    }

    public static function all()
    {
        return static::$sites;
    }

    public static function get($handle)
    {
        return static::$sites[$handle] ?? null;
    }

    public static function has($handle)
    {
        return isset(static::$sites[$handle]);
    }

    public static function current()
    {
        if (static::$current) {
            return static::$current;
        }

        return static::default();
    }

    public static function setCurrent($site)
    {
        if (is_string($site)) {
            $site = static::get($site);
        }

        static::$current = $site;
    }

    public static function default()
    {
        return static::get(static::$default);
    }

    public static function setDefault($handle)
    {
        static::$default = $handle;
    }

    public static function count()
    {
        return count(static::$sites);
    }

    public static function handles()
    {
        return array_keys(static::$sites);
    }

    public static function isMultisite()
    {
        return static::count() > 1;
    }

    public static function findByUrl($url)
    {
        foreach (static::$sites as $site) {
            if ($site->url() === $url) {
                return $site;
            }
        }

        return null;
    }

    public static function config()
    {
        return [
            'sites' => array_map(fn ($site) => $site->toArray(), static::$sites),
            'default' => static::$default,
            'current' => static::current()?->handle(),
            'multisite' => static::isMultisite(),
        ];
    }

    public static function boot()
    {
        // Default configuration
        static::setSites([
            'default' => [
                'name' => 'Default Site',
                'url' => '/',
                'locale' => 'en',
            ],
        ]);
    }
}
