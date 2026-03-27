<?php

namespace Cartino\Http\Controllers\Api;

use Cartino\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionBuilderController extends ApiController
{
    use ApiResponseTrait;

    public function __construct()
    {
        $this->middleware(['auth:api', 'permission:super|manage-permissions']);
    }

    /**
     * Get permission builder interface
     */
    public function builder(Request $request): JsonResponse
    {
        try {
            $structure = $this->getBuilderStructure();
            $roles = Role::with('permissions')->get();

            return $this->successResponse([
                'structure' => $structure,
                'roles' => $roles->map(fn ($role) => $this->formatRoleForBuilder($role)),
                'matrix' => $this->buildPermissionMatrix($structure, $roles),
                'templates' => $this->getPermissionTemplates(),
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Errore durante il caricamento del builder permessi');
        }
    }

    /**
     * Update permission matrix (bulk update)
     */
    public function updateMatrix(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'matrix' => 'required|array',
            'matrix.*.role_id' => 'required|integer|exists:roles,id',
            'matrix.*.permissions' => 'required|array',
            'matrix.*.permissions.*' => 'string',
        ]);

        try {
            $updatedRoles = [];

            foreach ($validated['matrix'] as $roleData) {
                $role = Role::findOrFail($roleData['role_id']);

                // Verifica permessi super
                if (in_array('super', $roleData['permissions']) && ! $request->user()->hasRole('super')) {
                    continue; // Salta ruoli che tentano di assegnare super senza autorizzazione
                }

                $role->syncPermissions($roleData['permissions']);
                $updatedRoles[] = $this->formatRoleForBuilder($role->fresh('permissions'));
            }

            return $this->successResponse([
                'updated_roles' => $updatedRoles,
                'count' => count($updatedRoles),
            ], 'Matrice permessi aggiornata con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore durante l\'aggiornamento della matrice');
        }
    }

    /**
     * Apply permission template to role
     */
    public function applyTemplate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'role_id' => 'required|integer|exists:roles,id',
            'template' => 'required|string|in:content_manager,shop_manager,customer_service,read_only,super_admin',
            'merge' => 'nullable|boolean', // Se true, fa merge con permessi esistenti
        ]);

        try {
            $role = Role::findOrFail($validated['role_id']);
            $template = $this->getPermissionTemplates()[$validated['template']];

            if ($validated['merge'] ?? false) {
                // Merge con permessi esistenti
                $existingPermissions = $role->permissions->pluck('name')->toArray();
                $newPermissions = array_unique(array_merge($existingPermissions, $template['permissions']));
                $role->syncPermissions($newPermissions);
            } else {
                // Sostituisci completamente
                $role->syncPermissions($template['permissions']);
            }

            return $this->successResponse(
                $this->formatRoleForBuilder($role->fresh('permissions')),
                "Template '{$template['label']}' applicato al ruolo {$role->name}",
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Errore durante l\'applicazione del template');
        }
    }

    /**
     * Generate permissions for new resource
     */
    public function generateResourcePermissions(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'resource' => 'required|string|max:100|regex:/^[a-z_]+$/',
            'actions' => 'nullable|array',
            'actions.*' => 'string|in:view,create,edit,delete,publish,configure,manage',
            'generate_all' => 'nullable|boolean',
        ]);

        try {
            $resource = $validated['resource'];
            $actions = $validated['actions'] ?? ['view', 'create', 'edit', 'delete'];

            if ($validated['generate_all'] ?? false) {
                $actions = ['view', 'create', 'edit', 'delete', 'publish', 'configure', 'manage'];
            }

            $createdPermissions = [];

            foreach ($actions as $action) {
                $permissionName = "{$action} {$resource}";

                $permission = Permission::firstOrCreate(
                    ['name' => $permissionName, 'guard_name' => 'api'],
                    [
                        'display_name' => ucfirst($action).' '.ucfirst(str_replace('_', ' ', $resource)),
                        'description' => "Permesso per {$action} su {$resource}",
                        'group' => $this->determineGroupFromResource($resource),
                    ],
                );

                if ($permission->wasRecentlyCreated) {
                    $createdPermissions[] = $permission;
                }
            }

            return $this->successResponse(
                [
                    'resource' => $resource,
                    'created_permissions' => $createdPermissions,
                    'count' => count($createdPermissions),
                    'all_permissions' => Permission::where('name', 'like', "%{$resource}%")->get(),
                ],
                count($createdPermissions)." permessi creati per la risorsa '{$resource}'",
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Errore durante la generazione dei permessi');
        }
    }

    /**
     * Export permission configuration
     */
    public function export(): JsonResponse
    {
        try {
            $config = [
                'version' => '1.0',
                'exported_at' => now()->toISOString(),
                'structure' => $this->getBuilderStructure(),
                'roles' => Role::with('permissions')
                    ->get()
                    ->map(function ($role) {
                        return [
                            'name' => $role->name,
                            'display_name' => $role->display_name,
                            'description' => $role->description,
                            'permissions' => $role->permissions->pluck('name')->toArray(),
                        ];
                    }),
                'templates' => $this->getPermissionTemplates(),
            ];

            return $this->successResponse($config, 'Configurazione permessi esportata');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore durante l\'esportazione');
        }
    }

    /**
     * Import permission configuration
     */
    public function import(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'config' => 'required|array',
            'merge_roles' => 'nullable|boolean',
            'create_missing_permissions' => 'nullable|boolean',
        ]);

        try {
            $config = $validated['config'];
            $mergeRoles = $validated['merge_roles'] ?? false;
            $createMissingPermissions = $validated['create_missing_permissions'] ?? true;

            $imported = [
                'roles' => 0,
                'permissions' => 0,
                'errors' => [],
            ];

            // Crea permessi mancanti se richiesto
            if ($createMissingPermissions && isset($config['structure'])) {
                foreach ($config['structure'] as $group) {
                    foreach ($group['permissions'] as $permissionData) {
                        Permission::firstOrCreate(
                            ['name' => $permissionData['handle'], 'guard_name' => 'api'],
                            [
                                'display_name' => $permissionData['label'],
                                'description' => $permissionData['description'] ?? null,
                                'group' => $group['handle'],
                            ],
                        );
                        $imported['permissions']++;
                    }
                }
            }

            // Importa ruoli
            if (isset($config['roles'])) {
                foreach ($config['roles'] as $roleData) {
                    try {
                        if ($mergeRoles) {
                            $role = Role::firstOrCreate(
                                ['name' => $roleData['name'], 'guard_name' => 'api'],
                                [
                                    'display_name' => $roleData['display_name'],
                                    'description' => $roleData['description'],
                                ],
                            );
                        } else {
                            $role = Role::updateOrCreate(
                                ['name' => $roleData['name'], 'guard_name' => 'api'],
                                [
                                    'display_name' => $roleData['display_name'],
                                    'description' => $roleData['description'],
                                ],
                            );
                        }

                        $role->syncPermissions($roleData['permissions']);
                        $imported['roles']++;
                    } catch (\Exception $e) {
                        $imported['errors'][] = "Errore importando ruolo {$roleData['name']}: {$e->getMessage()}";
                    }
                }
            }

            return $this->successResponse($imported, 'Configurazione importata con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore durante l\'importazione: '.$e->getMessage());
        }
    }

    /**
     * Private helper methods
     */
    private function getBuilderStructure(): array
    {
        // Struttura completa organizzata per aree funzionali
        return [
            [
                'handle' => 'content',
                'label' => 'Content Management',
                'description' => 'Gestione contenuti, articoli e pubblicazioni',
                'icon' => 'content-writing',
                'color' => 'blue',
                'permissions' => [
                    ['handle' => 'view content', 'label' => 'View', 'description' => 'Visualizzare contenuti'],
                    ['handle' => 'create content', 'label' => 'Create', 'description' => 'Creare contenuti'],
                    ['handle' => 'edit content', 'label' => 'Edit', 'description' => 'Modificare contenuti propri'],
                    [
                        'handle' => 'edit any content',
                        'label' => 'Edit Any',
                        'description' => 'Modificare qualsiasi contenuto',
                    ],
                    ['handle' => 'delete content', 'label' => 'Delete', 'description' => 'Eliminare contenuti'],
                    ['handle' => 'publish content', 'label' => 'Publish', 'description' => 'Pubblicare contenuti'],
                    [
                        'handle' => 'configure content',
                        'label' => 'Configure',
                        'description' => 'Configurare tipi di contenuto',
                    ],
                ],
            ],
            [
                'handle' => 'commerce',
                'label' => 'E-Commerce',
                'description' => 'Gestione negozio, prodotti e ordini',
                'icon' => 'shopping-cart',
                'color' => 'green',
                'permissions' => [
                    ['handle' => 'view products', 'label' => 'View Products'],
                    ['handle' => 'create products', 'label' => 'Create Products'],
                    ['handle' => 'edit products', 'label' => 'Edit Products'],
                    ['handle' => 'delete products', 'label' => 'Delete Products'],
                    ['handle' => 'manage inventory', 'label' => 'Manage Inventory'],
                    ['handle' => 'view orders', 'label' => 'View Orders'],
                    ['handle' => 'create orders', 'label' => 'Create Orders'],
                    ['handle' => 'edit orders', 'label' => 'Edit Orders'],
                    ['handle' => 'process orders', 'label' => 'Process Orders'],
                    ['handle' => 'configure commerce', 'label' => 'Configure Commerce'],
                ],
            ],
            [
                'handle' => 'customers',
                'label' => 'Customer Management',
                'description' => 'Gestione clienti e gruppi clienti',
                'icon' => 'users',
                'color' => 'purple',
                'permissions' => [
                    ['handle' => 'view customers', 'label' => 'View Customers'],
                    ['handle' => 'create customers', 'label' => 'Create Customers'],
                    ['handle' => 'edit customers', 'label' => 'Edit Customers'],
                    ['handle' => 'delete customers', 'label' => 'Delete Customers'],
                    ['handle' => 'manage customer groups', 'label' => 'Manage Groups'],
                ],
            ],
            [
                'handle' => 'users',
                'label' => 'User Management',
                'description' => 'Gestione utenti del sistema',
                'icon' => 'user',
                'color' => 'orange',
                'permissions' => [
                    ['handle' => 'view users', 'label' => 'View Users'],
                    ['handle' => 'create users', 'label' => 'Create Users'],
                    ['handle' => 'edit users', 'label' => 'Edit Users'],
                    ['handle' => 'delete users', 'label' => 'Delete Users'],
                    ['handle' => 'edit user roles', 'label' => 'Edit Roles'],
                ],
            ],
            [
                'handle' => 'system',
                'label' => 'System Administration',
                'description' => 'Amministrazione sistema e configurazioni',
                'icon' => 'settings',
                'color' => 'red',
                'permissions' => [
                    ['handle' => 'view settings', 'label' => 'View Settings'],
                    ['handle' => 'edit settings', 'label' => 'Edit Settings'],
                    ['handle' => 'manage permissions', 'label' => 'Manage Permissions'],
                    [
                        'handle' => 'super',
                        'label' => 'Super User',
                        'description' => 'Accesso completo senza restrizioni',
                    ],
                ],
            ],
        ];
    }

    private function formatRoleForBuilder(Role $role): array
    {
        return [
            'id' => $role->id,
            'name' => $role->name,
            'title' => $role->display_name ?? $role->name,
            'description' => $role->description,
            'super' => $role->hasPermissionTo('super'),
            'permissions' => $role->permissions->pluck('name')->toArray(),
            'permission_count' => $role->permissions->count(),
            'user_count' => $role->users()->count(),
        ];
    }

    private function buildPermissionMatrix(array $structure, $roles): array
    {
        $matrix = [];

        foreach ($roles as $role) {
            $rolePermissions = $role->permissions->pluck('name')->toArray();
            $roleMatrix = [
                'role' => $this->formatRoleForBuilder($role),
                'groups' => [],
            ];

            foreach ($structure as $group) {
                $groupData = [
                    'handle' => $group['handle'],
                    'label' => $group['label'],
                    'permissions' => [],
                    'all_granted' => true,
                    'none_granted' => true,
                ];

                foreach ($group['permissions'] as $permission) {
                    $granted = in_array($permission['handle'], $rolePermissions);
                    $groupData['permissions'][] = [
                        'handle' => $permission['handle'],
                        'label' => $permission['label'],
                        'granted' => $granted,
                    ];

                    if ($granted) {
                        $groupData['none_granted'] = false;
                    } else {
                        $groupData['all_granted'] = false;
                    }
                }

                $roleMatrix['groups'][] = $groupData;
            }

            $matrix[] = $roleMatrix;
        }

        return $matrix;
    }

    private function getPermissionTemplates(): array
    {
        return [
            'content_manager' => [
                'label' => 'Content Manager',
                'description' => 'Gestisce contenuti e pubblicazioni',
                'permissions' => [
                    'view content',
                    'create content',
                    'edit content',
                    'delete content',
                    'publish content',
                    'view customers',
                    'view users',
                ],
            ],
            'shop_manager' => [
                'label' => 'Shop Manager',
                'description' => 'Gestisce prodotti, ordini e inventario',
                'permissions' => [
                    'view products',
                    'create products',
                    'edit products',
                    'delete products',
                    'manage inventory',
                    'view orders',
                    'create orders',
                    'edit orders',
                    'process orders',
                    'view customers',
                    'create customers',
                    'edit customers',
                ],
            ],
            'customer_service' => [
                'label' => 'Customer Service',
                'description' => 'Gestisce ordini e supporto clienti',
                'permissions' => [
                    'view orders',
                    'edit orders',
                    'process orders',
                    'view customers',
                    'edit customers',
                    'view products',
                ],
            ],
            'read_only' => [
                'label' => 'Read Only',
                'description' => 'Solo visualizzazione',
                'permissions' => [
                    'view content',
                    'view products',
                    'view orders',
                    'view customers',
                ],
            ],
            'super_admin' => [
                'label' => 'Super Administrator',
                'description' => 'Accesso completo',
                'permissions' => ['super'],
            ],
        ];
    }

    private function determineGroupFromResource(string $resource): string
    {
        $resourceGroups = [
            'content' => ['posts', 'pages', 'articles', 'blogs'],
            'commerce' => ['products', 'orders', 'inventory', 'payments'],
            'customers' => ['customers', 'customer_groups'],
            'users' => ['users', 'staff'],
            'system' => ['settings', 'configurations', 'permissions'],
        ];

        foreach ($resourceGroups as $group => $resources) {
            if (in_array($resource, $resources)) {
                return $group;
            }
        }

        return 'content'; // Default group
    }
}
