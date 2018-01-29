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
    Route::resource('customers', 'CustomerController');
    Route::resource('payment-histories', 'PaymentHistoryController');
    Route::resource('promotions', 'PromotionController');
    Route::get('helpers/{name}/{option?}', ['as' => 'helper.index', 'uses' => 'HelperController@index']);
    Route::post('check-promotion', ['as' => 'check-promotion.check', 'uses' => 'CheckPromotionController@check']);
    Route::get('account', 'AccountController@index');
    Route::post('account/change-password', 'AccountController@changePassword');
    Route::post('account/update-profile', 'AccountController@updateProfile');
    Route::post('account/upload-avatar', 'AccountController@uploadAvatar');
    Route::resource('roles', 'RoleController');
    Route::get('permissions/by-role', 'PermissionController@getByRole');
    Route::resource('permissions', 'PermissionController');
});