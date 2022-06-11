<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use App\Http\Controllers\API\CasinoController;
use App\Http\Controllers\Bost\casinoCategoryUpdateController;
use App\Http\Controllers\IPNController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

Route::get('/webhooks/btc/ipn/{txid}', [IPNController::class, 'payment']);
Route::post('/casino/aggregator', [CasinoController::class, 'aggregator'])->name('aggregator.callback');
Route::get('/', 'HomeController@index');

Auth::routes([
    'register' => false,
    'reset' => false,
    'confirm' => false,
    'verify' => false
]);

Route::get('providersHasGame', [CasinoController::class, 'providersHasGame']);

Route::get('/home', 'HomeController@index')->name('home');
Route::post('/self-validate', [CasinoController::class, 'selfValidate']);

Route::get('/update-currency/{key}', [CasinoController::class, 'updateCurrency']); // To update currency type in game list
Route::get('/get-game-provider/{key}', [CasinoController::class, 'getGameProvider']); // TO get Game and provider result
Route::get('/block-providers', [casinoCategoryUpdateController::class, 'blockProvider']); // Blocks providers
Route::get('currency-conversion', [CasinoController::class, 'currencyConversion']); // To check currency conversion api