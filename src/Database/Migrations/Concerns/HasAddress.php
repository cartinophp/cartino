<?php

namespace Cartino\Database\Migrations\Concerns;

use Illuminate\Database\Schema\Blueprint;

trait HasAddress
{
    /**
     * Add complete address fields with optional geocoding and validation
     */
    public function addAddressFields(
        Blueprint $table,
        bool $required = false,
        bool $withGeocoding = false,
        bool $withValidation = false,
        string $prefix = '',
    ): void {
        $nullable = ! $required;

        $table->string($prefix.'address_line_1')->nullable($nullable);
        $table->string($prefix.'address_line_2')->nullable();
        $table->string($prefix.'city')->nullable($nullable);
        $table->string($prefix.'state')->nullable();
        $table->string($prefix.'postal_code', 20)->nullable($nullable);
        $table->string($prefix.'country_code', 2)->nullable($nullable);

        if ($withGeocoding) {
            $table->decimal($prefix.'latitude', 10, 8)->nullable();
            $table->decimal($prefix.'longitude', 11, 8)->nullable();
            $table->string($prefix.'formatted_address')->nullable();
            $table->string($prefix.'place_id')->nullable(); // Google Places ID

            $table->index([$prefix.'latitude', $prefix.'longitude']);
        }

        if ($withValidation) {
            $table->boolean($prefix.'is_validated')->default(false);
            $table->timestamp($prefix.'validated_at')->nullable();
            $table->string($prefix.'validation_source')->nullable(); // google, ups, usps, manual

            $table->index([$prefix.'is_validated', $prefix.'validation_source']);
        }

        // Common indexes
        $table->index([$prefix.'country_code', $prefix.'city']);
        $table->index([$prefix.'postal_code', $prefix.'country_code']);
    }

    /**
     * Add JSON address field
     */
    public function addJsonAddress(Blueprint $table, string $columnName = 'address'): void
    {
        $table->jsonb($columnName)->nullable();
    }

    /**
     * Add contact fields (phone, email)
     */
    public function addContactFields(Blueprint $table, string $prefix = ''): void
    {
        $table->string($prefix.'phone', 20)->nullable();
        $table->string($prefix.'email')->nullable();
        $table->string($prefix.'contact_person')->nullable();
    }
}
