<?php

namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Model;

interface Repository
{
    /**
     * Get's a resource by it's ID
     *
     * @param int
     */
    public function get($id);

    /**
     * Get's all models.
     *
     * @return mixed
     */
    public function all();

    /**
     * Creates a resource.
     *
     * @param array
     */
    public function create(array $attributes);

    /**
     * Updates a resource.
     *
     * @param int
     * @param array
     */
    public function update($id, array $attributes);

    /**
     * Deletes a resource.
     *
     * @param int
     */
    public function delete($id);

    /**
     * Get all resources and paginate.
     */
    public function paginate();

    /**
     * Limit the attributes to be loaded by the query.
     * @param array $attributes
     * @return Repository
     */
    public function only(array $attributes): Repository;
}
