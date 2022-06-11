<?php

namespace Database\Seeders;

use App\Models\Sports\Sport;
use App\Models\Sports\SportCategory;
use Illuminate\Database\Seeder;

class SportCategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->categories() as $category) {
            SportCategory::query()->updateOrCreate($category);
        }
    }

    private function categories(): array
    {
        return [
            [
              'sport_id' => Sport::BOXING_MMA_SPORT_ID,
              'name' => 'boxing'
            ],
            [
              'sport_id' => Sport::MMA_SPORT_ID,
              'name' => 'UFC'
            ],
            [
              'sport_id' => Sport::E_SPORTS_SPORT_ID,
              'name' => 'CS:GO'
            ],
            [
              'sport_id' => Sport::E_SPORTS_SPORT_ID,
              'name' => 'LOL'
            ],
            [
              'sport_id' => Sport::E_SPORTS_SPORT_ID,
              'name' => 'DOTA2'
            ],
            [
              'sport_id' => Sport::E_SPORTS_SPORT_ID,
              'name' => 'Rocket League'
            ],
            [
              'sport_id' => Sport::E_SPORTS_SPORT_ID,
              'name' => 'SCII'
            ],
            [
              'sport_id' => Sport::E_SPORTS_SPORT_ID,
              'name' => 'VALORANT'
            ],
            [
              'sport_id' => Sport::E_SPORTS_SPORT_ID,
              'name' => 'COD'
            ],
        ];
    }
}
