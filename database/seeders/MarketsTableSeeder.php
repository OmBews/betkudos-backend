<?php

namespace Database\Seeders;

use App\Models\Markets\Market;
use Illuminate\Database\Seeder;

class MarketsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->updateOrCreateMarkets($this->markets());
    }

    protected function updateOrCreateMarkets(array $markets)
    {
        foreach ($markets as $market) {
            $query = ['key' => $market['key'], 'sport_id' => $market['sport_id']];
            Market::query()->updateOrCreate($query, $market);
        }
    }

    protected function markets(): array
    {
        return [];
    }
}
