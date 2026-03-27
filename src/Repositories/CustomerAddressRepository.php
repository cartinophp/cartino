<?php

namespace Cartino\Repositories;

use Cartino\Models\CustomerAddress;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class CustomerAddressRepository extends BaseRepository
{
    protected string $cachePrefix = 'addresses';

    protected function makeModel(): Model
    {
        return new CustomerAddress;
    }

    /**
     * Get paginated addresses with filters
     */
    public function findAll(array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->newQuery()->with('customer');

        // Customer filter
        if (! empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        // Type filter
        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        // Country filter
        if (! empty($filters['country_code'])) {
            $query->where('country_code', $filters['country_code']);
        }

        // Search filter
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q
                    ->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('company', 'like', "%{$search}%")
                    ->orWhere('address_line_1', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('postal_code', 'like', "%{$search}%");
            });
        }

        // Default addresses filter
        if (isset($filters['is_default'])) {
            $query->where('is_default', $filters['is_default']);
        }

        // Sorting
        $sortField = $filters['sort'] ?? 'created_at';
        $sortDirection = $filters['direction'] ?? 'desc';
        $query->orderBy($sortField, $sortDirection);

        return $query->paginate($perPage);
    }

    /**
     * Get addresses by customer
     */
    public function getByCustomer(int $customerId, ?string $type = null): Category
    {
        $cacheKey = $this->getCacheKey('customer', $customerId.'_'.$type);

        return Cache::remember($cacheKey, $this->cacheTtl, function () use (
            $customerId,
            $type,
        ) {
            $query = $this->model->where('customer_id', $customerId);

            if ($type) {
                $query->where('type', $type);
            }

            return $query->orderBy('is_default', 'desc')->orderBy('created_at', 'desc')->get();
        });
    }

    /**
     * Get default address for customer
     */
    public function getDefaultForCustomer(int $customerId, string $type): ?CustomerAddress
    {
        $cacheKey = $this->getCacheKey('default', $customerId.'_'.$type);

        return Cache::remember($cacheKey, $this->cacheTtl, function () use (
            $customerId,
            $type,
        ) {
            return $this->model
                ->where('customer_id', $customerId)
                ->where('type', $type)
                ->where('is_default', true)
                ->first();
        });
    }

    /**
     * Set address as default
     */
    public function setAsDefault(int $addressId): bool
    {
        $address = $this->find($addressId);

        if (! $address) {
            return false;
        }

        // Remove default from other addresses of same type
        $this->model
            ->where('customer_id', $address->customer_id)
            ->where('type', $address->type)
            ->where('id', '!=', $addressId)
            ->update(['is_default' => false]);

        // Set this address as default
        $address->update(['is_default' => true]);

        $this->clearCache();

        return true;
    }

    /**
     * Get addresses by country
     */
    public function getByCountry(string $countryCode): Category
    {
        $cacheKey = $this->getCacheKey('country', $countryCode);

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($countryCode) {
            return $this->model
                ->where('country_code', $countryCode)
                ->with('customer')
                ->orderBy('created_at', 'desc')
                ->get();
        });
    }

    /**
     * Validate address
     */
    public function validateAddress(array $addressData): array
    {
        $errors = [];

        // Basic validation
        if (empty($addressData['first_name'])) {
            $errors['first_name'] = 'First name is required';
        }

        if (empty($addressData['last_name'])) {
            $errors['last_name'] = 'Last name is required';
        }

        if (empty($addressData['address_line_1'])) {
            $errors['address_line_1'] = 'Address line 1 is required';
        }

        if (empty($addressData['city'])) {
            $errors['city'] = 'City is required';
        }

        if (empty($addressData['postal_code'])) {
            $errors['postal_code'] = 'Postal code is required';
        }

        if (empty($addressData['country_code'])) {
            $errors['country_code'] = 'Country is required';
        }

        return $errors;
    }

    /**
     * Bulk update addresses
     */
    public function bulkUpdate(array $addressIds, array $data): bool
    {
        try {
            $this->model->whereIn('id', $addressIds)->update($data);
            $this->clearCache();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get statistics
     */
    public function getStatistics(): array
    {
        $cacheKey = $this->getCacheKey('statistics', 'all');

        return Cache::remember($cacheKey, $this->cacheTtl, function () {
            return [
                'total_addresses' => $this->model->count(),
                'billing_addresses' => $this->model->where('type', 'billing')->count(),
                'shipping_addresses' => $this->model->where('type', 'shipping')->count(),
                'default_addresses' => $this->model->where('is_default', true)->count(),
                'addresses_by_country' => $this->model
                    ->select('country_code')
                    ->selectRaw('count(*) as count')
                    ->groupBy('country_code')
                    ->orderBy('count', 'desc')
                    ->limit(10)
                    ->get(),
            ];
        });
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
