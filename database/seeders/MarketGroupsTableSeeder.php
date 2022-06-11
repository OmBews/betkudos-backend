<?php

namespace Database\Seeders;

use App\Models\Markets\MarketGroup;
use Illuminate\Database\Seeder;

class MarketGroupsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->groups() as $group) {
            MarketGroup::query()->updateOrCreate($group);
        }
    }

    private function groups(): array
    {
        return [
            [
                'name' => 'Asian Lines',
                'key' => 'asian_lines'
            ],
            [
                'name' => 'Goals',
                'key' => 'goals'
            ],
            [
                'name' => 'Half',
                'key' => 'half'
            ],
            [
                'name' => 'Main',
                'key' => 'main'
            ],
            [
                'name' => 'Minutes',
                'key' => 'minutes'
            ],
            [
                'name' => 'Specials',
                'key' => 'specials'
            ],
            [
                'name' => 'Player',
                'key' => 'player'
            ],
            [
                'name' => 'Others',
                'key' => 'others'
            ]
        ];
    }
}
