<?php

namespace App\Base;

use App\Traits\ApiResponseTrait;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as Controller;

/**
 * BaseController
 *
 * All API controllers should extend this controller.
 * Provides:
 * 1. Standardized JSON responses
 * 2. Helpers for parsing select, groupBy, load, and whereHas
 *
 * Example usage in a controller:
 *
 * class UserController extends BaseController
 * {
 *     protected $service;
 *
 *     public function __construct(UserService $service)
 *     {
 *         $this->service = $service;
 *     }
 *
 *     public function index()
 *     {
 *         // Parse optional query parameters
 *         $select = $this->parseSelect(request()->get('select')); // ?select=id,name
 *         $groupBy = $this->parseGroupBy(request()->get('group_by')); // ?group_by=role
 *
 *         $users = $this->service->getAll($select, $groupBy);
 *
 *         return $this->success($users, 'Users fetched successfully');
 *     }
 * }
 */
class BaseController extends Controller
{
    use ApiResponseTrait, AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Parse selected fields from string or array
     *
     * Example:
     * $select = $this->parseSelect('id,name,email');
     * it converst it to the following result
     * Result: ['id','name','email']
     *1️⃣ What happens when a request comes in
     *GET /api/users?select=id,name,email&group_by=role
     *select=id,name,email → The client only wants these columns.

     *group_by=role → The client wants results grouped by role.
     * @param array|string|null $select
     * @return array|null
     */
    protected function parseSelect($select): ?array
    {
        if (!$select) return null;

        if (is_array($select)) return $select;

        if (is_string($select)) {
            return array_map('trim', explode(',', $select));
        }

        return null;
    }

    /**
     * Parse groupBy fields from string or array
     *
     * Example:
     * $groupBy = $this->parseGroupBy('role,department');
     * Result: ['role','department']
     *
     * @param array|string|null $groupBy
     * @return array|null
     */
    protected function parseGroupBy($groupBy): ?array
    {
        if (!$groupBy) return null;

        if (is_array($groupBy)) return $groupBy;

        if (is_string($groupBy)) {
            return array_map('trim', explode(',', $groupBy));
        }

        return null;
    }

    /**
     * Parse eager load relationships
     *
     * Example:
     * $load = $this->parseLoad([
     *     ['relationship' => 'posts', 'limit' => 10, 'direction' => 'desc']
     * ]);
     *
     * Result:
     * [
     *   ['relationship'=>'posts', 'limit'=>10, 'direction'=>'DESC', 'key'=>'id']
     * ]
     *GET /api/users?load[0][relationship]=posts&load[1][relationship]=roles&load[1][limit]=10&load[1][direction]=desc
     *2️⃣ When would a client request it

     *A client (frontend or another service) might only want some relationships in the API response. Examples:

     *Load related data on demand

     *Example: /api/users?load[0][relationship]=posts

     *Fetch users with their posts in one API call.

     *Limit and sort the relationship

     *Example: /api/users?load[0][relationship]=posts&load[0][limit]=5&load[0][direction]=desc

     *Fetch only the 5 most recent posts per user.

     *Flexible APIs

     *Instead of creating multiple endpoints (/users-with-posts, /users-with-comments), you let the client choose which relationships to load dynamically.
     *3️⃣ When you’d use parseLoad in your code

     *Anytime your controller needs to receive dynamic load instructions from the request.

     *Instead of manually validating each key (relationship, limit, direction), you just call:
     * GET /api/users?load[0][relationship]=posts&load[0][limit]=3&load[0][direction]=desc&load[1][relationship]=role

     * 
     * 4️⃣ Example scenario

     *Backend models:

     *User has many posts

     *User belongs to role
     *$load = $this->parseLoad(request()->get('load', []));
     *When would a client request it

     *A client (frontend or another service) might only want some relationships in the API response. Examples:

     *Load related data on demand

     *Example: /api/users?load[0][relationship]=posts

     *Fetch users with their posts in one API call.

     *Limit and sort the relationship

     *Example: /api/users?load[0][relationship]=posts&load[0][limit]=5&load[0][direction]=desc

     *Fetch only the 5 most recent posts per user.

     *Flexible APIs

     *Instead of creating multiple endpoints (/users-with-posts, /users-with-comments), you let the client choose which relationships to load dynamically.
     * @param array $load
     * @return array
     */
    protected function parseLoad(array $load): array
    {
        return array_map(function ($relation) {
            if (!isset($relation['relationship'])) {
                throw new \InvalidArgumentException("Load must have 'relationship' key");
            }

            $relation['direction'] = isset($relation['direction']) ? strtoupper($relation['direction']) : 'ASC';
            $relation['limit'] = $relation['limit'] ?? 5;
            $relation['key'] = $relation['key'] ?? 'id';

            return $relation;
        }, $load);
    }

    /**
     * Parse whereHas filters for relationships
     *
     * Example:
     * $whereHas = $this->parseWhereHas([
     *     ['relationship' => 'posts', 'operator'=>'gt', 'value'=>10]
     * ]);
     *
     * Result:
     * [
     *   ['relationship'=>'posts', 'operator'=>'gt', 'value'=>10, 'not'=>false]
     * ]
     *
     * get informations with with respect to conditions that is for instance get all user who have more than 10 posts
     * @param array $whereHas
     * @return array
     */
    protected function parseWhereHas(array $whereHas): array
    {
        foreach ($whereHas as $key => $has) {
            if (!isset($has['relationship'])) {
                throw new \InvalidArgumentException("whereHas must have 'relationship' key");
            }

            $whereHas[$key]['not'] = isset($has['not']) ? (bool)$has['not'] : false;
            $whereHas[$key]['operator'] = $has['operator'] ?? 'eq';

            if (isset($has['load'])) {
                $whereHas[$key]['load'] = $this->parseLoad([$has['load']])[0];
            }
        }

        return $whereHas;
    }
}
