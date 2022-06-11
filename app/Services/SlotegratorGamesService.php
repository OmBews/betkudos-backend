<?php

namespace App\Services;

use App\Jobs\RequestSlotegratorGames;
use App\Jobs\StoreSlotegratorGame;
use App\Slotegrator\Slotegrator;
use Illuminate\Support\Arr;

class SlotegratorGamesService
{
    public function __construct(private Slotegrator $slotegrator) {}

    /**
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function storeGames()
    {
        $nextPage = 2;
        $data = $this->getGames(1);
        echo "First page", PHP_EOL;

        $games = $data->items;

         foreach ($games as $game) {
            $this->storeGame($game);
         }

        $pageCount = $data->_meta->pageCount;

        while ($nextPage <= $pageCount) {
            echo "page $nextPage", PHP_EOL;

            RequestSlotegratorGames::dispatch($nextPage);

            $nextPage++;
        }
    }

    public function getGames(int $page): object
    {
        $response = $this->slotegrator->games($page);

        if ($response->failed()) {
            throw $response->toException();
        }

        return $response->object();
    }

    public function storeGame($game)
    {
        StoreSlotegratorGame::dispatch($game);
    }
}
