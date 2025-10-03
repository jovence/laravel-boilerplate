<?php

namespace App\Base;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;


// example usage in a service
// class UserService
// {
//     protected BaseRepository $repo;

//     public function __construct(BaseRepository $repo)
//     {
//         $this->repo = $repo;
//     }

//     public function getAllUsers($select = null, $filters = null, $load = null, $groupBy = null, $sort = null)
//     {
//         return $this->repo->getAll($select, $filters, $load, $groupBy, $sort);
//     }
// }
// example usage in a controller
// class UserController extends BaseController
// {
//     protected UserService $service;

//     public function index()
//     {
//         $select = $this->parseSelect(request()->get('select'));
//         $groupBy = $this->parseGroupBy(request()->get('group_by'));
//         $load = $this->parseLoad(request()->get('load', []));
//         $filters = request()->get('filters', []);

//         $users = $this->service->getAllUsers($select, $filters, $load, $groupBy);

//         return $this->success($users, 'Users fetched successfully');
//     }
// }

class BaseRepository
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Get all records with optional filters, select, load, groupBy, sort
     *
     * @param array|null $select
     * @param array|null $filters
     * @param array|null $load
     * @param array|null $groupBy
     * @param array|null $sort
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll(
        array $select = null,
        array $filters = null,
        array $load = null,
        array $groupBy = null,
        array $sort = null
    ) {
        $query = $this->model->newQuery();

        // Select specific fields
        if ($select) {
            $query->select($select);
        }

        // Apply filters dynamically
        if ($filters) {
            foreach ($filters as $field => $value) {
                $query->where($field, $value);
            }
        }

        // Load relationships
        if ($load) {
            foreach ($load as $relation) {
                $query->with([$relation['relationship'] => function ($q) use ($relation) {
                    $q->limit($relation['limit'])->orderBy($relation['key'], $relation['direction']);
                }]);
            }
        }

        // Group by
        if ($groupBy) {
            $query->groupBy($groupBy);
        }

        // Sort
        if ($sort) {
            foreach ($sort as $field => $direction) {
                $query->orderBy($field, $direction);
            }
        }

        return $query->get();
    }

    /**
     * Find by UUID
     */
    public function find(string $uuid): Model
    {
        return $this->model->findOrFail($uuid);
    }

    /**
     * Create a record
     */
    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    /**
     * Update a record by UUID
     */
    public function update(string $uuid, array $data): Model
    {
        $model = $this->find($uuid);
        $model->update($data);
        return $model;
    }

    /**
     * Delete a record by UUID
     */
    public function delete(string $uuid): bool
    {
        $model = $this->find($uuid);
        return $model->delete();
    }

    /**
     * Dynamic whereHas filter for relationships
     */
    public function whereHasDynamic(array $whereHas): Builder
    {
        $query = $this->model->newQuery();

        foreach ($whereHas as $filter) {
            $query->whereHas($filter['relationship'], function ($q) use ($filter) {
                if (isset($filter['operator'], $filter['value'])) {
                    $op = $filter['operator'];
                    $val = $filter['value'];

                    switch ($op) {
                        case 'eq':
                            $q->where('id', $val);
                            break;
                        case 'gt':
                            $q->where('id', '>', $val);
                            break;
                        case 'lt':
                            $q->where('id', '<', $val);
                            break;
                        default:
                            $q->where('id', $val);
                    }
                }
            });
        }

        return $query;
    }
}
