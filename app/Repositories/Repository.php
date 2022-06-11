<?php

namespace App\Repositories;

use App\Contracts\Repositories\Repository as RepositoryContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

abstract class Repository implements RepositoryContract
{
    public $model;

    /**
     * @var array
     */
    protected $attributes = ['*'];

    public function __construct($model)
    {
        $this->model = $model;
    }

    public static function factory($model)
    {
        return new static($model);
    }

    /**
     * @inheritDoc
     */
    public function get($id)
    {
        return $this->model->find($id);
    }

    /**
     * @inheritDoc
     */
    public function all(): Collection
    {
        return $this->model->all();
    }

    public function create(array $attributes)
    {
        return $this->model->create($attributes);
    }

    /**
     * @inheritDoc
     */
    public function update($id, array $attributes)
    {
        return $this->model
                    ->find($id)
                    ->update($attributes);
    }

    /**
     * @inheritDoc
     */
    public function delete($id)
    {
        return $this->model
                    ->find($id)
                    ->delete();
    }

    /**
     * @inheritDoc
     */
    public function paginate($perPage = 15)
    {
        return $this->model->paginate($perPage);
    }

    /**
     * @return Builder
     */
    public function newQuery()
    {
        return $this->model->newQuery();
    }

    public function only(array $attributes): RepositoryContract
    {
        $this->attributes = $attributes;

        return $this;
    }
}
