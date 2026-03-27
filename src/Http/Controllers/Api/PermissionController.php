<?php

namespace Cartino\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionController extends ApiController
{
    public function __construct()
    {
        $this->middleware(['auth:api', 'permission:manage-permissions']);
    }

    /**
     * Get permission structure (grouped by areas)
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $permissionGroups = $this->getPermissionStructure();

            return $this->successResponse([
                'permission_groups' => $permissionGroups,
                'available_roles' => Role::all(['id', 'name', 'display_name']),
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Errore durante il recupero della struttura dei permessi');
        }
    }

    /**
     * Get role permissions in structured format
     */
    public function rolePermissions(string $roleId): JsonResponse
    {
        try {
            $role = Role::with('permissions')->findOrFail($roleId);
            $permissionGroups = $this->getPermissionStructure();

            // Aggiungi informazioni sui permessi del ruolo
            foreach ($permissionGroups as &$group) {
                foreach ($group['permissions'] as &$permission) {
                    $permission['granted'] = $role->hasPermissionTo($permission['handle']);
                }
            }

            return $this->successResponse([
                'role' => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'display_name' => $role->display_name ?? $role->name,
                    'description' => $role->description,
                    'is_super' => $role->name === 'super' || $role->hasPermissionTo('super'),
                ],
                'permission_groups' => $permissionGroups,
                'inherited_from' => $this->getInheritedRoles($role),
            ]);
        } catch (\Exception $e) {
            return $this->notFoundResponse('Ruolo non trovato');
        }
    }

    /**
     * Update role permissions using structured format
     */
    public function updateRolePermissions(Request $request, string $roleId): JsonResponse
    {
        $validated = $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'string',
            'inherit_from' => 'nullable|array',
            'inherit_from.*' => 'integer|exists:roles,id',
        ]);

        try {
            $role = Role::findOrFail($roleId);

            // Verifica permessi esistenti
            $existingPermissions = Permission::whereIn('name', $validated['permissions'])->pluck('name')->toArray();
            $invalidPermissions = array_diff($validated['permissions'], $existingPermissions);

            if (! empty($invalidPermissions)) {
                return $this->validationErrorResponse('Permessi non validi: '.implode(', ', $invalidPermissions));
            }

            // Sincronizza permessi
            $role->syncPermissions($validated['permissions']);

            // Gestisci ereditarietà (se implementata)
            if (isset($validated['inherit_from'])) {
                $this->setRoleInheritance($role, $validated['inherit_from']);
            }

            return $this->successResponse($role->load('permissions'), 'Permessi del ruolo aggiornati con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore durante l\'aggiornamento dei permessi');
        }
    }

    /**
     * Generate structured permissions for all areas
     */
    public function generatePermissions(Request $request): JsonResponse
    {
        try {
            $permissionStructure = $this->getPermissionStructure();
            $createdPermissions = [];

            foreach ($permissionStructure as $group) {
                foreach ($group['permissions'] as $permission) {
                    $perm = Permission::firstOrCreate(
                        ['name' => $permission['handle'], 'guard_name' => 'api'],
                        [
                            'display_name' => $permission['label'],
                            'description' => $permission['description'] ?? null,
                            'group' => $group['handle'],
                        ],
                    );

                    if ($perm->wasRecentlyCreated) {
                        $createdPermissions[] = $perm;
                    }
                }
            }

            return $this->successResponse(
                [
                    'created_permissions' => $createdPermissions,
                    'count' => count($createdPermissions),
                    'structure' => $permissionStructure,
                ],
                count($createdPermissions).' permessi creati con successo',
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Errore durante la generazione dei permessi');
        }
    }

    /**
     * Create super user role
     */
    public function createSuperRole(): JsonResponse
    {
        try {
            $superRole = Role::firstOrCreate(
                ['name' => 'super', 'guard_name' => 'api'],
                [
                    'display_name' => 'Super User',
                    'description' => 'Accesso completo a tutte le funzionalità del sistema',
                ],
            );

            // Assegna tutti i permessi
            $allPermissions = Permission::all();
            $superRole->syncPermissions($allPermissions);

            return $this->successResponse(
                $superRole->load('permissions'),
                'Ruolo Super User '.($superRole->wasRecentlyCreated ? 'creato' : 'aggiornato').' con successo',
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Errore durante la creazione del ruolo Super User');
        }
    }

    /**
     * Get permission tree (hierarchical structure)
     */
    public function permissionTree(): JsonResponse
    {
        try {
            $tree = $this->buildPermissionTree();

            return $this->successResponse([
                'tree' => $tree,
                'total_groups' => count($tree),
                'total_permissions' => collect($tree)->sum(fn ($group) => count($group['permissions'])),
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Errore durante la generazione dell\'albero dei permessi');
        }
    }

    /**
     * Private helper methods
     */
    private function getPermissionStructure(): array
    {
        return [
            [
                'handle' => 'content',
                'label' => 'Content',
                'description' => 'Gestione contenuti e pubblicazioni',
                'icon' => 'content-writing',
                'permissions' => [
                    ['handle' => 'view content', 'label' => 'View Content', 'description' => 'Visualizzare contenuti'],
                    [
                        'handle' => 'create content',
                        'label' => 'Create Content',
                        'description' => 'Creare nuovi contenuti',
                    ],
                    [
                        'handle' => 'edit content',
                        'label' => 'Edit Content',
                        'description' => 'Modificare contenuti esistenti',
                    ],
                    ['handle' => 'delete content', 'label' => 'Delete Content', 'description' => 'Eliminare contenuti'],
                    [
                        'handle' => 'publish content',
                        'label' => 'Publish Content',
                        'description' => 'Pubblicare contenuti',
                    ],
                    [
                        'handle' => 'edit other authors content',
                        'label' => 'Edit Other Authors Content',
                        'description' => 'Modificare contenuti di altri autori',
                    ],
                ],
            ],
            [
                'handle' => 'collections',
                'label' => 'Collections',
                'description' => 'Gestione collezioni e categorie',
                'icon' => 'collection',
                'permissions' => [
                    ['handle' => 'view collections', 'label' => 'View Collections'],
                    ['handle' => 'create collections', 'label' => 'Create Collections'],
                    ['handle' => 'edit collections', 'label' => 'Edit Collections'],
                    ['handle' => 'delete collections', 'label' => 'Delete Collections'],
                    ['handle' => 'configure collections', 'label' => 'Configure Collections'],
                ],
            ],
            [
                'handle' => 'commerce',
                'label' => 'Commerce',
                'description' => 'E-commerce e gestione ordini',
                'icon' => 'shopping-cart',
                'permissions' => [
                    ['handle' => 'view orders', 'label' => 'View Orders'],
                    ['handle' => 'create orders', 'label' => 'Create Orders'],
                    ['handle' => 'edit orders', 'label' => 'Edit Orders'],
                    ['handle' => 'delete orders', 'label' => 'Delete Orders'],
                    ['handle' => 'process orders', 'label' => 'Process Orders'],
                    ['handle' => 'view products', 'label' => 'View Products'],
                    ['handle' => 'create products', 'label' => 'Create Products'],
                    ['handle' => 'edit products', 'label' => 'Edit Products'],
                    ['handle' => 'delete products', 'label' => 'Delete Products'],
                    ['handle' => 'manage inventory', 'label' => 'Manage Inventory'],
                ],
            ],
            [
                'handle' => 'customers',
                'label' => 'Customers',
                'description' => 'Gestione clienti e gruppi',
                'icon' => 'users',
                'permissions' => [
                    ['handle' => 'view customers', 'label' => 'View Customers'],
                    ['handle' => 'create customers', 'label' => 'Create Customers'],
                    ['handle' => 'edit customers', 'label' => 'Edit Customers'],
                    ['handle' => 'delete customers', 'label' => 'Delete Customers'],
                    ['handle' => 'manage customer groups', 'label' => 'Manage Customer Groups'],
                ],
            ],
            [
                'handle' => 'users',
                'label' => 'Users',
                'description' => 'Gestione utenti del sistema',
                'icon' => 'user',
                'permissions' => [
                    ['handle' => 'view users', 'label' => 'View Users'],
                    ['handle' => 'create users', 'label' => 'Create Users'],
                    ['handle' => 'edit users', 'label' => 'Edit Users'],
                    ['handle' => 'delete users', 'label' => 'Delete Users'],
                    ['handle' => 'edit user roles', 'label' => 'Edit User Roles'],
                    ['handle' => 'edit user groups', 'label' => 'Edit User Groups'],
                ],
            ],
            [
                'handle' => 'assets',
                'label' => 'Assets',
                'description' => 'Gestione file e media',
                'icon' => 'assets',
                'permissions' => [
                    ['handle' => 'view assets', 'label' => 'View Assets'],
                    ['handle' => 'upload assets', 'label' => 'Upload Assets'],
                    ['handle' => 'edit assets', 'label' => 'Edit Assets'],
                    ['handle' => 'move assets', 'label' => 'Move Assets'],
                    ['handle' => 'delete assets', 'label' => 'Delete Assets'],
                ],
            ],
            [
                'handle' => 'forms',
                'label' => 'Forms',
                'description' => 'Gestione form e submissions',
                'icon' => 'form',
                'permissions' => [
                    ['handle' => 'view forms', 'label' => 'View Forms'],
                    ['handle' => 'create forms', 'label' => 'Create Forms'],
                    ['handle' => 'edit forms', 'label' => 'Edit Forms'],
                    ['handle' => 'delete forms', 'label' => 'Delete Forms'],
                    ['handle' => 'view form submissions', 'label' => 'View Form Submissions'],
                    ['handle' => 'delete form submissions', 'label' => 'Delete Form Submissions'],
                ],
            ],
            [
                'handle' => 'settings',
                'label' => 'Settings',
                'description' => 'Configurazioni di sistema',
                'icon' => 'settings',
                'permissions' => [
                    ['handle' => 'view settings', 'label' => 'View Settings'],
                    ['handle' => 'edit settings', 'label' => 'Edit Settings'],
                    ['handle' => 'configure fields', 'label' => 'Configure Fields'],
                    ['handle' => 'configure collections', 'label' => 'Configure Collections'],
                    ['handle' => 'configure sites', 'label' => 'Configure Sites'],
                ],
            ],
            [
                'handle' => 'roles',
                'label' => 'Roles & Permissions',
                'description' => 'Gestione ruoli e permessi',
                'icon' => 'shield',
                'permissions' => [
                    ['handle' => 'view roles', 'label' => 'View Roles'],
                    ['handle' => 'create roles', 'label' => 'Create Roles'],
                    ['handle' => 'edit roles', 'label' => 'Edit Roles'],
                    ['handle' => 'delete roles', 'label' => 'Delete Roles'],
                    ['handle' => 'assign roles', 'label' => 'Assign Roles'],
                    [
                        'handle' => 'super',
                        'label' => 'Super User',
                        'description' => 'Accesso completo senza restrizioni',
                    ],
                ],
            ],
            [
                'handle' => 'reports',
                'label' => 'Reports & Analytics',
                'description' => 'Report e statistiche',
                'icon' => 'charts',
                'permissions' => [
                    ['handle' => 'view reports', 'label' => 'View Reports'],
                    ['handle' => 'create reports', 'label' => 'Create Reports'],
                    ['handle' => 'export reports', 'label' => 'Export Reports'],
                    ['handle' => 'view analytics', 'label' => 'View Analytics'],
                ],
            ],
        ];
    }

    private function buildPermissionTree(): array
    {
        $structure = $this->getPermissionStructure();
        $existingPermissions = Permission::all()->keyBy('name');

        $tree = [];
        foreach ($structure as $group) {
            $groupData = [
                'handle' => $group['handle'],
                'label' => $group['label'],
                'description' => $group['description'],
                'icon' => $group['icon'] ?? 'folder',
                'permissions' => [],
                'permission_count' => 0,
            ];

            foreach ($group['permissions'] as $permission) {
                $existing = $existingPermissions->get($permission['handle']);
                $groupData['permissions'][] = [
                    'handle' => $permission['handle'],
                    'label' => $permission['label'],
                    'description' => $permission['description'] ?? null,
                    'exists' => $existing !== null,
                    'id' => $existing?->id,
                ];
                $groupData['permission_count']++;
            }

            $tree[] = $groupData;
        }

        return $tree;
    }

    private function getInheritedRoles(Role $role): array
    {
        // Placeholder per ereditarietà ruoli (da implementare se necessario)
        return [];
    }

    private function setRoleInheritance(Role $role, array $inheritFromIds): void
    {
        // Placeholder per impostare ereditarietà ruoli (da implementare se necessario)
    }
}
