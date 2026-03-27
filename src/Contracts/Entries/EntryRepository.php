<?php

declare(strict_types=1);

namespace Cartino\Contracts\Entries;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

interface EntryRepository
{
    /**
     * Find an entry by ID
     *
     * @param  string  $id
     * @return Entry|null
     */
    public function find($id);

    /**
     * Find an entry by slug
     *
     * @param  string  $slug
     * @param  string  $collection
     * @return Entry|null
     */
    public function findBySlug($slug, $collection);

    /**
     * Get all entries in a collection
     *
     * @param  string  $collection
     * @return Collection
     */
    public function whereCollection($collection);

    /**
     * Save an entry
     *
     * @param  Entry  $entry
     * @return bool
     */
    public function save($entry);

    /**
     * Delete an entry
     *
     * @param  Entry  $entry
     * @return bool
     */
    public function delete($entry);

    /**
     * Create a new entry instance
     *
     * @return Entry
     */
    public function make();

    /**
     * Query entries
     *
     * @return Builder
     */
    public function query();
}
