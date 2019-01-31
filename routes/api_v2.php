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
    // Modul promotion
    Route::resource('promotions', 'PromotionController');
    Route::post('promotions/{id}/active', 'PromotionController@active');
    Route::post('promotions/upload-image', 'PromotionController@uploadImage');

    // Modul payment-histories
    Route::post('payment-histories/soft-delete', 'PaymentHistoryController@softDelete');
    Route::post('update-payment-histories', 'PaymentHistoryController@updatePaymentHistory');
    Route::resource('payment-histories', 'PaymentHistoryController');

    // Modul check-promotion
    Route::post('check-promotion', ['as' => 'check-promotion.check', 'uses' => 'CheckPromotionController@check']);

    /**
     * Invite friends
     * */ 
    Route::post('invite-friends', 'InviteFriendController@store');
    Route::get('invite-friends-index', 'InviteFriendController@index');

    // Customers
    Route::resource('customers', 'CustomerController');
});