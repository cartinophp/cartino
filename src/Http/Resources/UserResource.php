<?php

namespace Cartino\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'avatar' => $this->avatar,
            'email_verified_at' => $this->email_verified_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            // // Permissions and roles (if using Spatie Permission)
            // 'permissions' => $this->when(
            //     method_exists($this->resource, 'getAllPermissions'),
            //     fn() => $this->resource->getAllPermissions()->pluck('name')
            // ),
            // 'roles' => $this->when(
            //     method_exists($this->resource, 'getRoleNames'),
            //     fn() => $this->resource->getRoleNames()
            // ),

            // Control panel access
            'can_access_cp' => true,
            // Additional user meta
            'meta' => [
                'is_admin' => $this->is_admin ?? false,
                'last_login_at' => $this->last_login_at?->toISOString(),
                'timezone' => $this->timezone ?? config('app.timezone'),
                'locale' => $this->locale ?? config('app.locale'),
            ],
        ];
    }

    /**
     * Check if user can access control panel.
     */
    protected function canAccessControlPanel(): bool
    {
        // Check if user has CP access permission or role
        if (method_exists($this->resource, 'can')) {
            if ($this->resource->can('access-cp')) {
                return true;
            }
        }

        // Check if user has roles (Spatie Permission)
        if (method_exists($this->resource, 'hasRole')) {
            if ($this->resource->hasRole('admin') || $this->resource->hasRole('super-admin')) {
                return true;
            }
        }

        // Check if user has specific field
        if (isset($this->resource->can_access_cp)) {
            return (bool) $this->resource->can_access_cp;
        }

        // Check if user is admin type
        if (isset($this->resource->is_admin)) {
            return (bool) $this->resource->is_admin;
        }

        // Default: allow all authenticated users for now
        return true;
    }
}
