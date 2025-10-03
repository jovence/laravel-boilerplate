<?php

namespace App\Base;

use App\Base\BaseRepository;

class BaseService
{
    protected BaseRepository $repository;

    public function __construct(BaseRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get all records with full dynamic query support
     *
     * @param array|null $select
     * @param array|null $filters
     * @param array|null $load
     * @param array|null $whereHas
     * @param array|null $groupBy
     * @param array|null $sort
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll(
        array $select = null,
        array $filters = null,
        array $load = null,
        array $whereHas = null,
        array $groupBy = null,
        array $sort = null
    ) {
        $query = $this->repository->model()->newQuery();

        // 1️⃣ Select
        if ($select) {
            $query->select($select);
        }

        // 2️⃣ Filters
        if ($filters) {
            foreach ($filters as $field => $value) {
                $query->where($field, $value);
            }
        }

        // 3️⃣ Load relations
        if ($load) {
            foreach ($load as $relation) {
                $query->with([$relation['relationship'] => function ($q) use ($relation) {
                    $q->limit($relation['limit'] ?? 5)
                        ->orderBy($relation['key'] ?? 'id', $relation['direction'] ?? 'ASC');
                }]);
            }
        }

        // 4️⃣ whereHas dynamic
        if ($whereHas) {
            foreach ($whereHas as $filter) {
                $query->whereHas($filter['relationship'], function ($q) use ($filter) {
                    if (isset($filter['operator'], $filter['value'])) {
                        switch ($filter['operator']) {
                            case 'eq':
                                $q->where('id', $filter['value']);
                                break;
                            case 'gt':
                                $q->where('id', '>', $filter['value']);
                                break;
                            case 'lt':
                                $q->where('id', '<', $filter['value']);
                                break;
                            default:
                                $q->where('id', $filter['value']);
                        }
                    }

                    // Load nested relations if needed
                    if (isset($filter['load'])) {
                        $q->with([$filter['load']['relationship'] => function ($q2) use ($filter) {
                            $q2->limit($filter['load']['limit'] ?? 5)
                                ->orderBy($filter['load']['key'] ?? 'id', $filter['load']['direction'] ?? 'ASC');
                        }]);
                    }
                });
            }
        }

        // 5️⃣ Group By
        if ($groupBy) {
            $query->groupBy($groupBy);
        }

        // 6️⃣ Sort
        if ($sort) {
            foreach ($sort as $field => $direction) {
                $query->orderBy($field, $direction);
            }
        }

        return $query->get();
    }

    /**
     * Find record by UUID
     */
    public function find(string $uuid)
    {
        return $this->repository->find($uuid);
    }

    /**
     * Create a record
     */
    public function create(array $data)
    {
        return $this->repository->create($data);
    }

    /**
     * Update a record by UUID
     */
    public function update(string $uuid, array $data)
    {
        return $this->repository->update($uuid, $data);
    }

    /**
     * Delete a record by UUID
     */
    public function delete(string $uuid)
    {
        return $this->repository->delete($uuid);
    }
}
