<?php

namespace Cartino\Services;

use Cartino\DTO\CustomerAddress\CustomerAddressData;
use Cartino\Models\CustomerAddress;
use Cartino\Repositories\CustomerAddressRepository;

class CustomerAddressService
{
    public function __construct(
        private CustomerAddressRepository $repository,
    ) {}

    /**
     * Get all addresses for a customer
     */
    public function getCustomerAddresses(int $customerId, ?string $type = null): array
    {
        $addresses = $this->repository->getByCustomer($customerId, $type);

        return $addresses->map(fn ($address) => CustomerAddressData::fromModel($address))->toArray();
    }

    /**
     * Create new address
     */
    public function createAddress(array $data): CustomerAddressData
    {
        // If this is set as default, remove default from other addresses of same type
        if ($data['is_default'] ?? false) {
            CustomerAddress::query()
                ->where('customer_id', $data['customer_id'])
                ->where('type', $data['type'])
                ->update(['is_default' => false]);
        }

        $address = $this->repository->create($data);

        return CustomerAddressData::fromModel($address);
    }

    /**
     * Update address
     */
    public function updateAddress(CustomerAddress $address, array $data): CustomerAddressData
    {
        // If this is set as default, remove default from other addresses of same type
        if ($data['is_default'] ?? false) {
            CustomerAddress::query()
                ->where('customer_id', $address->customer_id)
                ->where('type', $address->type)
                ->where('id', '!=', $address->id)
                ->update(['is_default' => false]);
        }

        $address = $this->repository->update($address->id, $data);

        return CustomerAddressData::fromModel($address);
    }

    /**
     * Delete address
     */
    public function deleteAddress(CustomerAddress $address): bool
    {
        return $this->repository->delete($address->id);
    }

    /**
     * Set address as default
     */
    public function setAsDefault(int $addressId): bool
    {
        return $this->repository->setAsDefault($addressId);
    }

    /**
     * Get default address for customer
     */
    public function getDefaultAddress(int $customerId, string $type): ?CustomerAddressData
    {
        $address = $this->repository->getDefaultForCustomer($customerId, $type);

        return $address ? CustomerAddressData::fromModel($address) : null;
    }

    /**
     * Validate address data
     */
    public function validateAddress(array $data): array
    {
        return $this->repository->validateAddress($data);
    }

    /**
     * Get address statistics
     */
    public function getStatistics(): array
    {
        return $this->repository->getStatistics();
    }

    /**
     * Bulk update addresses
     */
    public function bulkUpdate(array $addressIds, array $data): bool
    {
        return $this->repository->bulkUpdate($addressIds, $data);
    }
}
