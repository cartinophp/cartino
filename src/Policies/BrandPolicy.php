<?php

declare(strict_types=1);

namespace Cartino\Policies;

use Cartino\Models\Brand;
use Cartino\Policies\Concerns\HasStandardAuthorization;
use Illuminate\Database\Eloquent\Model;

class BrandPolicy
{
    use HasStandardAuthorization;

    protected function getPermissionPrefix(): string
    {
        return 'brands';
    }

    /**
     * Brands can't be deleted if they have products
     */
    protected function canDeleteModel(Model|Brand $model): bool
    {
        return ! $model->products()->exists();
    }
}
