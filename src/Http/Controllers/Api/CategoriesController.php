<?php

namespace Cartino\Http\Controllers\Api;

use Cartino\Http\Resources\CategoryResource;
use Cartino\Repositories\CategoryRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoriesController extends ApiController
{
    public function __construct(
        protected CategoryRepository $repository,
    ) {}

    /**
     * Display a listing of categories
     */
    public function index(Request $request): JsonResponse
    {
        $request = $request->all();

        $data = $this->repository->findAll($request);

        return $this->paginatedResponse($data);
    }

    /**
     * Get category tree (root categories with nested children)
     */
    public function tree(): JsonResponse
    {
        $tree = $this->repository->getTree();

        return response()->json([
            'data' => CategoryResource::collection($tree),
        ]);
    }

    /**
     * Get root categories only (with direct children count)
     */
    public function root(): JsonResponse
    {
        $rootCategories = $this->repository->getRootCategories();

        return response()->json([
            'data' => CategoryResource::collection($rootCategories),
        ]);
    }

    /**
     * Store a newly created collection
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:collections,slug',
            'description' => 'nullable|string',
            'type' => 'required|in:manual,automatic',
            'conditions' => 'nullable|array',
            'image' => 'nullable|string',
            'is_published' => 'boolean',
            'published_at' => 'nullable|date',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:160',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        try {
            $collection = $this->repository->create($validated);

            return response()->json([
                'message' => 'Collezione creata con successo',
                'data' => $collection,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Errore durante la creazione della collezione',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified collection
     */
    public function show(string $id): JsonResponse
    {
        try {
            $collection = $this->repository->findWithProducts($id);

            return response()->json([
                'data' => $collection,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Collezione non trovata',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Update the specified collection
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:collections,slug,'.$id,
            'description' => 'nullable|string',
            'type' => 'required|in:manual,automatic',
            'conditions' => 'nullable|array',
            'image' => 'nullable|string',
            'is_published' => 'boolean',
            'published_at' => 'nullable|date',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:160',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        try {
            $collection = $this->repository->update($id, $validated);

            return response()->json([
                'message' => 'Collezione aggiornata con successo',
                'data' => $collection,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Errore durante l\'aggiornamento della collezione',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified collection
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $this->repository->delete($id);

            return response()->json([
                'message' => 'Collezione eliminata con successo',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Errore durante l\'eliminazione della collezione',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get collection products
     */
    public function products(Request $request, string $id): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 25);
            $products = $this->repository->getProducts($id, $perPage);

            return response()->json([
                'data' => $products->items(),
                'meta' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Errore durante il recupero dei prodotti',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Add products to collection
     */
    public function addProducts(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'integer|exists:products,id',
        ]);

        try {
            $result = $this->repository->addProducts($id, $validated['product_ids']);

            return response()->json([
                'message' => "Aggiunti {$result['added']} prodotti alla collezione",
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Errore durante l\'aggiunta dei prodotti',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove products from collection
     */
    public function removeProducts(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'integer|exists:products,id',
        ]);

        try {
            $result = $this->repository->removeProducts($id, $validated['product_ids']);

            return response()->json([
                'message' => "Rimossi {$result['removed']} prodotti dalla collezione",
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Errore durante la rimozione dei prodotti',
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
            'action' => 'required|in:delete,publish,unpublish',
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:collections,id',
        ]);

        try {
            $result = $this->repository->bulkAction($validated['action'], $validated['ids']);

            return response()->json([
                'message' => "Azione '{$validated['action']}' eseguita su {$result['count']} collezioni",
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Errore durante l\'esecuzione dell\'azione bulk',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
