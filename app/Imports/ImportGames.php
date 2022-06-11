<?php

namespace App\Imports;

use App\Models\Casino\Games\Game;
use App\Models\Casino\Providers\Provider;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ImportGames implements ToCollection, WithHeadingRow
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection)
    {
        try {
            // To update casino game category
            foreach ($collection as $value) {
                $provider = trim($value['provider']);
                $game_name = trim($value['game_name']);
                $betkudos_category = trim($value['betkudos_category']);
                $commission = trim($value['commission']);
                
                // Check Games by provider name and game name
                $game = Game::where('provider', 'like', '%'.$provider.'%')
                            ->where('name', 'like', '%'.$game_name.'%')->first();

                // Update game category from excel file
                if ($game) {
                    $game->category = $betkudos_category;
                    $game->save();
                }

                // check provider 
                $isProvider = Provider::where('name', 'like', '%'.$provider.'%')->where('commission', 0)->first();
                
                if($isProvider) {
                    $isProvider->commission = $commission;
                    $isProvider->save();
                }
            }

        } catch (\Throwable $th) {
            throw $th;
        }
        
    }
}
