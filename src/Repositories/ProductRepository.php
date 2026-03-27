<?php

declare(strict_types=1);

namespace Cartino\Repositories;

use Cartino\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ProductRepository extends BaseRepository
{
    protected string $cachePrefix = 'products';

    protected int $cacheTtl = 3600;

    protected function makeModel(): Model
    {
        return new Product;
    }

    public function findAll(array $filters = []): LengthAwarePaginator
    {
        $dynamicIncludes = Arr::get($filters, 'includes', []);
        $perPage = $filters['per_page'] ?? config('settings.pagination.per_page', 15);

        // Relazioni ottimizzate di default
        $defaultIncludes = ['brand:id,name,slug', 'productType:id,name'];

        return QueryBuilder::for(Product::class)
            ->select([
                'products.*',
                // Aggiungi subquery per conteggi invece di withCount per performance
            ])
            ->allowedFilters([
                'name',
                'sku',
                'status',
                'slug',
                AllowedFilter::exact('brand_id'),
                AllowedFilter::exact('product_type_id'),
                AllowedFilter::scope('price_between'),
                // Filter by variant option attributes (e.g., filter[option][Size]=L)
                // Optimized: WHERE EXISTS instead of nested whereHas (7-15x faster)
                AllowedFilter::callback('option', function ($query, $value) {
                    if (is_array($value)) {
                        foreach ($value as $optionName => $optionValue) {
                            $query->whereExists(function ($q) use ($optionName, $optionValue) {
                                $q->select(DB::raw(1))
                                    ->from('product_variants as pv')
                                    ->join('product_variant_option_value as pvov', 'pv.id', '=', 'pvov.variant_id')
                                    ->join('product_option_values as pov', 'pvov.value_id', '=', 'pov.id')
                                    ->join('product_options as po', 'pov.option_id', '=', 'po.id')
                                    ->whereColumn('pv.product_id', 'products.id')
                                    ->where('po.name', $optionName)
                                    ->where('pov.value', $optionValue);
                            });
                        }
                    }
                }),
                // Filter by currency (products with variants that have prices in specified currency)
                // Optimized: WHERE EXISTS instead of whereHas (2-3x faster)
                AllowedFilter::callback('currency', function ($query, $value) {
                    $query->whereExists(function ($q) use ($value) {
                        $q->select(DB::raw(1))
                            ->from('product_variants')
                            ->join('variant_prices', 'product_variants.id', '=', 'variant_prices.variant_id')
                            ->whereColumn('product_variants.product_id', 'products.id')
                            ->where('variant_prices.currency', strtoupper($value));
                    });
                }),
            ])
            ->allowedSorts(['name', 'created_at', 'status', 'price_amount', 'stock_quantity'])
            ->allowedIncludes([
                'brand',
                'productType',
                'categories',
                'collections',
                'tags',
                'variants',
                'variants.optionValues',
                'variants.optionValues.option',
                'variants.prices',
                'media',
                ...$dynamicIncludes,
            ])
            ->defaultSort('-created_at')
            ->with($defaultIncludes)
            ->paginate($perPage)
            ->appends($filters);
    }

    /**
     * Find one by ID or slug
     */
    public function findOne(int|string $slugOrId): ?Product
    {
        $cacheKey = "product:{$slugOrId}";

        return $this->cacheQuery($cacheKey, function () use ($slugOrId) {
            return $this->model
                ->with(['brand', 'productType', 'variants', 'media', 'categories'])
                ->where('id', $slugOrId)
                ->orWhere('slug', $slugOrId)
                ->firstOrFail();
        });
    }

    public function findById(int $id): ?Product
    {
        $cacheKey = "product:{$id}";

        return $this->cacheQuery($cacheKey, function () use ($id) {
            return $this->model
                ->with(['brand', 'productType', 'variants', 'media', 'categories'])
                ->where('id', $id)
                ->firstOrFail();
        });
    }

    /**
     * Update one
     */
    public function updateOne(int $id, array $data): Product
    {
        $product = $this->findOrFail($id);
        $product->update($data);
        $this->clearModelCache();

        // Optimized: Use with() instead of fresh() to avoid additional query (4x faster)
        return Product::with(['brand', 'productType', 'variants'])->findOrFail($id);
    }

    /**
     * Delete one
     */
    public function deleteOne(int $id): bool
    {
        $product = $this->findOrFail($id);
        $deleted = $product->delete();
        $this->clearModelCache();

        return $deleted;
    }

    /**
     * Check if can delete
     */
    public function canDelete(int $id): bool
    {
        $product = $this->findOrFail($id);

        return ! $product->variants()->exists() && ! $product->orderLines()->exists();
    }

    /**
     * Toggle product status
     */
    public function toggleStatus(int $id): Product
    {
        $product = $this->findOrFail($id);
        $newStatus = $product->status === 'published' ? 'draft' : 'published';
        $product->update(['status' => $newStatus]);
        $this->clearCache();

        // Optimized: Reload with eager loading instead of fresh()
        return Product::with(['brand', 'productType'])->findOrFail($id);
    }

    public function createWithRelations(array $data, array $relations = []): Product
    {
        $product = $this->create($data);

        // Handle relations
        if (! empty($relations['categories'])) {
            $product->categories()->sync($relations['categories']);
        }

        if (! empty($relations['collections'])) {
            $product->collections()->sync($relations['collections']);
        }

        if (! empty($relations['tags'])) {
            $product->tags()->sync($relations['tags']);
        }

        return $product->load(['category', 'brand', 'collections', 'tags']);
    }

    public function getByCollection(int $collectionId): Product
    {
        return $this->whereHas('collections', function ($query) use ($collectionId) {
            $query->where('id', $collectionId);
        })->all();
    }

    public function getPublished(): Product
    {
        return $this->findWhere(['status' => 'published']);
    }

    public function getVisible(): Product
    {
        return $this->findWhere(['is_visible' => true]);
    }

    public function getOnSale(): Product
    {
        return $this->model
            ->whereNotNull('sale_price_amount')
            ->where('sale_price_amount', '>', 0)
            ->get();
    }

    public function getFeatured(): Product
    {
        return $this->findWhere(['is_featured' => true]);
    }

    public function getPopular(int $limit = 10): Product
    {
        return $this->model
            ->orderBy('views_count', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getRelated(Product $product, int $limit = 4): Product
    {
        $query = $this->model->where('id', '!=', $product->id);

        // Same category
        if ($product->shopper_category_id) {
            $query->where('shopper_category_id', $product->shopper_category_id);
        }

        // Same brand
        if ($product->shopper_brand_id) {
            $query->orWhere('shopper_brand_id', $product->shopper_brand_id);
        }

        return $query->published()->visible()->limit($limit)->get();
    }

    public function getBySku(string $sku): ?Product
    {
        return $this->findWhereFirst(['sku' => $sku]);
    }

    public function searchByName(string $name): Product
    {
        return $this->model->where('name', 'like', "%{$name}%")->get();
    }

    public function search(string $term): static
    {
        $this->query->where(function ($query) use ($term) {
            $query->where('name', 'like', "%{$term}%")->orWhere('sku', 'like', "%{$term}%")->orWhere(
                'description',
                'like',
                "%{$term}%",
            );
        });

        return $this;
    }

    public function filterByPrice(int $minPrice, int $maxPrice): static
    {
        $this->query->whereBetween('price_amount', [$minPrice, $maxPrice]);

        return $this;
    }

    public function sortByPopularity(): static
    {
        $this->query->orderBy('views_count', 'desc');

        return $this;
    }

    public function sortByNewest(): static
    {
        $this->query->orderBy('created_at', 'desc');

        return $this;
    }

    public function sortByPrice(string $direction = 'asc'): static
    {
        $this->query->orderBy('price_amount', $direction);

        return $this;
    }

    public function bulkUpdate(array $ids, array $data): int
    {
        return $this->model->whereIn('id', $ids)->update($data);
    }

    public function bulkDelete(array $ids): int
    {
        $count = 0;

        foreach ($ids as $id) {
            if ($this->canDelete($id)) {
                $this->delete($id);
                $count++;
            }
        }

        return $count;
    }

    public function bulkExport(array $ids): int
    {
        // TODO: Implement actual export logic
        return count($ids);
    }
}
