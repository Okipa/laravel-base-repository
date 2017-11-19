<?php

namespace Okipa\LaravelCleverBaseRepository\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

trait EloquentOverlayTrait
{
    /**
     * The query builder
     *
     * @var Builder
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
     * The repository model
     *
     * @var Model
     */
    protected $model;

    /**
     * Set the repository current model
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return $this
     */
    public function setModel(Model $model)
    {
        $this->model = $model;

        return $this;
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
        if (!isset($value)) {
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
        if (isset($this->take) and !is_null($this->take)) {
            $this->query->take($this->take);
        }
        if (isset($this->skip) and !is_null($this->skip)) {
            $this->query->skip($this->skip);
        }
        if (isset($this->paginate) and !is_null($this->paginate)) {
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
     * @throws \ErrorException
     */
    protected function newQuery()
    {
        if (!$this->model) {
            throw new ErrorException(get_class($this) . ' : this repository has no defined model.');
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
     */
    public function count()
    {
        return $this->get()->count();
    }

    /**
     * Get all the specified model records in the database
     *
     * @return Collection
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
     */
    public function findBy(string $attribute, mixed $value)
    {
        $this->unsetClauses();
        $this->newQuery()->eagerLoad();

        return $this->query->where($attribute, '=', $value)->firstOrFail();
    }

    /**
     * Get all the model records in the database
     *
     * @return Collection
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
     * @return Collection
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
     * Create a new model record in the database
     *
     * @param array $data
     *
     * @return Model
     */
    public function create(array $data)
    {
        $this->unsetClauses();

        return $this->model->create($data);
    }

    /**
     * Update the specified model record in the database
     *
     * @param int   $entityId
     * @param array $data
     *
     * @return Model
     */
    public function updateById(int $entityId, array $data)
    {
        $this->unsetClauses();
        $model = $this->find($entityId);
        $model->update($data);

        return $model;
    }

    /**
     * Get the specified model record from the database
     *
     * @param int $entityId
     *
     * @return \Illuminate\Database\Eloquent\Model
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function find(int $entityId)
    {
        $this->unsetClauses();
        $this->newQuery()->eagerLoad();

        return $this->query->findOrFail($entityId);
    }

    /**
     * Delete one or more model records from the database
     *
     * @return int
     */
    public function delete()
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
     */
    public function deleteById(int $entityId)
    {
        $this->unsetClauses();

        return $this->find($entityId)->delete();
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
}