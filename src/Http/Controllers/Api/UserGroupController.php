<?php

namespace Cartino\Http\Controllers\Api;

use Cartino\Http\Requests\Api\AssignPermissionRequest;
use Cartino\Http\Requests\Api\StoreUserGroupRequest;
use Cartino\Http\Requests\Api\UpdateUserGroupRequest;
use Cartino\Models\UserGroup;
use Cartino\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserGroupController extends ApiController
{
    use ApiResponseTrait;

    public function __construct()
    {
        $this->middleware(['auth:api', 'permission:manage-user-groups']);
    }

    /**
     * Display a listing of user groups
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 25);
        $search = $request->get('search');

        $query = UserGroup::withCount('users');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $userGroups = $query->orderBy('name')->paginate($perPage);

        return $this->paginatedResponse($userGroups);
    }

    /**
     * Store a newly created user group
     */
    public function store(StoreUserGroupRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            // Se questo gruppo deve essere default, rimuovi il flag dagli altri
            if ($validated['is_default'] ?? false) {
                UserGroup::where('is_default', true)->update(['is_default' => false]);
            }

            $userGroup = UserGroup::create($validated);

            // Assegna permessi se forniti
            if (! empty($validated['permissions'])) {
                $userGroup->syncPermissions($validated['permissions']);
            }

            return $this->createdResponse($userGroup->load('permissions'), 'Gruppo utenti creato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore durante la creazione del gruppo utenti');
        }
    }

    /**
     * Display the specified user group
     */
    public function show(string $id): JsonResponse
    {
        try {
            $userGroup = UserGroup::with(['users', 'permissions'])->withCount('users')->findOrFail($id);

            return $this->successResponse($userGroup);
        } catch (\Exception $e) {
            return $this->notFoundResponse('Gruppo utenti non trovato');
        }
    }

    /**
     * Update the specified user group
     */
    public function update(UpdateUserGroupRequest $request, string $id): JsonResponse
    {
        try {
            $validated = $request->validated();
            $userGroup = UserGroup::findOrFail($id);

            // Se questo gruppo deve essere default, rimuovi il flag dagli altri
            if (($validated['is_default'] ?? false) && ! $userGroup->is_default) {
                UserGroup::where('is_default', true)->update(['is_default' => false]);
            }

            $userGroup->update($validated);

            // Sincronizza permessi se forniti
            if (isset($validated['permissions'])) {
                $userGroup->syncPermissions($validated['permissions']);
            }

            return $this->successResponse($userGroup->load('permissions'), 'Gruppo utenti aggiornato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore durante l\'aggiornamento del gruppo utenti');
        }
    }

    /**
     * Remove the specified user group
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $userGroup = UserGroup::findOrFail($id);

            // Non permettere l'eliminazione di gruppi con utenti
            if ($userGroup->users()->exists()) {
                return $this->validationErrorResponse('Impossibile eliminare un gruppo con utenti assegnati');
            }

            // Non permettere l'eliminazione del gruppo default
            if ($userGroup->is_default) {
                return $this->validationErrorResponse('Impossibile eliminare il gruppo predefinito');
            }

            $userGroup->delete();

            return $this->successResponse(null, 'Gruppo utenti eliminato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore durante l\'eliminazione del gruppo utenti');
        }
    }

    /**
     * Get users in this group
     */
    public function users(string $id): JsonResponse
    {
        try {
            $userGroup = UserGroup::with('users')->findOrFail($id);

            return $this->successResponse([
                'group' => $userGroup,
                'users' => $userGroup->users,
                'users_count' => $userGroup->users->count(),
            ]);
        } catch (\Exception $e) {
            return $this->notFoundResponse('Gruppo utenti non trovato');
        }
    }

    /**
     * Assign users to group
     */
    public function assignUsers(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'integer|exists:users,id',
        ]);

        try {
            $userGroup = UserGroup::findOrFail($id);
            $userGroup->users()->attach($validated['user_ids']);

            return $this->successResponse(
                $userGroup->load('users'),
                count($validated['user_ids']).' utenti assegnati al gruppo',
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Errore durante l\'assegnazione degli utenti');
        }
    }

    /**
     * Remove users from group
     */
    public function removeUsers(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'integer|exists:users,id',
        ]);

        try {
            $userGroup = UserGroup::findOrFail($id);
            $userGroup->users()->detach($validated['user_ids']);

            return $this->successResponse(
                $userGroup->load('users'),
                count($validated['user_ids']).' utenti rimossi dal gruppo',
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Errore durante la rimozione degli utenti');
        }
    }

    /**
     * Sync group permissions
     */
    public function syncPermissions(AssignPermissionRequest $request, string $id): JsonResponse
    {
        try {
            $validated = $request->validated();
            $userGroup = UserGroup::findOrFail($id);
            $userGroup->syncPermissions($validated['permissions']);

            return $this->successResponse(
                $userGroup->load('permissions'),
                'Permessi del gruppo sincronizzati con successo',
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Errore durante la sincronizzazione dei permessi');
        }
    }

    /**
     * Get group permissions matrix
     */
    public function permissionsMatrix(string $id): JsonResponse
    {
        try {
            $userGroup = UserGroup::with('permissions')->findOrFail($id);

            // Ottieni tutte le risorse disponibili
            $resourceController = new ResourcePermissionController;
            $resources = $resourceController->getResourcesMatrix();

            // Aggiungi informazioni sui permessi del gruppo
            foreach ($resources as &$resource) {
                foreach ($resource['permissions'] as &$permission) {
                    $permission['granted'] = $userGroup->hasPermissionTo($permission['name']);
                }
            }

            return $this->successResponse([
                'group' => $userGroup,
                'resources' => $resources,
            ]);
        } catch (\Exception $e) {
            return $this->notFoundResponse('Gruppo utenti non trovato');
        }
    }

    /**
     * Handle bulk actions on user groups
     */
    public function bulk(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'action' => 'required|in:delete,activate,deactivate,assign_permissions',
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:user_groups,id',
            'permissions' => 'required_if:action,assign_permissions|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        try {
            $userGroups = UserGroup::whereIn('id', $validated['ids'])->get();
            $count = 0;

            foreach ($userGroups as $userGroup) {
                switch ($validated['action']) {
                    case 'delete':
                        if (! $userGroup->users()->exists() && ! $userGroup->is_default) {
                            $userGroup->delete();
                            $count++;
                        }
                        break;

                    case 'activate':
                        $userGroup->update(['is_active' => true]);
                        $count++;
                        break;

                    case 'deactivate':
                        if (! $userGroup->is_default) {
                            $userGroup->update(['is_active' => false]);
                            $count++;
                        }
                        break;

                    case 'assign_permissions':
                        $userGroup->syncPermissions($validated['permissions']);
                        $count++;
                        break;
                }
            }

            return $this->bulkActionResponse($count, "Azione '{$validated['action']}' eseguita", [
                'processed' => $count,
                'total' => count($validated['ids']),
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Errore durante l\'esecuzione dell\'azione bulk');
        }
    }

    /**
     * Get default user group
     */
    public function default(): JsonResponse
    {
        try {
            $defaultGroup = UserGroup::where('is_default', true)->first();

            if (! $defaultGroup) {
                return $this->notFoundResponse('Nessun gruppo predefinito configurato');
            }

            return $this->successResponse($defaultGroup->load('permissions'));
        } catch (\Exception $e) {
            return $this->errorResponse('Errore durante il recupero del gruppo predefinito');
        }
    }

    /**
     * Set group as default
     */
    public function setAsDefault(string $id): JsonResponse
    {
        try {
            $userGroup = UserGroup::findOrFail($id);

            // Rimuovi il flag default da tutti gli altri gruppi
            UserGroup::where('is_default', true)->update(['is_default' => false]);

            // Imposta questo gruppo come default
            $userGroup->update(['is_default' => true, 'is_active' => true]);

            return $this->successResponse($userGroup, 'Gruppo impostato come predefinito');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore durante l\'impostazione del gruppo predefinito');
        }
    }
}
