<?php

namespace Database\Seeders;

use App\Models\Casino\Games\GameCategory;
use Illuminate\Database\Seeder;

class GameCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->categories() as $category) {
            GameCategory::query()->updateOrCreate($category);
        }
    }

    /**
     * 
     *   'name' => 'Arcade'
     *   'name' => 'Game Shows'   
     */

    private function categories()
    {
        return [
            [
                'name' => 'Slots'
            ],
            [
                'name' => 'Live Casino'
            ],
            [
                'name' => 'Table Games'
            ],
            [
                'name' => 'Populars'
            ],
            [
                'name' => 'Provably Fair'
            ],
            [
                'name' => 'Favourites'
            ]
        ];
    }
}
