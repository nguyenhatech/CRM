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

Route::post('login', ['as' => 'login', 'uses' => 'LoginController@login']);

Route::group([
    'middleware' => 'auth:api',
], function () {
    Route::resource('clients', 'ClientController');
    Route::resource('promotions', 'PromotionController');
});