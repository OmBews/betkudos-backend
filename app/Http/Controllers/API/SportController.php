<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\SportResource;
use App\Models\Sports\Sport;
use App\Repositories\SportsRepository;
use Illuminate\Http\Resources\Json\JsonResource;

class SportController extends Controller
{
    private $repository;

    public function __construct(SportsRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        return SportResource::collection($this->repository->all($this->relations()));
    }

    public function soon()
    {
        return SportResource::collection($this->repository->soon($this->relations()));
    }

    public function live()
    {
        return SportResource::collection($this->repository->live($this->relations(true)));
    }

    private function relations(bool $isLive = false): array
    {
        return [
            'categories',
            'featured' => function ($query) use ($isLive) {
                if ($isLive) {
                    $query->where('on_live_betting', true);
                }

                $query->select([
                    'id', 'name', 'sport_id',
                    'key', 'featured', 'priority',
                    'headers', 'featured_header'
                ])->take(2);
            }
        ];
    }
}
