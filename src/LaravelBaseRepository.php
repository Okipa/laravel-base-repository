<?php

namespace Okipa\LaravelBaseRepository;

use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

abstract class LaravelBaseRepository
{
    /**
     * The repository associated main model
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model;
    /**
     * The repository associated request
     *
     * @var \Illuminate\Http\Request
     */
    protected $request;
    /**
     * Except standard laravel http attributes from request attributes
     *
     * @var boolean
     */
    protected $exceptLaravelHttpAttributes = true;
    /**
     * The query builder
     *
     * @var \Illuminate\Database\Eloquent\Builder;
     */
    protected $query;
    /**
     * Alias for the query limit
     *
     * @var int
     */
    protected $take;
    /**
     * The number of element to skip
     *
     * @var int
     */
    protected $skip;
    /**
     * The number of entities to show per page
     *
     * @var int
     */
    protected $paginate;
    /**
     * Array of related models to eager load
     *
     * @var array
     */
    protected $with = [];
    /**
     * Array of one or more where clause parameters
     *
     * @var array
     */
    protected $wheres = [];
    /**
     * Array of one or more where in clause parameters
     *
     * @var array
     */
    protected $whereIns = [];
    /**
     * Array of one or more ORDER BY column/value pairs
     *
     * @var array
     */
    protected $orderBys = [];
    /**
     * Array of scope methods to call on the model
     *
     * @var array
     */
    protected $scopes = [];

    /**
     * BaseRepository constructor.
     */
    public function __construct()
    {
        if ($this->model && ! $this->model instanceof Model) {
            $this->model = app($this->model);
        }
        $this->request = request();
    }

    /**
     * @param string $column
     * @param string $operator
     * @param mixed  $value
     *
     * @return $this
     */
    public function where(string $column, string $operator, mixed $value = null)
    {
        if (! isset($value)) {
            $value = $operator;
            $operator = '=';
        }
        $this->wheres[] = compact('column', 'operator', 'value');

        return $this;
    }

    /**
     * Add a simple where in clause to the query
     *
     * @param string $column
     * @param mixed  $values
     *
     * @return $this
     */
    public function whereIn(string $column, mixed $values)
    {
        $values = is_array($values) ? $values : [$values];
        $this->whereIns[] = compact('column', 'values');

        return $this;
    }

    /**
     * Set an ORDER BY clause
     *
     * @param string $column
     * @param string $direction
     *
     * @return $this
     */
    public function orderBy(string $column, string $direction = 'asc')
    {
        $this->orderBys[] = compact('column', 'direction');

        return $this;
    }

    /**
     * Set the query limit
     *
     * @param int $limit
     *
     * @return $this
     */
    public function take(int $limit)
    {
        $this->take = $limit;

        return $this;
    }

    /**
     * Set the query skip
     *
     * @param int $start
     *
     * @return $this
     */
    public function skip(int $start)
    {
        $this->skip = $start;

        return $this;
    }

    /**
     * Get the first specified model record from the database
     *
     * @return Model
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Exception
     */
    public function first()
    {
        $this->newQuery()->eagerLoad()->setClauses()->setScopes();
        $model = $this->query->firstOrFail();
        $this->unsetClauses();

        return $model;
    }

    /**
     * Set query scopes
     *
     * @return $this
     */
    protected function setScopes()
    {
        foreach ($this->scopes as $method => $args) {
            $this->query->$method(implode(', ', $args));
        }

        return $this;
    }

    /**
     * Set clauses on the query builder
     *
     * @return $this
     */
    protected function setClauses()
    {
        foreach ($this->wheres as $where) {
            $this->query->where($where['column'], $where['operator'], $where['value']);
        }
        foreach ($this->whereIns as $whereIn) {
            $this->query->whereIn($whereIn['column'], $whereIn['values']);
        }
        foreach ($this->orderBys as $orders) {
            $this->query->orderBy($orders['column'], $orders['direction']);
        }
        if (isset($this->take) and ! is_null($this->take)) {
            $this->query->take($this->take);
        }
        if (isset($this->skip) and ! is_null($this->skip)) {
            $this->query->skip($this->skip);
        }
        if (isset($this->paginate) and ! is_null($this->paginate)) {
            $this->query->paginate($this->paginate);
        }

        return $this;
    }

    /**
     * Add relationships to the query builder to eager load
     *
     * @return $this
     */
    protected function eagerLoad()
    {
        foreach ($this->with as $relation) {
            $this->query->with($relation);
        }

        return $this;
    }

    /**
     * Create a new instance of the model's query builder
     *
     * @return $this
     * @throws \Exception
     */
    protected function newQuery()
    {
        if (! $this->model instanceof Model) {
            throw new Exception(get_class($this) . ' : this repository has no defined model.');
        }
        $this->query = $this->model->newQuery();

        return $this;
    }

    /**
     * Reset the query clause parameter arrays
     *
     * @return $this
     */
    protected function unsetClauses()
    {
        $this->wheres = [];
        $this->whereIns = [];
        $this->orderBys = [];
        $this->take = null;
        $this->skip = null;
        $this->paginate = null;
        $this->scopes = [];

        return $this;
    }

