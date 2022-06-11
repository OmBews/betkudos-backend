<?php

namespace App\Repositories\Concerns;

trait Prioritizable
{
    /**
     * Return all resources ordered by
     * their priority.
     * @return mixed
     */
    public function orderedByPriority(array $relations = [])
    {
        return $this->model->with($relations)->get()->sortBy('priority');
    }
}
