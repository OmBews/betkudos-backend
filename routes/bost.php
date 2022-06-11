<?php

use App\Http\Controllers\API\TransactionController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\WalletController;
use App\Http\Controllers\API\WithdrawalController;
use App\Http\Controllers\Bost\AddFundsController;
use App\Http\Controllers\Bost\BetMonitorController;
use App\Http\Controllers\Bost\DepositController;
use App\Http\Controllers\Bost\LimitsController;
use App\Http\Controllers\Bost\RiskOverviewController;
use App\Http\Controllers\Bost\BetListController;
use App\Http\Controllers\Bost\casinoCategoryUpdateController;
use App\Http\Controllers\Bost\CasinoGameController;
use App\Http\Controllers\Bost\GeoBlockingController;
use App\Http\Controllers\Bost\CommissionController;
use App\Http\Controllers\Bost\SelfxController;
use App\Http\Controllers\Bost\UserKycController;
use App\Models\Withdrawals\Withdrawal;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'auth', 'namespace' => 'Auth'], function () {
    Route::post('login', 'LoginController@login')->name('bost.login');
});

Route::group(['middleware' => ['auth:api', 'role:bookie']], function () {
    Route::get('sessions/search/{value}/{field}/{filter?}', 'SessionController@search')->name('sessions.search');

    Route::get('sessions/filter/{filter?}', 'SessionController@filter')->name('sessions.filter');

    Route::put('sessions/{session}/block-ip', 'SessionController@blockIp')->name('sessions.block-ip');

    Route::apiResource('sessions', 'SessionController')->only('index', 'show');

    Route::group(['prefix' => 'settings'], function () {
        Route::get('/', 'SettingsController@index')->name('settings.index');
        Route::put('/', 'SettingsController@update')->name('settings.update');
        Route::post('/maintenance', 'SettingsController@maintenance')->name('settings.maintenance');
        Route::get('/maintenance', 'SettingsController@getMaintenance')->name('settings.getMaintenance');
    });

    Route::get('/deposits', 'DepositController@filter')->name('deposits.filter');
    Route::get('/withdrawals', [WithdrawalController::class, 'filter'])->name('withdrawals.filter');
    Route::post('/approve-withdraw', [WithdrawalController::class, 'approveWithdrawal']);
    Route::get('/hot-balance', [WithdrawalController::class, 'getBtcBalance']);

    Route::get('/bet-monitor', [BetMonitorController::class, 'bets'])->name('bet-monitor.filter');
    Route::get('/limits', [LimitsController::class, 'index'])->name('limits.index');
    Route::get('/risk-overview', [RiskOverviewController::class, 'index'])->name('risk.overview');
    
    Route::get('/risk-overview/address/generate', [WalletController::class, 'generateHotAddress']);

    Route::get('/user-profile/{user}', [UserController::class, 'profile'])->name('user.profile');
    Route::get('/user-profile/transactions/{user}', [TransactionController::class, 'byUser'])->name('user.transactions');
    Route::post('/user-profile/notes/{user}', [UserController::class, 'updateNotes'])->name('user.notes');

    // Casino Games Route
    Route::get('/bet-list', [BetListController::class, 'bets'])->name('bet-list.filter');
    Route::get('/game-list', [CasinoGameController::class, 'gameList'])->name('game-list');

    // KYC
    Route::get('/user-kyc', [UserKycController::class, 'filter'])->name('user.kyc');
    Route::post('/kyc-document', [UserKycController::class, 'storeDocument']);
    Route::post('/delete-document', [UserKycController::class, 'deleteDocument']);
    Route::get('/document-list/{user}', [UserKycController::class, 'documentList']);
    Route::post('/change-status', [UserKycController::class, 'updateStatus']);

    // Casino related routes
    Route::post('/casino-category-update', [casinoCategoryUpdateController::class, 'updateCasinoCategory']);
    
    // Geo Blocking
    Route::get('/geo-blocking', [GeoBlockingController::class, 'getCountryList'])->name('geo.blocking');
    Route::post('/geo-block', [GeoBlockingController::class, 'changeCountryStatus'])->name('geo.block');

    // Self-X 
    Route::put('self-x/{id}', [SelfxController::class, 'updateSelfx'])->name('selfx');
    Route::get('casino-commission', [CommissionController::class, 'getProvider'])->name('casino-commission');

    // Add funds and to change user type
    Route::post('add-funds', [AddFundsController::class, 'addFunds'])->name('add-funds');
    Route::get('switch-user/{id}', [UserController::class, 'switchUser'])->name('switch-user');
});
