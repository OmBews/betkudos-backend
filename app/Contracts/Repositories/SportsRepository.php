<?php

namespace App\Contracts\Repositories;

use App\Models\Sports\Sport;
use Illuminate\Support\Collection;

interface SportsRepository
{
    public function all(array $relations = []): Collection;

    public function soon(array $relations = []): Collection;

    public function find(int $id, bool $fail = false): ?Sport;

    public function live(): Collection;
}
