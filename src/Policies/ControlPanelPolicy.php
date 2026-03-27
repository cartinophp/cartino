<?php

namespace Cartino\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Gate;

class ControlPanelPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can access the control panel.
     */
    public function access(Authenticatable $user): bool
    {
        return $this->userCan($user, 'access-cp');
    }

    /**
     * Determine whether the user can view the dashboard.
     */
    public function viewDashboard(Authenticatable $user): bool
    {
        return $this->userCan($user, 'view-dashboard');
    }

    /**
     * Determine whether the user can view analytics.
     */
    public function viewAnalytics(Authenticatable $user): bool
    {
        return $this->userCan($user, 'view-analytics');
    }

    /**
     * Determine whether the user can view reports.
     */
    public function viewReports(Authenticatable $user): bool
    {
        return $this->userCan($user, 'view-reports');
    }

    /**
     * Determine whether the user can manage settings.
     */
    public function manageSettings(Authenticatable $user): bool
    {
        return $this->userCan($user, 'view-settings') || $this->userCan($user, 'edit-settings');
    }

    /**
     * Determine whether the user can edit settings.
     */
    public function editSettings(Authenticatable $user): bool
    {
        return $this->userCan($user, 'edit-settings');
    }

    /**
     * Determine whether the user can manage users.
     */
    public function manageUsers(Authenticatable $user): bool
    {
        return
            $this->userCan($user, 'view-users') ||
            $this->userCan($user, 'create-users') ||
            $this->userCan($user, 'edit-users');
    }

    /**
     * Determine whether the user can manage roles and permissions.
     */
    public function manageRoles(Authenticatable $user): bool
    {
        return $this->userCan($user, 'manage-roles') || $this->userCan($user, 'manage-permissions');
    }

    /**
     * Helper method to check if user has permission.
     */
    private function userCan(Authenticatable $user, string $permission): bool
    {
        // Check if user has the method can() (like models with HasPermissions trait)
        if (method_exists($user, 'can')) {
            return $user->can($permission);
        }

        // Fallback to Gate facade
        return Gate::forUser($user)->allows($permission);
    }
}