    /**
     * Count the number of specified model records in the database
     *
     * @return int
     * @throws \Exception
     */
    public function count()
    {
        return $this->get()->count();
    }

    /**
     * Get all the specified model records in the database
     *
     * @return \Illuminate\Database\Eloquent\Collection
     * @throws \Exception
     */
    public function get()
    {
        $this->newQuery()->eagerLoad()->setClauses()->setScopes();
        $models = $this->query->get();
        $this->unsetClauses();

        return $models;
    }

    /**
     * Get the specified model record from the database from its attribute
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return \Illuminate\Database\Eloquent\Model
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Exception
     */
    public function findBy(string $attribute, mixed $value)
    {
        $this->unsetClauses();
        $this->newQuery()->eagerLoad();

        return $this->query->where($attribute, '=', $value)->firstOrFail();
    }

    /**
     * Get all the model records stored in the database
     *
     * @return \Illuminate\Database\Eloquent\Collection
     * @throws \Exception
     */
    public function all()
    {
        $this->newQuery()->eagerLoad();
        $models = $this->query->get();
        $this->unsetClauses();

        return $models;
    }

    /**
     * Set Eloquent relationships to eager load
     *
     * @param array $relations
     *
     * @return $this
     */
    public function with(array $relations)
    {
        $this->with = $relations;

        return $this;
    }

    /**
     * Get paginated list
     *
     * @param int   $perPage
     * @param array $columns
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     * @throws \Exception
     */
    public function paginate(int $perPage = 15, array $columns = ['*'])
    {
        $this->newQuery()->eagerLoad()->setClauses()->setScopes();
        $paginator = $this->query->paginate($perPage, $columns);
        $this->unsetClauses();

        return $paginator;
    }

    /**
     * Create one or more new model records in the database
     *
     * @param array $data
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function createMultiple(array $data)
    {
        $models = new Collection();
        foreach ($data as $d) {
            $models->push($this->create($d));
        }

        return $models;
    }

    /**
     * Delete one or more model records from the database
     *
     * @return int
     * @throws \Exception
     */
    public function deleteFromQuery()
    {
        $this->newQuery()->setClauses()->setScopes();
        $result = $this->query->delete();
        $this->unsetClauses();

        return $result;
    }

    /**
     * Delete the specified model record from the database
     *
     * @param int $entityId
     *
     * @return bool|null
     * @throws \Exception
     */
    public function deleteFromId(int $entityId)
    {
        return $this->find($entityId)->delete();
    }

    /**
     * Get the specified model record from the database
     *
     * @param int $entityId
     *
     * @return \Illuminate\Database\Eloquent\Model
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Exception
     */
    public function findFromId(int $entityId)
    {
        $this->unsetClauses();
        $this->newQuery()->eagerLoad();

        return $this->query->findOrFail($entityId);
    }

    /**
     * Delete multiple records
     *
     * @param array $entityIds
     *
     * @return int
     */
    public function deleteMultipleById(array $entityIds)
    {
        return $this->model->destroy($entityIds);
    }

    /**
     * @param array $exceptFromSaving
     * @param array $addToSaving
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function createOrUpdateModelFromRequest(array $exceptFromSaving = [], array $addToSaving = [])
    {
        $requestAttributes = $this->exceptLaravelHttpAttributesFromRequestValues($exceptFromSaving);
        $attributesToSave = array_merge_recursive($requestAttributes, $addToSaving);

        return $this->requestContainsModelPrimary()
            ? $this->model->create($attributesToSave)
            : $this->model->updateFromId($attributesToSave);
    }

    /**
     * Except Laravel Http attributes from request.
     *
     * @param array $except
     *
     * @return array $defaultRequestEntries
     */
    protected function exceptLaravelHttpAttributesFromRequestValues(array $except = [])
    {
        if ($this->exceptLaravelHttpAttributes) {
            $except[] = '_token';
            $except[] = '_method';
        }

        return $this->request->except($except);
    }

    /**
     * Check if the model should be created.
     * If not, it should be updated.
     *
     * @return bool
     */
    protected function requestContainsModelPrimary()
    {
        return ! empty($this->getModelPrimaryValueFromRequest());
    }

    /**
     * Get model primary value from request.
     *
     * @return array|null|string
     */
    protected function getModelPrimaryValueFromRequest()
    {
        return $this->request->input($this->model->getKeyName());
    }

    /**
     * Destroy a model from the request data
     *
     * @return bool|null|\Exception
     * @throws \Exception
     */
    public function destroyModelFromRequest()
    {
        if ($this->requestContainsModelPrimary()) {
            return $this->model->delete();
        } else {
            return new Exception('The request does not contain the repository-associated-model primary key value.');
        }
    }

    /**
     * Update a model instance from its id
     *
     * @param int   $entityId
     * @param array $data
     *
     * @return Model
     * @throws \Exception
     */
    protected function updateFromId(int $entityId, array $data)
    {
        $model = $this->findFromId($entityId);
        $model->update($data);

        return $model;
    }
}
