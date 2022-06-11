<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $defaults = config('settings.defaults');

        foreach ($defaults as $key => $value) {
            setting([$key => $value])->save();
        }
    }
}
