<?php

use Illuminate\Http\Request;

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

Route::post('user/registration', 'API\UserAPIController@registration');
Route::post('user/login', 'API\UserAPIController@user_login');

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// promocodes
Route::get('promocodes', 'API\PromocodeController@index');
Route::get('promocodes/verify/{code}', 'API\PromocodeController@verify');

Route::get('categories/list', 'API\CategoryController@list');
Route::get('categories/list/{id}', 'API\CategoryController@list');
Route::post('products/list/{business_id}', 'API\ProductController@list');
Route::get('products/details/{id}/{variation_id}', 'API\ProductController@details');

Route::get('wallet/transactional', 'API\WalletController@transactional');
Route::get('wallet/promotional', 'API\WalletController@promotional');

Route::middleware('auth:api')->group(function () {

    Route::post('wallet/transactional/add', 'API\WalletController@store');

	Route::get('address', 'API\AddressAPIController@index');
    Route::post('address/add', 'API\AddressAPIController@store');
    Route::post('address/update/{id}', 'API\AddressAPIController@update');
    Route::post('address/delete/{id}', 'API\AddressAPIController@destroy');
});

Route::group(['prefix' => 'sync'], function () {
    Route::get('categories', 'API\SYNC\CategoryController@index');
    Route::get('location', 'API\SYNC\LocationController@index');
    Route::get('products', 'API\SYNC\ProductController@index');
    Route::get('users', 'API\SYNC\SyncDataFromApiController@getCustomer');
    Route::get('sync-users', 'API\SYNC\SyncDataFromApiController@index');
    Route::get('wallets', 'API\SYNC\WalletController@index');
    Route::post('users-synced', 'API\SYNC\SyncDataFromApiController@syncedCustomers');
     Route::get('order', 'API\SYNC\SynOrderFromApiController@index');
     Route::get('order-list', 'API\SYNC\SynOrderFromApiController@getOrders');
     Route::post('sync-order', 'API\SYNC\SynOrderFromApiController@syncedOrders');
});