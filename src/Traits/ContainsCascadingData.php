<?php

declare(strict_types=1);

namespace Cartino\Traits;

trait ContainsCascadingData
{
    protected $cascade = [];

    /**
     * Set cascading data
     *
     * @return $this
     */
    public function cascade(array $data)
    {
        $this->cascade = $data;

        return $this;
    }

    /**
     * Get cascading data
     *
     * @return array
     */
    public function getCascade()
    {
        return $this->cascade;
    }

    /**
     * Get a value from cascading data
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function getCascadeValue($key, $default = null)
    {
        return $this->cascade[$key] ?? $default;
    }

    /**
     * Check if cascading data has a key
     *
     * @param  string  $key
     * @return bool
     */
    public function hasCascadeValue($key)
    {
        return isset($this->cascade[$key]);
    }
}
