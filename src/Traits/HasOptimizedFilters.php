<?php

namespace Cartino\Traits;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

trait HasOptimizedFilters
{
    /**
     * All available operators for filtering
     */
    protected static array $operators = [
        'eq' => '=',
        'ne' => '!=',
        'gt' => '>',
        'gte' => '>=',
        'lt' => '<',
        'lte' => '<=',
        'like' => 'LIKE',
        'nlike' => 'NOT LIKE',
        'starts' => 'LIKE_START',
        'ends' => 'LIKE_END',
        'in' => 'IN',
        'nin' => 'NOT_IN',
        'between' => 'BETWEEN',
        'nbetween' => 'NOT_BETWEEN',
        'null' => 'NULL',
        'nnull' => 'NOT_NULL',
        'date' => 'DATE',
        'month' => 'MONTH',
        'year' => 'YEAR',
    ];

    /**
     * Fields that should always be eager loaded
     */
    protected static function getDefaultEagerLoad(): array
    {
        return property_exists(static::class, 'defaultEagerLoad') ? static::$defaultEagerLoad : [];
    }

    /**
     * Fields that can be filtered
     */
    protected static function getFilterableFields(): array
    {
        return property_exists(static::class, 'filterable') ? static::$filterable : (new static)->getFillable();
    }

    /**
     * Fields that can be sorted
     */
    protected static function getSortableFields(): array
    {
        return property_exists(static::class, 'sortable') ? static::$sortable : ['id', 'created_at', 'updated_at'];
    }

    /**
     * Main filter scope with N+1 protection
     */
    public function scopeFilter(Builder $query, array $params = []): Builder
    {
        // 1. Always eager load default relations (N+1 protection)
        if (! empty(static::getDefaultEagerLoad())) {
            $query->with(static::getDefaultEagerLoad());
        }

        // 2. Apply field selection if specified
        if (isset($params['fields'])) {
            $this->applyFieldSelection($query, $params['fields']);
        }

        // 3. Apply filters
        foreach ($params as $field => $conditions) {
            // Skip special parameters
            if ($this->isSpecialParameter($field)) {
                continue;
            }

            // Check if field is filterable
            if (! $this->isFilterable($field)) {
                continue;
            }

            $this->applyFilter($query, $field, $conditions);
        }

        // 4. Apply sorting
        if (isset($params['sort'])) {
            $this->applySorting($query, $params['sort']);
        }

        // 5. Apply additional includes
        if (isset($params['include'])) {
            $this->applyIncludes($query, $params['include']);
        }

        // 6. Apply search if present
        if (isset($params['search']) && ! empty($params['search'])) {
            $this->applySearch($query, $params['search']);
        }

        return $query;
    }

    /**
     * Fast filter for critical performance queries
     */
    public function scopeFastFilter(Builder $query, array $filters): Builder
    {
        $wheres = [];
        $bindings = [];

        foreach ($filters as $field => $value) {
            if ($this->isFilterable($field)) {
                $wheres[] = "`{$field}` = ?";
                $bindings[] = $value;
            }
        }

        if (! empty($wheres)) {
            $query->whereRaw(implode(' AND ', $wheres), $bindings);
        }

        return $query;
    }

    /**
     * Apply a single filter condition
     */
    protected function applyFilter(Builder $query, string $field, $conditions): void
    {
        // Handle relation filters
        if (str_contains($field, '.')) {
            $this->applyRelationFilter($query, $field, $conditions);

            return;
        }

        // Simple equality
        if (! is_array($conditions)) {
            $query->where($field, $conditions);

            return;
        }

        // Multiple operators on same field
        foreach ($conditions as $operator => $value) {
            $this->applyOperator($query, $field, $operator, $value);
        }
    }

    /**
     * Apply operator to query
     */
    protected function applyOperator(Builder $query, string $field, string $operator, $value): void
    {
        switch ($operator) {
            case 'like':
                $query->where($field, 'LIKE', "%{$value}%");
                break;

            case 'nlike':
                $query->where($field, 'NOT LIKE', "%{$value}%");
                break;

            case 'starts':
                $query->where($field, 'LIKE', "{$value}%");
                break;

            case 'ends':
                $query->where($field, 'LIKE', "%{$value}");
                break;

            case 'in':
                $values = is_array($value) ? $value : explode(',', $value);
                $query->whereIn($field, $values);
                break;

            case 'nin':
                $values = is_array($value) ? $value : explode(',', $value);
                $query->whereNotIn($field, $values);
                break;

            case 'between':
                $values = is_array($value) ? $value : explode(',', $value);
                if (count($values) >= 2) {
                    $query->whereBetween($field, [$values[0], $values[1]]);
                }
                break;

            case 'nbetween':
                $values = is_array($value) ? $value : explode(',', $value);
                if (count($values) >= 2) {
                    $query->whereNotBetween($field, [$values[0], $values[1]]);
                }
                break;

            case 'null':
                $query->whereNull($field);
                break;

            case 'nnull':
                $query->whereNotNull($field);
                break;

            case 'date':
                $query->whereDate($field, $value);
                break;

            case 'month':
                $query->whereMonth($field, $value);
                break;

            case 'year':
                $query->whereYear($field, $value);
                break;

            default:
                // Basic operators
                $sqlOperator = self::$operators[$operator] ?? '=';
                if (
                    ! in_array($sqlOperator, [
                        'NULL',
                        'NOT_NULL',
                        'IN',
                        'NOT_IN',
                        'BETWEEN',
                        'NOT_BETWEEN',
                        'LIKE_START',
                        'LIKE_END',
                    ])
                ) {
                    $query->where($field, $sqlOperator, $value);
                }
                break;
        }
    }

