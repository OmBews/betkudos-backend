<?php

if (!function_exists('promise')) {
    function promise(callable $callback, ...$args): Amp\Promise
    {
        return \Amp\call($callback, $args);
    }
}

if (! function_exists('genModelUniqueID')) {
    function genModelUniqueID(\Illuminate\Database\Eloquent\Model $model, string $column)
    {
        $random = rand(1, 100000);

        while ($model::query()->where($column, $random)->first()) {
            $random = rand(1, 100000);
        }

        return $random;
    }
}
