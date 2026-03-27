<?php

namespace Cartino\Repositories;

use Cartino\Models\Customer;
use Cartino\Models\CustomerGroup;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class CustomerRepository extends BaseRepository
{
    protected array $with = ['customerGroup', 'orders', 'fidelityCard'];

    protected string $cachePrefix = 'customers';

    protected function makeModel(): Model
    {
        return new Customer;
    }

    /**
     * Get paginated customers with filters and search
     */
    public function findAll(array $filters = []): LengthAwarePaginator
    {
        $perPage = $filters['per_page'] ?? config('settings.pagination.per_page', 15);

        return QueryBuilder::for(Customer::class)
            ->select('customers.*')
            ->with([
                'customerGroup:id,name,discount_percentage',
                'fidelityCard:id,customer_id,card_number,points',
            ])
            ->withCount('orders')
            ->allowedFilters([
                'first_name',
                'last_name',
                'email',
                'phone_number',
                AllowedFilter::exact('customer_group_id'),
                AllowedFilter::exact('is_active'),
            ])
            ->allowedSorts(['first_name', 'last_name', 'email', 'created_at'])
            ->allowedIncludes(['customerGroup', 'fidelityCard', 'orders', 'addresses'])
            ->defaultSort('-created_at')
            ->paginate($perPage)
            ->appends($filters);
    }

    /**
     * Find one by ID or email
     */
    public function findOne(int|string $emailOrId): ?Customer
    {
        $cacheKey = "customer:{$emailOrId}";

        return $this->cacheQuery($cacheKey, function () use ($emailOrId) {
            return $this->model
                ->with(['customerGroup', 'fidelityCard', 'addresses'])
                ->withCount('orders')
                ->where('id', $emailOrId)
                ->orWhere('email', $emailOrId)
                ->firstOrFail();
        });
    }

    /**
     * Create a new customer
     */
    public function createOne(array $data): Customer
    {
        $customer = $this->model->create($data);
        $this->clearModelCache();

        return $customer;
    }

    /**
     * Update customer
     */
    public function updateOne(int $id, array $data): Customer
    {
        $customer = $this->findOrFail($id);
        $customer->update($data);
        $this->clearModelCache();

        return $customer->fresh(['customerGroup', 'fidelityCard']);
    }

    /**
     * Delete customer
     */
    public function deleteOne(int $id): bool
    {
        $customer = $this->findOrFail($id);
        $deleted = $customer->delete();
        $this->clearCache();

        return $deleted;
    }

    /**
     * Check if can delete
     */
    public function canDelete(int $id): bool
    {
        $customer = $this->findOrFail($id);

        return ! $customer->orders()->exists();
    }

    /**
     * Toggle customer active status
     */
    public function toggleStatus(int $id): Customer
    {
        $customer = $this->findOrFail($id);
        $customer->update(['is_active' => ! $customer->is_active]);
        $this->clearCache();

        return $customer->fresh();
    }

    /**
     * Get customer groups for filters
     */
    public function getCustomerGroups(): Collection
    {
        return CustomerGroup::select('id', 'name')->orderBy('name')->get();
    }

    /**
     * Find customer with fidelity card details
     */
    public function findWithFidelityCard(int $id): ?Customer
    {
        return $this->model->with(['fidelityCard.transactions' => function ($query) {
            $query->orderBy('created_at', 'desc')->limit(10);
        }])->find($id);
    }

    /**
     * Get customers with fidelity statistics
     */
    public function getWithFidelityStats(array $filters = []): LengthAwarePaginator
    {
        $query = $this->model
            ->newQuery()
            ->with(['customerGroup', 'fidelityCard'])
            ->withCount(['orders'])
            ->withSum('orders', 'total_amount');

        // Aggiungi filtri per fidelity card
        if (! empty($filters['has_fidelity_card'])) {
            if ($filters['has_fidelity_card'] === 'yes') {
                $query->whereHas('fidelityCard');
            } elseif ($filters['has_fidelity_card'] === 'no') {
                $query->whereDoesntHave('fidelityCard');
            }
        }

        if (! empty($filters['fidelity_tier'])) {
            $query->whereHas('fidelityCard', function ($q) use ($filters) {
                $tier = $filters['fidelity_tier'];
                switch ($tier) {
                    case 'bronze':
                        $q->where('total_spent_amount', '<', 100);
                        break;
                    case 'silver':
                        $q->whereBetween('total_spent_amount', [100, 499.99]);
                        break;
                    case 'gold':
                        $q->whereBetween('total_spent_amount', [500, 999.99]);
                        break;
                    case 'platinum':
                        $q->where('total_spent_amount', '>=', 1000);
                        break;
                }
            });
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q
                    ->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('fidelityCard', function ($fq) use ($search) {
                        $fq->where('card_number', 'like', "%{$search}%");
                    });
            });
        }

        return $query->paginate($perPage);
    }

    /**
     * Get customer fidelity statistics
     */
    public function getFidelityStatistics(int $id): array
    {
        $customer = $this->findWithFidelityCard($id);

        if (! $customer || ! $customer->fidelityCard) {
            return [
                'has_fidelity_card' => false,
                'card_number' => null,
                'points' => 0,
                'tier' => null,
                'recent_transactions' => [],
            ];
        }

        $card = $customer->fidelityCard;

        return [
            'has_fidelity_card' => true,
            'card_number' => $card->card_number,
            'points' => [
                'total' => $card->total_points,
                'available' => $card->available_points,
                'redeemed' => $card->total_redeemed,
            ],
            'spending' => [
                'total' => $card->total_spent_amount,
                'tier' => $card->getCurrentTier(),
                'next_tier' => $card->getNextTier(),
            ],
            'card_status' => $card->is_active ? 'active' : 'inactive',
            'issued_at' => $card->issued_at,
            'last_activity' => $card->last_activity_at,
            'recent_transactions' => $card
                ->transactions()
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($transaction) {
                    return [
                        'id' => $transaction->id,
                        'type' => $transaction->type,
                        'points' => $transaction->points,
                        'description' => $transaction->description,
                        'created_at' => $transaction->created_at,
                    ];
                }),
        ];
    }

    /**
     * Find customer with relations
     */
    public function findWithRelations(int $id, array $relations = []): ?Customer
    {
        $query = $this->model->with($relations);

        return $query->find($id);
    }

    /**
     * Bulk update customers
     */
    public function bulkUpdate(array $ids, array $data): int
    {
        $this->clearCache();

        return $this->model->whereIn('id', $ids)->update($data);
    }

    /**
     * Bulk delete customers
     */
    public function bulkDelete(array $ids): int
    {
        $this->clearCache();
        $count = 0;

        foreach ($ids as $id) {
            if ($this->canDelete($id)) {
                $this->model->find($id)->delete();
                $count++;
            }
        }

        return $count;
    }

    /**
     * Bulk export customers
     */
    public function bulkExport(array $ids): int
    {
        // TODO: Implement actual export logic
        return count($ids);
    }

    /**
     * Find customer with orders relationship
     */
    public function findWithOrders(int $id): ?Customer
    {
        $cacheKey = $this->getCacheKey('with_orders', $id);

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($id) {
            return $this->model->with(['orders', 'orders.items', 'orders.payments'])->find($id);
        });
    }

    /**
     * Get customer orders with filters
     */
    public function getOrders(int $customerId, array $filters = []): LengthAwarePaginator
    {
        $customer = $this->model->find($customerId);

        if (! $customer) {
            return new LengthAwarePaginator([], 0, $perPage);
        }

        $query = $customer->orders()->with(['items', 'payments', 'shippingAddress']);

        // Status filter
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Date range filter
        if (! empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        // Sorting
        $sortField = $filters['sort'] ?? 'created_at';
        $sortDirection = $filters['direction'] ?? 'desc';
        $query->orderBy($sortField, $sortDirection);

        return $query->paginate($perPage);
    }

    /**
     * Get customer addresses
     */
    public function getAddresses(int $customerId): Collection
    {
        $cacheKey = $this->getCacheKey('addresses', $customerId);

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($customerId) {
            $customer = $this->model->find($customerId);

            return $customer ? $customer->addresses : collect();
        });
    }

    /**
     * Add address to customer
     */
    public function addAddress(int $customerId, array $addressData): ?Model
    {
        $customer = $this->model->find($customerId);

        if (! $customer) {
            return null;
        }

        // If this is the first address or is_default is true, make it default
        if ($customer->addresses()->count() === 0 || ($addressData['is_default'] ?? false)) {
            $customer->addresses()->update(['is_default' => false]);
            $addressData['is_default'] = true;
        }

        $address = $customer->addresses()->create($addressData);
        $this->clearCache();

        return $address;
    }

    /**
     * Get customer statistics
     */
    public function getStatistics(int $customerId): array
    {
        $cacheKey = $this->getCacheKey('statistics', $customerId);

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($customerId) {
            $customer = $this->model->with(['orders'])->find($customerId);

            if (! $customer) {
                return [];
            }

            $orders = $customer->orders;
            $totalSpent = $orders->sum('total_amount');
            $averageOrderValue = $orders->count() > 0 ? ($totalSpent / $orders->count()) : 0;

            return [
                'total_orders' => $orders->count(),
                'total_spent' => $totalSpent,
                'average_order_value' => round($averageOrderValue, 2),
                'first_order_date' => $orders->min('created_at'),
                'last_order_date' => $orders->max('created_at'),
                'loyalty_points' => $customer->fidelityCard?->points ?? 0,
            ];
        });
    }

    /**
     * Bulk action for customers
     */
    public function bulkAction(string $action, array $ids, array $metadata = []): array
    {
        $validatedIds = $this->model
            ->whereIn('id', $ids)
            ->pluck('id')
            ->toArray();
        $processedCount = 0;
        $errors = [];

        foreach ($validatedIds as $id) {
            try {
                switch ($action) {
                    case 'activate':
                        $this->model->where('id', $id)->update(['is_active' => true]);
                        $processedCount++;
                        break;

                    case 'deactivate':
                        $this->model->where('id', $id)->update(['is_active' => false]);
                        $processedCount++;
                        break;

                    case 'delete':
                        if ($this->canDelete($id)) {
                            $this->model->find($id)->delete();
                            $processedCount++;
                        } else {
                            $errors[] = "Customer ID {$id}: Cannot delete customer with orders";
                        }
                        break;

                    case 'add_to_group':
                        if (isset($metadata['group_id'])) {
                            $this->model->where('id', $id)->update(['customer_group_id' => $metadata['group_id']]);
                            $processedCount++;
                        } else {
                            $errors[] = "Customer ID {$id}: Missing group_id";
                        }
                        break;

                    case 'export':
                        $processedCount++;
                        break;

                    default:
                        $errors[] = "Customer ID {$id}: Unknown action '{$action}'";
                }
            } catch (\Exception $e) {
                $errors[] = "Customer ID {$id}: {$e->getMessage()}";
            }
        }

        $this->clearCache();

        return [
            'processed' => $processedCount,
            'total' => count($ids),
            'errors' => $errors,
            'success' => count($errors) === 0,
        ];
    }

    /**
     * Clear repository cache
     */
    protected function clearCache(): void
    {
        $tags = [$this->cachePrefix];
        Cache::tags($tags)->flush();
    }
}
