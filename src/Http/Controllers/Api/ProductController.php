<?php

namespace Cartino\Http\Controllers\Api;

use Cartino\Http\Resources\ProductResource;
use Cartino\Models\Product;
use Cartino\Repositories\ProductRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductController extends ApiController
{
    public function __construct(
        private readonly ProductRepository $repository,
    ) {}

    /**
     * Display a listing of brands
     */
    public function index(Request $request): JsonResponse
    {
        $request = $request->all();

        $data = $this->repository->findAll($request);

        return $this->paginatedResponse($data);
    }

    public function show(Product $product): JsonResource
    {
        $product->load(['brand', 'productType', 'variants', 'media']);

        return new ProductResource($product);
    }

    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2',
        ]);

        $params = $this->filterService->parseRequest($request->all());
        $params['search'] = $request->get('q');

        $products = Product::where('status', 'published')->paginateFilter($params);

        return $this->success([
            'data' => ProductResource::collection($products->items()),
            'meta' => [
                'total' => $products->total(),
                'query' => $request->get('q'),
            ],
            'filters' => $params,
        ]);
    }

    public function featured(Request $request): JsonResponse
    {
        $params = $this->filterService->parseRequest($request->all());
        $params['is_featured'] = true;

        $products = Product::where('status', 'published')->paginateFilter($params, 12);

        return $this->success([
            'data' => ProductResource::collection($products->items()),
            'meta' => [
                'total' => $products->total(),
            ],
        ]);
    }

    public function popular(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 10);

        $products = Product::where('status', 'published')
            ->orderBy('average_rating', 'desc')
            ->orderBy('review_count', 'desc')
            ->limit($limit)
            ->get();

        return $this->success([
            'data' => ProductResource::collection($products),
        ]);
    }

    public function onSale(Request $request): JsonResponse
    {
        $products = Product::where('status', 'published')
            ->whereColumn('compare_price', '>', 'price')
            ->whereNotNull('compare_price')
            ->limit(12)
            ->get();

        return $this->success([
            'data' => ProductResource::collection($products),
        ]);
    }

    public function related(Product $product, Request $request): JsonResponse
    {
        $limit = $request->get('limit', 6);

        // Get related products by category or brand
        $related = Product::where('status', 'published')
            ->where('id', '!=', $product->id)
            ->where(function ($query) use ($product) {
                $query->where('brand_id', $product->brand_id)->orWhere('product_type_id', $product->product_type_id);
            })
            ->limit($limit)
            ->get();

        return $this->success([
            'data' => ProductResource::collection($related),
        ]);
    }
}
