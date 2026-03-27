<?php

namespace Cartino\Http\Controllers\Cp;

use Cartino\Cp\Page;
use Cartino\Http\Controllers\Controller;
use Cartino\Models\Product;
use Cartino\Models\ProductReview;
use Cartino\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class ReviewsController extends Controller
{
    use ApiResponseTrait;

    /**
     * Reviews index page
     */
    public function index(Request $request)
    {
        $page = Page::make('Product Reviews')
            ->breadcrumb('Home', '/cp')
            ->breadcrumb('Products', '/cp/products')
            ->breadcrumb('Reviews')
            ->secondaryActions([
                ['label' => 'Export Reviews', 'url' => '/cp/reviews/export'],
                ['label' => 'Analytics', 'url' => '/cp/reviews/analytics'],
            ]);

        // If AJAX request, return data for Vue component
        if ($request->expectsJson()) {
            return $this->getReviewsData($request);
        }

        return Inertia::render('products/Reviews', [
            'page' => $page->compile(),
        ]);
    }

    /**
     * Get reviews data for AJAX requests
     */
    private function getReviewsData(Request $request): JsonResponse
    {
        $query = ProductReview::with([
            'customer:id,name,email',
            'product:id,name,handle',
            'reviewMedia',
        ]);

        // Search filter
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('product', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Product filter
        if ($productId = $request->get('product_id')) {
            $query->where('product_id', $productId);
        }

        // Rating filter
        if ($rating = $request->get('rating')) {
            $query->where('rating', $rating);
        }

        // Approval status filter
        if ($request->has('is_approved')) {
            $query->where('is_approved', $request->boolean('is_approved'));
        }

        // Verified purchase filter
        if ($request->has('is_verified_purchase')) {
            $query->where('is_verified_purchase', $request->boolean('is_verified_purchase'));
        }

        // Featured filter
        if ($request->has('is_featured')) {
            $query->where('is_featured', $request->boolean('is_featured'));
        }

        // Date range filter
        if ($from = $request->get('date_from')) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = $request->get('date_to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');

        $allowedSorts = [
            'created_at',
            'rating',
            'helpful_count',
            'unhelpful_count',
            'title',
            'is_approved',
            'is_featured',
        ];

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDirection);
        }

        // Pagination
        $perPage = min($request->get('per_page', 20), 100);
        $reviews = $query->paginate($perPage);

        return $this->successResponse([
            'data' => $reviews->items(),
            'meta' => [
                'current_page' => $reviews->currentPage(),
                'per_page' => $reviews->perPage(),
                'total' => $reviews->total(),
                'last_page' => $reviews->lastPage(),
            ],
            'links' => [
                'first' => $reviews->url(1),
                'last' => $reviews->url($reviews->lastPage()),
                'prev' => $reviews->previousPageUrl(),
                'next' => $reviews->nextPageUrl(),
            ],
        ]);
    }

    /**
     * Store a newly created review
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer|exists:products,id',
            'customer_id' => 'required|integer|exists:customers,id',
            'order_id' => 'nullable|integer|exists:orders,id',
            'order_line_id' => 'nullable|integer|exists:order_lines,id',
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:2000',
            'is_verified_purchase' => 'boolean',
            'is_approved' => 'boolean',
            'is_featured' => 'boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 422);
        }

        try {
            DB::beginTransaction();

            $review = ProductReview::create([
                'product_id' => $request->product_id,
                'customer_id' => $request->customer_id,
                'order_id' => $request->order_id,
                'order_line_id' => $request->order_line_id,
                'rating' => $request->rating,
                'title' => $request->title,
                'content' => $request->content,
                'is_verified_purchase' => $request->boolean('is_verified_purchase', false),
                'is_approved' => $request->boolean('is_approved', false),
                'is_featured' => $request->boolean('is_featured', false),
            ]);

            // Update product average rating
            $this->updateProductRating($request->product_id);

            DB::commit();

            $review->load(['customer', 'product', 'reviewMedia']);

            return $this->successResponse([
                'message' => 'Review created successfully',
                'data' => $review,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->errorResponse('Failed to create review: '.$e->getMessage(), 500);
        }
    }

    /**
     * Display the specified review
     */
    public function show(ProductReview $review): JsonResponse
    {
        $review->load([
            'customer:id,name,email',
            'product:id,name,handle',
            'reviewMedia',
            'votes',
        ]);

        return $this->successResponse([
            'data' => $review,
        ]);
    }

    /**
     * Update the specified review
     */
    public function update(Request $request, ProductReview $review): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'rating' => 'sometimes|integer|min:1|max:5',
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string|max:2000',
            'is_approved' => 'sometimes|boolean',
            'is_featured' => 'sometimes|boolean',
            'is_verified_purchase' => 'sometimes|boolean',
            'reply_content' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 422);
        }

        try {
            DB::beginTransaction();

            $originalRating = $review->rating;
            $updateData = $request->only([
                'rating',
                'title',
                'content',
                'is_approved',
                'is_featured',
                'is_verified_purchase',
            ]);

            // Handle reply
            if ($request->has('reply_content')) {
                $updateData['reply_content'] = $request->reply_content;

                if ($request->reply_content && ! $review->replied_at) {
                    $updateData['replied_at'] = now();
                    $updateData['replied_by'] = Auth::id();
                } elseif (! $request->reply_content) {
                    $updateData['replied_at'] = null;
                    $updateData['replied_by'] = null;
                }
            }

            $review->update($updateData);

            // Update product average rating if rating changed
            if (isset($updateData['rating']) && $updateData['rating'] !== $originalRating) {
                $this->updateProductRating($review->product_id);
            }

            DB::commit();

            $review->load(['customer', 'product', 'reviewMedia']);

            return $this->successResponse([
                'message' => 'Review updated successfully',
                'data' => $review,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->errorResponse('Failed to update review: '.$e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified review
     */
    public function destroy(ProductReview $review): JsonResponse
    {
        try {
            DB::beginTransaction();

            $productId = $review->product_id;
            $review->delete();

            // Update product average rating
            $this->updateProductRating($productId);

            DB::commit();

            return $this->successResponse([
                'message' => 'Review deleted successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->errorResponse('Failed to delete review: '.$e->getMessage(), 500);
        }
    }

    /**
     * Approve a review
     */
    public function approve(ProductReview $review): JsonResponse
    {
        try {
            $review->update(['is_approved' => true]);

            return $this->successResponse([
                'message' => 'Review approved successfully',
                'data' => $review,
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to approve review: '.$e->getMessage(), 500);
        }
    }

    /**
     * Unapprove a review
     */
    public function unapprove(ProductReview $review): JsonResponse
    {
        try {
            $review->update(['is_approved' => false]);

            return $this->successResponse([
                'message' => 'Review unapproved successfully',
                'data' => $review,
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to unapprove review: '.$e->getMessage(), 500);
        }
    }

    /**
     * Bulk approve reviews
     */
    public function bulkApprove(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'review_ids' => 'required|array|min:1',
            'review_ids.*' => 'integer|exists:product_reviews,id',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 422);
        }

        try {
            $updated = ProductReview::whereIn('id', $request->review_ids)->update(['is_approved' => true]);

            return $this->successResponse([
                'message' => "{$updated} reviews approved successfully",
                'updated_count' => $updated,
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to approve reviews: '.$e->getMessage(), 500);
        }
    }

    /**
     * Bulk delete reviews
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'review_ids' => 'required|array|min:1',
            'review_ids.*' => 'integer|exists:product_reviews,id',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 422);
        }

        try {
            DB::beginTransaction();

            // Get product IDs before deletion for rating updates
            $productIds = ProductReview::whereIn('id', $request->review_ids)->pluck('product_id')->unique();

            $deleted = ProductReview::whereIn('id', $request->review_ids)->delete();

            // Update product ratings
            foreach ($productIds as $productId) {
                $this->updateProductRating($productId);
            }

            DB::commit();

            return $this->successResponse([
                'message' => "{$deleted} reviews deleted successfully",
                'deleted_count' => $deleted,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->errorResponse('Failed to delete reviews: '.$e->getMessage(), 500);
        }
    }

    /**
     * Get review analytics
     */
    public function analytics(Request $request): JsonResponse
    {
        try {
            $baseQuery = ProductReview::query();

            // Apply date filter if provided
            if ($from = $request->get('date_from')) {
                $baseQuery->whereDate('created_at', '>=', $from);
            }

            if ($to = $request->get('date_to')) {
                $baseQuery->whereDate('created_at', '<=', $to);
            }

            // Basic stats
            $totalReviews = $baseQuery->count();
            $averageRating = $baseQuery->avg('rating');
            $pendingReviews = $baseQuery->where('is_approved', false)->count();
            $featuredReviews = $baseQuery->where('is_featured', true)->count();

            // Rating distribution
            $ratingDistribution = $baseQuery
                ->select('rating', DB::raw('count(*) as count'))
                ->groupBy('rating')
                ->orderBy('rating', 'desc')
                ->pluck('count', 'rating')
                ->toArray();

            // Fill missing ratings with 0
            for ($i = 1; $i <= 5; $i++) {
                if (! isset($ratingDistribution[$i])) {
                    $ratingDistribution[$i] = 0;
                }
            }
            ksort($ratingDistribution);

            // Reviews over time (last 30 days)
            $reviewsOverTime = $baseQuery
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->where('created_at', '>=', now()->subDays(30))
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->keyBy('date')
                ->map
                ->count;

            // Most reviewed products
            $topProducts = Product::withCount([
                'reviews' => function ($query) use ($request) {
                    if ($from = $request->get('date_from')) {
                        $query->whereDate('created_at', '>=', $from);
                    }
                    if ($to = $request->get('date_to')) {
                        $query->whereDate('created_at', '<=', $to);
                    }
                },
            ])
                ->having('reviews_count', '>', 0)
                ->orderBy('reviews_count', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'reviews_count' => $product->reviews_count,
                        'average_rating' => $product->reviews()->avg('rating'),
                    ];
                });

            return $this->successResponse([
                'data' => [
                    'total_reviews' => $totalReviews,
                    'average_rating' => round($averageRating, 2),
                    'pending_reviews' => $pendingReviews,
                    'featured_reviews' => $featuredReviews,
                    'rating_distribution' => $ratingDistribution,
                    'reviews_over_time' => $reviewsOverTime,
                    'top_products' => $topProducts,
                ],
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to load analytics: '.$e->getMessage(), 500);
        }
    }

    /**
     * Export reviews to CSV
     */
    public function export(Request $request)
    {
        try {
            $query = ProductReview::with(['customer', 'product']);

            // Apply same filters as index
            if ($search = $request->get('search')) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")->orWhere('content', 'like', "%{$search}%");
                });
            }

            if ($productId = $request->get('product_id')) {
                $query->where('product_id', $productId);
            }

            if ($rating = $request->get('rating')) {
                $query->where('rating', $rating);
            }

            if ($request->has('is_approved')) {
                $query->where('is_approved', $request->boolean('is_approved'));
            }

            $reviews = $query->orderBy('created_at', 'desc')->get();

            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="reviews-'.now()->format('Y-m-d').'.csv"',
            ];

            $callback = function () use ($reviews) {
                $file = fopen('php://output', 'w');

                // CSV headers
                fputcsv($file, [
                    'ID',
                    'Product',
                    'Customer',
                    'Rating',
                    'Title',
                    'Content',
                    'Approved',
                    'Featured',
                    'Verified Purchase',
                    'Helpful Count',
                    'Unhelpful Count',
                    'Created At',
                ]);

                // CSV data
                foreach ($reviews as $review) {
                    fputcsv($file, [
                        $review->id,
                        $review->product?->name ?? 'N/A',
                        $review->customer?->name ?? 'N/A',
                        $review->rating,
                        $review->title,
                        $review->content,
                        $review->is_approved ? 'Yes' : 'No',
                        $review->is_featured ? 'Yes' : 'No',
                        $review->is_verified_purchase ? 'Yes' : 'No',
                        $review->helpful_count,
                        $review->unhelpful_count,
                        $review->created_at->format('Y-m-d H:i:s'),
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to export reviews: '.$e->getMessage(), 500);
        }
    }

    /**
     * Update product average rating and review count
     */
    private function updateProductRating(int $productId): void
    {
        $product = Product::find($productId);
        if (! $product) {
            return;
        }

        $reviews = ProductReview::where('product_id', $productId)->where('is_approved', true);

        $averageRating = $reviews->avg('rating');
        $reviewCount = $reviews->count();

        $product->update([
            'average_rating' => $averageRating ? round($averageRating, 2) : null,
            'review_count' => $reviewCount,
        ]);
    }
}
