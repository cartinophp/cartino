<?php

declare(strict_types=1);

namespace Cartino\Http\Controllers\Api;

use Cartino\Http\Requests\Api\StoreProductReviewRequest;
use Cartino\Http\Requests\Api\UpdateProductReviewRequest;
use Cartino\Http\Resources\ProductReviewResource;
use Cartino\Models\ProductReview;
use Cartino\Repositories\ProductReviewRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductReviewsController extends ApiController
{
    public function __construct(
        private readonly ProductReviewRepository $repository,
    ) {}

    /**
     * Display a listing of product reviews
     */
    public function index(Request $request): JsonResponse
    {
        $data = $this->repository->findAll($request->all());

        return $this->paginatedResponse($data);
    }

    /**
     * Display the specified product review
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->repository->findOne($id);

        return $this->successResponse(new ProductReviewResource($data));
    }

    /**
     * Store a newly created product review
     */
    public function store(StoreProductReviewRequest $request): JsonResponse
    {
        try {
            $review = $this->repository->createOne($request->validated());

            return $this->created(new ProductReviewResource($review), 'Review creata con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nella creazione della review: '.$e->getMessage());
        }
    }

    /**
     * Update the specified product review
     */
    public function update(UpdateProductReviewRequest $request, ProductReview $productReview): JsonResponse
    {
        try {
            $updatedReview = $this->repository->updateOne($productReview->id, $request->validated());

            return $this->successResponse(new ProductReviewResource($updatedReview), 'Review aggiornata con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nell\'aggiornamento della review: '.$e->getMessage());
        }
    }

    /**
     * Remove the specified product review
     */
    public function destroy(ProductReview $productReview): JsonResponse
    {
        try {
            $this->repository->deleteOne($productReview->id);

            return $this->successResponse(null, 'Review eliminata con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nell\'eliminazione della review: '.$e->getMessage());
        }
    }

    /**
     * Approve product review
     */
    public function approve(ProductReview $productReview): JsonResponse
    {
        try {
            $approvedReview = $this->repository->approveReview($productReview->id);

            return $this->successResponse(new ProductReviewResource($approvedReview), 'Review approvata con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nell\'approvazione della review: '.$e->getMessage());
        }
    }

    /**
     * Reject product review
     */
    public function reject(ProductReview $productReview): JsonResponse
    {
        try {
            $rejectedReview = $this->repository->rejectReview($productReview->id);

            return $this->successResponse(new ProductReviewResource($rejectedReview), 'Review rifiutata con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nel rifiuto della review: '.$e->getMessage());
        }
    }
}
