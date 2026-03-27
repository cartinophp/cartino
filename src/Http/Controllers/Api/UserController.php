<?php

namespace Cartino\Http\Controllers\Api;

use Cartino\Http\Requests\Api\AssignPermissionRequest;
use Cartino\Http\Requests\Api\AssignRoleRequest;
use Cartino\Models\User;
use Cartino\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserController extends ApiController
{
    use ApiResponseTrait;

    public function __construct()
    {
        $this->middleware(['auth:api', 'permission:manage-users']);
    }

    /**
     * Display a listing of users
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::query();

        // Search filter
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Role filter
        if ($role = $request->get('role')) {
            $query->whereHas('roles', function ($q) use ($role) {
                $q->where('name', $role);
            });
        }

        $perPage = $request->get('per_page', 25);
        $users = $query->with(['roles', 'permissions'])->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'data' => $users->items(),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
                'from' => $users->firstItem(),
                'to' => $users->lastItem(),
            ],
            'links' => [
                'first' => $users->url(1),
                'last' => $users->url($users->lastPage()),
                'prev' => $users->previousPageUrl(),
                'next' => $users->nextPageUrl(),
            ],
        ]);
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'avatar' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'timezone' => 'nullable|string|max:50',
            'locale' => 'nullable|string|max:5',
            'is_active' => 'boolean',
            'roles' => 'nullable|array',
            'roles.*' => 'string|exists:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        try {
            $validated['password'] = Hash::make($validated['password']);

            $user = User::create($validated);

            // Assign roles if provided
            if (! empty($validated['roles'])) {
                $user->assignRole($validated['roles']);
            }

            // Assign permissions if provided
            if (! empty($validated['permissions'])) {
                $user->givePermissionTo($validated['permissions']);
            }

            return response()->json([
                'message' => 'Utente creato con successo',
                'data' => $user->load(['roles', 'permissions']),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Errore durante la creazione dell\'utente',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified user
     */
    public function show(string $id): JsonResponse
    {
        try {
            $user = User::with(['roles', 'permissions'])->findOrFail($id);

            return response()->json([
                'data' => $user,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Utente non trovato',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$id,
            'password' => 'nullable|string|min:8|confirmed',
            'avatar' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'timezone' => 'nullable|string|max:50',
            'locale' => 'nullable|string|max:5',
            'is_active' => 'boolean',
            'roles' => 'nullable|array',
            'roles.*' => 'string|exists:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        try {
            $user = User::findOrFail($id);

            // Hash password if provided
            if (! empty($validated['password'])) {
                $validated['password'] = Hash::make($validated['password']);
            } else {
                unset($validated['password']);
            }

            $user->update($validated);

            // Update roles if provided
            if (isset($validated['roles'])) {
                $user->syncRoles($validated['roles']);
            }

            // Update permissions if provided
            if (isset($validated['permissions'])) {
                $user->syncPermissions($validated['permissions']);
            }

            return response()->json([
                'message' => 'Utente aggiornato con successo',
                'data' => $user->fresh(['roles', 'permissions']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Errore durante l\'aggiornamento dell\'utente',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified user
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);

            // Prevent deletion of the current user
            if (auth()->id() == $id) {
                return response()->json([
                    'message' => 'Non puoi eliminare il tuo account',
                ], 422);
            }

            $user->delete();

            return response()->json([
                'message' => 'Utente eliminato con successo',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Errore durante l\'eliminazione dell\'utente',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update user password
     */
    public function updatePassword(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => 'required_if:id,'.auth()->id(),
            'password' => 'required|string|min:8|confirmed',
        ]);

        try {
            $user = User::findOrFail($id);

            // If user is updating their own password, verify current password
            if (auth()->id() == $id) {
                if (! Hash::check($validated['current_password'], $user->password)) {
                    return response()->json([
                        'message' => 'Password attuale non corretta',
                    ], 422);
                }
            }

            $user->update([
                'password' => Hash::make($validated['password']),
            ]);

            return response()->json([
                'message' => 'Password aggiornata con successo',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Errore durante l\'aggiornamento della password',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle user status
     */
    public function toggleStatus(string $id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);

            // Prevent deactivation of the current user
            if (auth()->id() == $id && $user->is_active) {
                return response()->json([
                    'message' => 'Non puoi disattivare il tuo account',
                ], 422);
            }

            $user->update([
                'is_active' => ! $user->is_active,
            ]);

            return response()->json([
                'message' => 'Stato utente aggiornato',
                'data' => $user->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Errore durante l\'aggiornamento dello stato',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get user permissions
     */
    public function permissions(string $id): JsonResponse
    {
        try {
            $user = User::with(['roles.permissions', 'permissions'])->findOrFail($id);

            $allPermissions = $user->getAllPermissions();

            return response()->json([
                'data' => [
                    'direct_permissions' => $user->permissions,
                    'role_permissions' => $user->roles->flatMap->permissions->unique(),
                    'all_permissions' => $allPermissions,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Errore durante il recupero dei permessi',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle bulk actions
     */
    public function bulk(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'action' => 'required|in:delete,activate,deactivate',
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:users,id',
        ]);

        try {
            $users = User::whereIn('id', $validated['ids']);
            $currentUserId = auth()->id();
            $count = 0;
            $errors = [];

            switch ($validated['action']) {
                case 'delete':
                    $users
                        ->get()
                        ->each(function ($user) use (&$count, &$errors, $currentUserId) {
                            if ($user->id == $currentUserId) {
                                $errors[] = 'Non puoi eliminare il tuo account';

                                return;
                            }
                            $user->delete();
                            $count++;
                        });
                    break;

                case 'activate':
                    $count = $users->update(['is_active' => true]);
                    break;

                case 'deactivate':
                    $users
                        ->get()
                        ->each(function ($user) use (&$count, &$errors, $currentUserId) {
                            if ($user->id == $currentUserId) {
                                $errors[] = 'Non puoi disattivare il tuo account';

                                return;
                            }
                            $user->update(['is_active' => false]);
                            $count++;
                        });
                    break;
            }

            $result = [
                'count' => $count,
                'errors' => $errors,
            ];

            if (! empty($errors)) {
                return response()->json([
                    'message' => "Azione eseguita su {$count} utenti con alcuni errori",
                    'data' => $result,
                ], 207); // 207 Multi-Status
            }

            return $this->bulkActionResponse($count, "Azione '{$validated['action']}' eseguita", $result);
        } catch (\Exception $e) {
            return $this->errorResponse('Errore durante l\'esecuzione dell\'azione bulk');
        }
    }

    /**
     * Assign roles to user
     */
    public function assignRoles(AssignRoleRequest $request, string $id): JsonResponse
    {
        try {
            $validated = $request->validated();
            $user = User::findOrFail($id);
            $user->syncRoles($validated['roles']);

            return $this->successResponse($user->load('roles'), 'Ruoli assegnati con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore durante l\'assegnazione dei ruoli');
        }
    }

    /**
     * Remove roles from user
     */
    public function revokeRoles(AssignRoleRequest $request, string $id): JsonResponse
    {
        try {
            $validated = $request->validated();
            $user = User::findOrFail($id);
            $user->removeRole($validated['roles']);

            return $this->successResponse($user->load('roles'), 'Ruoli revocati con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore durante la revoca dei ruoli');
        }
    }

    /**
     * Assign direct permissions to user
     */
    public function assignPermissions(AssignPermissionRequest $request, string $id): JsonResponse
    {
        try {
            $validated = $request->validated();
            $user = User::findOrFail($id);
            $user->givePermissionTo($validated['permissions']);

            return $this->successResponse($user->load(['roles', 'permissions']), 'Permessi assegnati con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore durante l\'assegnazione dei permessi');
        }
    }

    /**
     * Remove direct permissions from user
     */
    public function revokePermissions(AssignPermissionRequest $request, string $id): JsonResponse
    {
        try {
            $validated = $request->validated();
            $user = User::findOrFail($id);
            $user->revokePermissionTo($validated['permissions']);

            return $this->successResponse($user->load(['roles', 'permissions']), 'Permessi revocati con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore durante la revoca dei permessi');
        }
    }

    /**
     * Get all permissions for user (direct + from roles)
     */
    public function userPermissions(string $id): JsonResponse
    {
        try {
            $user = User::with(['roles.permissions', 'permissions'])->findOrFail($id);

            $allPermissions = $user->getAllPermissions();
            $directPermissions = $user->getDirectPermissions();
            $rolePermissions = $user->getPermissionsViaRoles();

            return $this->successResponse([
                'user' => $user->name,
                'all_permissions' => $allPermissions,
                'direct_permissions' => $directPermissions,
                'role_permissions' => $rolePermissions,
                'roles' => $user->roles,
            ]);
        } catch (\Exception $e) {
            return $this->notFoundResponse('Utente non trovato');
        }
    }

    /**
     * Check if user has specific permission
     */
    public function hasPermission(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'permission' => 'required|string|exists:permissions,name',
        ]);

        try {
            $user = User::findOrFail($id);
            $hasPermission = $user->hasPermissionTo($validated['permission']);

            return $this->successResponse([
                'user' => $user->name,
                'permission' => $validated['permission'],
                'has_permission' => $hasPermission,
            ]);
        } catch (\Exception $e) {
            return $this->notFoundResponse('Utente non trovato');
        }
    }

    /**
     * Check if user has specific role
     */
    public function hasRole(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'role' => 'required|string|exists:roles,name',
        ]);

        try {
            $user = User::findOrFail($id);
            $hasRole = $user->hasRole($validated['role']);

            return $this->successResponse([
                'user' => $user->name,
                'role' => $validated['role'],
                'has_role' => $hasRole,
            ]);
        } catch (\Exception $e) {
            return $this->notFoundResponse('Utente non trovato');
        }
    }

    /**
     * Get users by role
     */
    public function byRole(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'role' => 'required|string|exists:roles,name',
        ]);

        try {
            $users = User::role($validated['role'])->with(['roles', 'permissions'])->get();

            return $this->successResponse([
                'role' => $validated['role'],
                'users' => $users,
                'count' => $users->count(),
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Errore durante il recupero degli utenti');
        }
    }

    /**
     * Get users by permission
     */
    public function byPermission(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'permission' => 'required|string|exists:permissions,name',
        ]);

        try {
            $users = User::permission($validated['permission'])->with(['roles', 'permissions'])->get();

            return $this->successResponse([
                'permission' => $validated['permission'],
                'users' => $users,
                'count' => $users->count(),
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Errore durante il recupero degli utenti');
        }
    }
}
