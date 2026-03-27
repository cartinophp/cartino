<?php

declare(strict_types=1);

namespace Cartino\Contracts\Entries;

use Carbon\Carbon;
use Cartino\Collections\Category;
use Illuminate\Support\Collection;

interface Entry
{
    /**
     * Get or set the entry ID
     *
     * @param  string|null  $id
     * @return string|$this
     */
    public function id($id = null);

    /**
     * Get or set the entry slug
     *
     * @param  string|null  $slug
     * @return string|$this
     */
    public function slug($slug = null);

    /**
     * Get or set the entry URI
     *
     * @param  string|null  $uri
     * @return string|null|$this
     */
    public function uri($uri = null);

    /**
     * Get or set the collection
     *
     * @param  mixed  $collection
     * @return Category|$this
     */
    public function collection($collection = null);

    /**
     * Get the collection handle
     *
     * @return string
     */
    public function collectionHandle();

    /**
     * Get or set the blueprint
     *
     * @param  mixed  $blueprint
     * @return mixed
     */
    public function blueprint($blueprint = null);

    /**
     * Get or set the locale
     *
     * @param  string|null  $locale
     * @return string|$this
     */
    public function locale($locale = null);

    /**
     * Get or set published status
     *
     * @param  bool|null  $published
     * @return bool|$this
     */
    public function published($published = null);

    /**
     * Get or set the date
     *
     * @param  mixed  $date
     * @return Carbon|$this
     */
    public function date($date = null);

    /**
     * Get or set data
     *
     * @param  array|null  $data
     * @return Collection|$this
     */
    public function data($data = null);

    /**
     * Get a data value
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function get($key, $default = null);

    /**
     * Set a data value
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return $this
     */
    public function set($key, $value);

    /**
     * Check if data has a key
     *
     * @param  string  $key
     * @return bool
     */
    public function has($key);

    /**
     * Save the entry
     *
     * @return $this
     */
    public function save();

    /**
     * Delete the entry
     *
     * @return void
     */
    public function delete();

    /**
     * Get the entry URL
     *
     * @return string|null
     */
    public function url();

    /**
     * Get the entry title
     *
     * @return string
     */
    public function title();

    /**
     * Convert to array
     *
     * @return array
     */
    public function toArray();
}
