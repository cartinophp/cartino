<?php

declare(strict_types=1);

namespace Cartino\Models;

use Cartino\Traits\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductType extends Model
{
    use HasFactory;
    use SoftDeletes;
    use Translatable;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'status',
    ];

    protected array $translatable = [
        'name',
        'description',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
