<?php

namespace App\Contracts\Repositories\Concerns;

interface Prioritizable
{
    /**
     * Return all resources ordered by
     * their priority.
     * @return mixed
     */
    public function orderedByPriority(array $relations = []);
}
