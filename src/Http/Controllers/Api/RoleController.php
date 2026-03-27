<?php

namespace Cartino\Http\Controllers\Api;

use Cartino\Http\Requests\Api\AssignRoleUsersRequest;
use Cartino\Http\Requests\Api\RoleRequest;
use Cartino\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends ApiController
{
    use ApiResponseTrait;

    public function __construct()
    {
        $this->middleware(['auth:api']);
        $this->middleware('permission:view roles')->only(['index', 'show']);
        $this->middleware('permission:create roles')->only(['store']);
        $this->middleware('permission:edit roles')->only(['update']);
        $this->middleware('permission:delete roles')->only(['destroy']);
        $this->middleware('permission:assign roles')->only(['assignUsers', 'removeUsers']);
    }

    /**
     * Get all roles with structured formatting
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $roles = Role::with(['permissions', 'users'])
                ->when($request->search, function ($query, $search) {
                    $query->where('name', 'like', "%{$search}%")->orWhere('display_name', 'like', "%{$search}%");
                })
                ->orderBy('name')
                ->get()
                ->map(function ($role) {
                    return $this->formatRoleData($role);
                });

            return $this->successResponse([
                'roles' => $roles,
                'total' => $roles->count(),
                'permission_groups' => $this->getPermissionGroups(),
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Errore durante il recupero dei ruoli');
        }
    }

    /**
     * Create new role
     */
    public function store(RoleRequest $request): JsonResponse
    {
        try {
            $role = Role::create([
                'name' => $request->name,
                'guard_name' => 'api',
                'display_name' => $request->display_name,
                'description' => $request->description,
            ]);

            // Assegna permessi se specificati
            if ($request->has('permissions')) {
                $role->syncPermissions($request->permissions);
            }

            return $this->createdResponse(
                $this->formatRoleData($role->load(['permissions', 'users'])),
                'Ruolo creato con successo',
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Errore durante la creazione del ruolo');
        }
    }

    /**
     * Get specific role
     */
    public function show(string $id): JsonResponse
    {
        try {
            $role = Role::with(['permissions', 'users'])->findOrFail($id);

            return $this->successResponse([
                'role' => $this->formatRoleData($role),
                'permission_groups' => $this->getPermissionGroupsWithStatus($role),
                'available_permissions' => Permission::all(['id', 'name', 'display_name']),
            ]);
        } catch (\Exception $e) {
            return $this->notFoundResponse('Ruolo non trovato');
        }
    }

    /**
     * Update role
     */
    public function update(RoleRequest $request, string $id): JsonResponse
    {
        try {
            $role = Role::findOrFail($id);

            // Verifica che non sia il ruolo super se non si è super user
            if ($role->name === 'super' && ! $request->user()->hasRole('super')) {
                return $this->forbiddenResponse('Non puoi modificare il ruolo Super User');
            }

            $role->update([
                'display_name' => $request->display_name,
                'description' => $request->description,
            ]);

            // Aggiorna permessi se specificati
            if ($request->has('permissions')) {
                $role->syncPermissions($request->permissions);
            }

            return $this->successResponse(
                $this->formatRoleData($role->load(['permissions', 'users'])),
                'Ruolo aggiornato con successo',
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Errore durante l\'aggiornamento del ruolo');
        }
    }

    /**
     * Delete role
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $role = Role::findOrFail($id);

            // Verifica che non sia il ruolo super
            if ($role->name === 'super') {
                return $this->forbiddenResponse('Non puoi eliminare il ruolo Super User');
            }

            // Verifica che non ci siano utenti associati
            if ($role->users()->count() > 0) {
                return $this->validationErrorResponse(
                    'Non puoi eliminare un ruolo con utenti associati. Rimuovi prima tutti gli utenti.',
                );
            }

            $role->delete();

            return $this->successResponse(null, 'Ruolo eliminato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore durante l\'eliminazione del ruolo');
        }
    }

    /**
     * Assign users to role
     */
    public function assignUsers(AssignRoleUsersRequest $request, string $id): JsonResponse
    {
        try {
            $role = Role::findOrFail($id);

            foreach ($request->user_ids as $userId) {
                $user = config('auth.providers.users.model')::findOrFail($userId);
                $user->assignRole($role);
            }

            return $this->successResponse(
                $this->formatRoleData($role->load(['permissions', 'users'])),
                count($request->user_ids).' utenti assegnati al ruolo',
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Errore durante l\'assegnazione degli utenti');
        }
    }

    /**
     * Remove users from role
     */
    public function removeUsers(AssignRoleUsersRequest $request, string $id): JsonResponse
    {
        try {
            $role = Role::findOrFail($id);

            foreach ($request->user_ids as $userId) {
                $user = config('auth.providers.users.model')::findOrFail($userId);
                $user->removeRole($role);
            }

            return $this->successResponse(
                $this->formatRoleData($role->load(['permissions', 'users'])),
                count($request->user_ids).' utenti rimossi dal ruolo',
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Errore durante la rimozione degli utenti');
        }
    }

    /**
     * Clone role
     */
    public function clone(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'display_name' => 'nullable|string|max:255',
        ]);

        try {
            $originalRole = Role::with('permissions')->findOrFail($id);

            $newRole = Role::create([
                'name' => $request->name,
                'guard_name' => 'api',
                'display_name' => $request->display_name ?? ($originalRole->display_name.' (Copia)'),
                'description' => $originalRole->description.' (Clonato da '.$originalRole->name.')',
            ]);

            // Copia tutti i permessi
            $newRole->syncPermissions($originalRole->permissions);

            return $this->createdResponse(
                $this->formatRoleData($newRole->load(['permissions', 'users'])),
                'Ruolo clonato con successo',
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Errore durante la clonazione del ruolo');
        }
    }

    /**
     * Get role statistics
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = [
                'total_roles' => Role::count(),
                'roles_with_users' => Role::has('users')->count(),
                'most_used_role' => Role::withCount('users')->orderBy('users_count', 'desc')->first(),
                'permission_distribution' => $this->getPermissionDistribution(),
                'user_distribution' => $this->getUserDistribution(),
            ];

            return $this->successResponse($stats);
        } catch (\Exception $e) {
            return $this->errorResponse('Errore durante il recupero delle statistiche');
        }
    }

    /**
     * Private helper methods
     */
    private function formatRoleData(Role $role): array
    {
        return [
            'id' => $role->id,
            'handle' => $role->name,
            'title' => $role->display_name ?? $role->name,
            'description' => $role->description,
            'super' => $role->name === 'super' || $role->hasPermissionTo('super'),
            'permissions' => $role->permissions->pluck('name')->toArray(),
            'permission_count' => $role->permissions->count(),
            'users' => $role->users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ];
            }),
            'user_count' => $role->users->count(),
            'created_at' => $role->created_at?->toISOString(),
            'updated_at' => $role->updated_at?->toISOString(),
        ];
    }

    private function getPermissionGroups(): array
    {
        $groups = [
            'content' => 'Content',
            'collections' => 'Collections',
            'commerce' => 'Commerce',
            'customers' => 'Customers',
            'users' => 'Users',
            'assets' => 'Assets',
            'forms' => 'Forms',
            'settings' => 'Settings',
            'roles' => 'Roles & Permissions',
            'reports' => 'Reports & Analytics',
        ];

        return collect($groups)
            ->map(function ($label, $handle) {
                return [
                    'handle' => $handle,
                    'label' => $label,
                    'permission_count' => Permission::where('name', 'like', "%{$handle}%")->count(),
                ];
            })
            ->values()
            ->toArray();
    }

    private function getPermissionGroupsWithStatus(Role $role): array
    {
        $groups = $this->getPermissionGroups();

        foreach ($groups as &$group) {
            $groupPermissions = Permission::where('name', 'like', "%{$group['handle']}%")->get();
            $group['permissions'] = $groupPermissions->map(function ($permission) use ($role) {
                return [
                    'id' => $permission->id,
                    'name' => $permission->name,
                    'display_name' => $permission->display_name,
                    'granted' => $role->hasPermissionTo($permission->name),
                ];
            });
            $group['granted_count'] = $groupPermissions->filter(fn ($p) => $role->hasPermissionTo($p->name))->count();
        }

        return $groups;
    }

    private function getPermissionDistribution(): array
    {
        return Role::withCount('permissions')
            ->get(['id', 'name', 'display_name', 'permissions_count'])
            ->map(function ($role) {
                return [
                    'role' => $role->display_name ?? $role->name,
                    'permission_count' => $role->permissions_count,
                    'percentage' => Permission::count() > 0
                        ? round(($role->permissions_count / Permission::count()) * 100, 2)
                        : 0,
                ];
            })
            ->toArray();
    }

    private function getUserDistribution(): array
    {
        return Role::withCount('users')
            ->get(['id', 'name', 'display_name', 'users_count'])
            ->map(function ($role) {
                return [
                    'role' => $role->display_name ?? $role->name,
                    'user_count' => $role->users_count,
                    'percentage' => app(config('auth.providers.users.model'))->count() > 0
                        ? round(($role->users_count / app(config('auth.providers.users.model'))->count()) * 100, 2)
                        : 0,
                ];
            })
            ->toArray();
    }
}
