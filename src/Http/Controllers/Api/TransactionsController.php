<?php

declare(strict_types=1);

namespace Cartino\Http\Controllers\Api;

use Cartino\Http\Requests\Api\StoreTransactionRequest;
use Cartino\Http\Requests\Api\UpdateTransactionRequest;
use Cartino\Http\Resources\TransactionResource;
use Cartino\Models\Transaction;
use Cartino\Repositories\TransactionRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransactionsController extends ApiController
{
    public function __construct(
        private readonly TransactionRepository $repository,
    ) {}

    /**
     * Display a listing of transactions
     */
    public function index(Request $request): JsonResponse
    {
        $data = $this->repository->findAll($request->all());

        return $this->paginatedResponse($data);
    }

    /**
     * Display the specified transaction
     */
    public function show(int|string $transactionId): JsonResponse
    {
        $data = $this->repository->findOne($transactionId);

        return $this->successResponse(new TransactionResource($data));
    }

    /**
     * Store a newly created transaction
     */
    public function store(StoreTransactionRequest $request): JsonResponse
    {
        try {
            $transaction = $this->repository->createOne($request->validated());

            return $this->created(new TransactionResource($transaction), 'Transaction creata con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nella creazione della transaction: '.$e->getMessage());
        }
    }

    /**
     * Update the specified transaction
     */
    public function update(UpdateTransactionRequest $request, Transaction $transaction): JsonResponse
    {
        try {
            $updatedTransaction = $this->repository->updateOne($transaction->id, $request->validated());

            return $this->successResponse(
                new TransactionResource($updatedTransaction),
                'Transaction aggiornata con successo',
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nell\'aggiornamento della transaction: '.$e->getMessage());
        }
    }

    /**
     * Remove the specified transaction
     */
    public function destroy(Transaction $transaction): JsonResponse
    {
        try {
            $this->repository->deleteOne($transaction->id);

            return $this->successResponse(null, 'Transaction eliminata con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nell\'eliminazione della transaction: '.$e->getMessage());
        }
    }
}
