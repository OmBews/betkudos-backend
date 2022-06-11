<?php

return [
	/*
	|--------------------------------------------------------------------------
	| Default Settings Store
	|--------------------------------------------------------------------------
	|
	| This option controls the default settings store that gets used while
	| using this settings library.
	|
	| Supported: "json", "database"
	|
	*/
	'store' => 'database',

	/*
	|--------------------------------------------------------------------------
	| JSON Store
	|--------------------------------------------------------------------------
	|
	| If the store is set to "json", settings are stored in the defined
	| file path in JSON format. Use full path to file.
	|
	*/
	'path' => storage_path().'/settings.json',

	/*
	|--------------------------------------------------------------------------
	| Database Store
	|--------------------------------------------------------------------------
	|
	| The settings are stored in the defined file path in JSON format.
	| Use full path to JSON file.
	|
	*/
	// If set to null, the default connection will be used.
	'connection' => null,
	// Name of the table used.
	'table' => 'settings',
	// Cache usage configurations.
	'enableCache' => false,
	'forgetCacheByWrite' => true,
	'cacheTtl' => 15,
	// If you want to use custom column names in database store you could
	// set them in this configuration
	'keyColumn' => 'key',
	'valueColumn' => 'value',

    /*
    |--------------------------------------------------------------------------
    | Default Settings
    |--------------------------------------------------------------------------
    |
    | Define all default settings that will be used before any settings are set,
    | this avoids all settings being set to false to begin with and avoids
    | hardcoding the same defaults in all 'Settings::get()' calls
    |
    */
    'defaults' => [
        'global.live_events_enabled' => 1,
        'global.block_sports_book' => 0,
        'limits.min_deposit' => 50,
        'limits.max_deposit' => 50,
        'limits.min_withdrawal_per_day' => 50,
        'limits.max_withdrawal_per_day' => 50,
        'limits.instant_withdrawal_limit' => 50,
        'limits.min_bet' => 0.5, // mBTC
        'limits.max_bet' => 1000, // mBTC
        'limits.max_bet_profit' => 800, // mBTC
    ]
];
