<?php

declare(strict_types=1);

namespace Cartino\Contracts\Collections;

interface Category
{
    /**
     * Get or set the collection handle
     */
    public function handle($handle = null);

    /**
     * Get or set the collection title
     */
    public function title($title = null);

    /**
     * Get or set routes
     */
    public function routes($routes = null);

    /**
     * Get route for a specific site
     */
    public function route($site = null);

    /**
     * Get entry blueprints
     */
    public function entryBlueprints();

    /**
     * Check if collection is dated
     */
    public function dated();

    /**
     * Save the collection
     */
    public function save();

    /**
     * Delete the collection
     */
    public function delete();

    /**
     * Convert to array
     */
    public function toArray();
}
