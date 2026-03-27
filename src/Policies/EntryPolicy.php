<?php

declare(strict_types=1);

namespace Cartino\Policies;

use Cartino\Models\Entry;
use Cartino\Models\User;

class EntryPolicy
{
    /**
     * Perform pre-authorization checks.
     * Super admins can do anything.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->is_super ?? false) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view any entries.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view entries');
    }

    /**
     * Determine whether the user can view the entry.
     */
    public function view(User $user, Entry $entry): bool
    {
        // Published entries can be viewed by anyone with view permission
        if ($entry->isPublished()) {
            return $user->can('view entries');
        }

        // Drafts can only be viewed by editors or the author
        return $user->can('edit entries') || $entry->author_id === $user->id;
    }

    /**
     * Determine whether the user can create entries.
     */
    public function create(User $user): bool
    {
        return $user->can('create entries');
    }

    /**
     * Determine whether the user can update the entry.
     */
    public function update(User $user, Entry $entry): bool
    {
        // Can edit if has permission, or is the author
        return $user->can('edit entries') || $entry->author_id === $user->id;
    }

    /**
     * Determine whether the user can delete the entry.
     */
    public function delete(User $user, Entry $entry): bool
    {
        return $user->can('delete entries');
    }

    /**
     * Determine whether the user can restore the entry.
     */
    public function restore(User $user, Entry $entry): bool
    {
        return $user->can('restore entries');
    }

    /**
     * Determine whether the user can permanently delete the entry.
     */
    public function forceDelete(User $user, Entry $entry): bool
    {
        return $user->can('force delete entries');
    }

    /**
     * Determine whether the user can publish the entry.
     */
    public function publish(User $user, Entry $entry): bool
    {
        return $user->can('publish entries');
    }

    /**
     * Determine whether the user can unpublish the entry.
     */
    public function unpublish(User $user, Entry $entry): bool
    {
        return $user->can('publish entries');
    }

    /**
     * Determine whether the user can schedule the entry.
     */
    public function schedule(User $user, Entry $entry): bool
    {
        return $user->can('publish entries');
    }

    /**
     * Determine whether the user can duplicate the entry.
     */
    public function duplicate(User $user, Entry $entry): bool
    {
        return $user->can('create entries');
    }

    /**
     * Determine whether the user can reorder entries.
     */
    public function reorder(User $user): bool
    {
        return $user->can('edit entries');
    }
}
