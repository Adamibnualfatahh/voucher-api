<?php

namespace App\Repositories;

use App\Interfaces\RepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class EloquentRepository implements RepositoryInterface
{
    /**
     * @var Builder|Model
     */
    protected Model|Builder $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function getAll(array $params = []): LengthAwarePaginator
    {
        $query = $this->model->query();

        if (isset($params['select'])) {
            $query->select($params['select']);
        }

        if (isset($params['sort_by']) && isset($params['sort_dir'])) {
            $query->orderBy($params['sort_by'], $params['sort_dir']);
        }

        $perPage = $params['per_page'] ?? 15;

        return $query->paginate($perPage);
    }

    public function find(int|string $id): ?Model
    {
        return $this->model->find($id);
    }

    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    public function update(int|string $id, array $data): bool
    {
        $model = $this->find($id);
        if ($model) {
            return $model->update($data);
        }
        return false;
    }

    public function delete(int|string $id): bool
    {
        $model = $this->find($id);
        if ($model) {
            return $model->delete();
        }
        return false;
    }
}