    /**
     * Apply relation filter
     */
    protected function applyRelationFilter(Builder $query, string $field, $conditions): void
    {
        $parts = explode('.', $field);
        $relation = array_shift($parts);
        $relationField = implode('.', $parts);

        $query->whereHas($relation, function ($q) use ($relationField, $conditions) {
            if (! is_array($conditions)) {
                $q->where($relationField, $conditions);
            } else {
                foreach ($conditions as $operator => $value) {
                    $this->applyOperator($q, $relationField, $operator, $value);
                }
            }
        });
    }

    /**
     * Apply sorting
     */
    protected function applySorting(Builder $query, string $sort): void
    {
        $sorts = explode(',', $sort);

        foreach ($sorts as $sortField) {
            $direction = 'asc';

            // Check for descending prefix
            if (str_starts_with($sortField, '-')) {
                $direction = 'desc';
                $sortField = substr($sortField, 1);
            }

            // Check if field is sortable
            if (in_array($sortField, static::getSortableFields())) {
                $query->orderBy($sortField, $direction);
            }
        }
    }

    /**
     * Apply includes
     */
    protected function applyIncludes(Builder $query, $includes): void
    {
        $relations = is_array($includes) ? $includes : explode(',', $includes);

        // Filter valid relations only
        $validRelations = array_filter($relations, function ($relation) {
            return method_exists($this, Str::camel($relation));
        });

        if (! empty($validRelations)) {
            $query->with($validRelations);
        }
    }

    /**
     * Apply search across searchable fields
     */
    protected function applySearch(Builder $query, string $search): void
    {
        $searchable = property_exists(static::class, 'searchable') ? static::$searchable : ['name'];

        $query->where(function ($q) use ($search, $searchable) {
            foreach ($searchable as $field) {
                $q->orWhere($field, 'LIKE', "%{$search}%");
            }
        });
    }

    /**
     * Apply field selection
     */
    protected function applyFieldSelection(Builder $query, $fields): void
    {
        $fieldList = is_array($fields) ? $fields : explode(',', $fields);

        // Always include ID and foreign keys
        if (! in_array('id', $fieldList)) {
            array_unshift($fieldList, 'id');
        }

        // Add foreign keys for eager loaded relations
        foreach (static::getDefaultEagerLoad() as $relation) {
            $relationName = explode(':', $relation)[0];
            $foreignKey = Str::snake($relationName).'_id';

            if (! in_array($foreignKey, $fieldList) && $this->hasColumn($foreignKey)) {
                $fieldList[] = $foreignKey;
            }
        }

        $query->select($fieldList);
    }

    /**
     * Check if parameter is special (not a filter)
     */
    protected function isSpecialParameter(string $field): bool
    {
        return in_array($field, [
            'page',
            'per_page',
            'sort',
            'order',
            'include',
            'fields',
            'search',
            'limit',
        ]);
    }

    /**
     * Check if field is filterable
     */
    protected function isFilterable(string $field): bool
    {
        // Allow relation filters
        if (str_contains($field, '.')) {
            return true;
        }

        return in_array($field, static::getFilterableFields());
    }

    /**
     * Check if column exists
     */
    protected function hasColumn(string $column): bool
    {
        return Schema::hasColumn($this->getTable(), $column);
    }

    /**
     * Scope for paginated results with filters
     */
    public function scopePaginateFilter(
        Builder $query,
        array $params = [],
        ?int $perPage = null,
    ): LengthAwarePaginator {
        $perPage = $perPage ?? $params['per_page'] ?? config('cartino.filters.pagination.default', 15);
        $maxPerPage = config('cartino.filters.pagination.max', 100);

        $perPage = min($perPage, $maxPerPage);

        return $query->filter($params)->paginate($perPage)->withQueryString();
    }
}
