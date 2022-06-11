<?php

namespace App\Concerns\MemSQL;

trait UsesMemSQLConnection
{
    protected string $testConnection = 'sqlite';

    /**
     * Get the current connection name for the model.
     *
     * @return string
     */
    public function getConnectionName()
    {
        if (config('app.env') === 'testing') {
            return $this->testConnection;
        }

        return config('database.feed_connection');
    }
}
