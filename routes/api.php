<?php

use App\Http\Controllers\API\CasinoController;
use App\Http\Controllers\API\TransactionController;
use App\Http\Controllers\API\WalletController;
use App\Http\Controllers\API\WithdrawalController;
use App\Http\Controllers\Bost\GeoBlockingController;
use App\Http\Controllers\encryption\OfflineSigninController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('locales/{locale}', 'LocalizationController@lang')->name('api.locales');

Route::group(['prefix' => 'auth', 'namespace' =>  'Auth'], function () {

    Route::post('register', 'RegisterController@register')->name('api.register');

    Route::post('login', 'LoginController@login')->name('api.login');

    Route::get('verify/{id}/{hash}', 'VerificationController@verify')->name('api.verify-email');

    Route::post('forgot-password', 'ForgotPasswordController@sendResetLinkEmail')->name('api.forgot-password');

    Route::post('reset-password/{method?}', 'ResetPasswordController@reset')->name('api.reset-password');

    Route::group(['middleware' => 'auth:api'], function () {
        Route::post('resend', 'VerificationController@resend')->name('api.resend-verification');

        Route::get('2fa/qr-code', 'Google2FAController@QRCode')->name('api.2fa.qr-code');

        Route::post('2fa/enable', 'Google2FAController@enable')->name('api.2fa.enable');

        Route::post('2fa/disable', 'Google2FAController@disable')->name('api.2fa.disable');

        Route::post('logout', 'LogoutController@logout')->name('api.logout');
    });
});

// Users
Route::group(['prefix' => 'users'], function () {
    Route::get('search/{value}/{field}/{filter?}', 'UserController@search')->name('users.search');

    Route::get('filter/{type}', 'UserController@filter')->name('users.filter');

    Route::put('{id}/status', 'UserController@restrict')->name('users.restrict');

    Route::put('{user}/block/current-ip', 'UserController@blockIp')->name('users.block-current-ip');
});

Route::get('faqs/welcome', 'FaqController@welcome')->name('api.faqs-welcome');

Route::group(['middleware' => 'auth:api'], function () {
    Route::group(['prefix' => 'user'], function () {
        Route::get('account', 'UserController@account')->name('user.account');
        Route::put('update', 'UserController@update')->name('user.update');
    });

    Route::group(['prefix' => 'bets'], function () {
        Route::post('/', 'BetController@placeBet')->name('bets.place-bet');
        Route::get('open', 'BetController@open')->name('bets.open');
        Route::get('settled', 'BetController@settled')->name('bets.settled');
    });

    Route::group(['prefix' => 'kyc'], function () {
        Route::get('/country', 'KycController@getCountry')->name('kyc.country');
        Route::post('/kyc-documents', 'KycController@getDocuments')->name('kyc.kyc-documents');
        Route::post('/levelone', 'KycController@getLevelOne')->name('kyc.levelone');
        Route::post('/level-one', 'KycController@storeLevelOne')->name('kyc.level-one');
        Route::post('/level-two', 'KycController@storeLevelTwo')->name('kyc.level-two');
        Route::post('/level-three', 'KycController@storeLevelThree')->name('kyc.level-three');
    });

    Route::get('wallets/{wallet}/address/generate', [WalletController::class, 'generateAddress']);
    Route::post('withdrawals', [WithdrawalController::class, 'create']);
    Route::get('transactions', [TransactionController::class, 'index']);
});

Route::group(['prefix' => 'feed'], function () {
    Route::get('popular/{status}/{sport}', 'FeedController@popular')->name('feed.popular');
    Route::get('preview/today/{sport}', 'FeedController@fromTodayPreview')->name('feed.today-preview');
    Route::get('today/{sport}', 'FeedController@fromToday')->name('feed.today');
    Route::get('soon/{sport}', 'FeedController@startingSoon')->name('feed.soon');
    Route::get('preview/live/{sport}', 'FeedController@liveSportPreview')->name('feed.live-preview');
    Route::get('live/{sport}', 'FeedController@live')->name('feed.live');
    Route::get('upcoming/{sport}', 'FeedController@upcoming')->name('feed.upcoming');
    Route::get('upcoming/{sport}/categories', 'FeedController@categories')->name('feed.sports.categories');
    Route::get('upcoming/categories/{sportCategory}', 'FeedController@byCategory')->name('feed.sport-category');
    Route::get('competitions/{country}/{sport}', 'FeedController@byCountry')->name('feed.competitions');
    Route::get('preview/upcoming', 'FeedController@preview')->name('feed.upcoming-preview');
    Route::get('preview/live', 'FeedController@livePreview')->name('feed.live-preview');
});

Route::group(['prefix' => 'bet-slip'], function () {
    Route::post('update', 'BetSlipController@update')->name('api.bet-slip.update');
});

Route::group(['prefix' => 'matches'], function () {
    Route::get('{match}', 'MatchController@show')->name('matches.show');
    Route::get('{match}/odds', 'MatchController@odds')->name('matches.odds');
});

Route::group(['prefix' => 'sports'], function () {
   Route::get('/', 'SportController@index')->name('sports.index');
   Route::get('/live', 'SportController@live')->name('sports.live');
   Route::get('/soon', 'SportController@soon')->name('sports.soon');
});

Route::group(['prefix' => 'home'], function () {
    Route::get('/geo-check', [GeoBlockingController::class, 'checkGeoLocation'])->name('home.geo-check');
});

Route::group(['prefix' => 'casino'], function () {
   Route::get('/games', [CasinoController::class, 'games'])->name('casino.games');
   Route::get('/games/{user}/{game}', [CasinoController::class, 'game'])->name('casino.game');
   Route::get('/providers', [CasinoController::class, 'providers'])->name('casino.providers');
   Route::get('casino-providers', [CasinoController::class, 'providersHasGame']);
   Route::post('/init/{game}', [CasinoController::class, 'init'])->name('casino.init');
   Route::get('/favourite/{user}/{game}', [CasinoController::class, 'addFavourite'])->name('casino.favourite');
   Route::get('/conversion/{ticker}', [CasinoController::class, 'conversion'])->name('casino.conversion');
   Route::get('/category', [CasinoController::class, 'getCategory'])->name('casino.category');
});

Route::get('countries/{sport}', 'CountryController@withMatchesBySport')->name('countries.index');
Route::get('/maintenance', [GeoBlockingController::class, 'getMaintenance'])->name('api.getMaintenance');
Route::get('currency', [CasinoController::class, 'conversion']); // Call to update footer USD & EUR balance

Route::get('signin-encrypt', [OfflineSigninController::class, 'encrypt']); // Next Phase work
Route::get('signin-decrypt', [OfflineSigninController::class, 'decrypt']); // // Next Phase work

Route::apiResources([
    'users' => 'UserController',
    'faqs' => 'FaqController',
    'promotions' => 'PromotionController'
]);
