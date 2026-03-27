<?php

namespace Cartino\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

interface RepositoryInterface
{
    public function all(array $columns = ['*']): Collection;

    public function paginate(int $perPage = 20, array $columns = ['*']): LengthAwarePaginator;

    public function find(int $id, array $columns = ['*']): ?Model;

    public function findOrFail(int $id, array $columns = ['*']): Model;

    public function findWhere(array $where, array $columns = ['*']): Collection;

    public function findWhereFirst(array $where, array $columns = ['*']): ?Model;

    public function create(array $attributes): Model;

    public function update(int $id, array $attributes): Model;

    public function delete(int $id): bool;

    public function with(array $relations): static;

    public function whereHas(string $relation, ?callable $callback = null): static;

    public function orderBy(string $column, string $direction = 'asc'): static;

    public function search(string $term): static;
}
