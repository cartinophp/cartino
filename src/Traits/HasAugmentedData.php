<?php

declare(strict_types=1);

namespace Cartino\Traits;

trait HasAugmentedData
{
    protected $augmentedData = [];

    /**
     * Set augmented data
     *
     * @return $this
     */
    public function augment(array $data)
    {
        $this->augmentedData = array_merge($this->augmentedData, $data);

        return $this;
    }

    /**
     * Get all augmented data
     *
     * @return array
     */
    public function augmentedData()
    {
        return $this->augmentedData;
    }

    /**
     * Get an augmented value
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function getAugmentedValue($key, $default = null)
    {
        return $this->augmentedData[$key] ?? $default;
    }

    /**
     * Check if augmented data has a key
     *
     * @param  string  $key
     * @return bool
     */
    public function hasAugmentedValue($key)
    {
        return isset($this->augmentedData[$key]);
    }

    /**
     * Clear all augmented data
     *
     * @return $this
     */
    public function clearAugmentedData()
    {
        $this->augmentedData = [];

        return $this;
    }
}
