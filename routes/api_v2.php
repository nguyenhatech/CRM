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

Route::post('login', ['as' => 'developer.login', 'uses' => 'LoginController@login']);

Route::group([
    'middleware' => 'auth:api',
], function () {
    Route::resource('promotions', 'PromotionController');
    Route::post('promotions/{id}/active', 'PromotionController@active');
    Route::post('promotions/upload-image', 'PromotionController@uploadImage');
});