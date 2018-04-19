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
    Route::get('customers/export-excel', 'CustomerController@exportExcel');
    Route::post('customers/import-excel', 'CustomerController@importExcel');
    Route::post('customers/upload-avatar', 'CustomerController@uploadAvatar');
    Route::resource('customers', 'CustomerController');

    Route::get('cgroups/{id}/customers', 'CgroupController@getCustomerList');
    Route::delete('cgroups/{id}/remove-customer/{customerId}', 'CgroupController@removeCustomer');
    Route::post('cgroups/{id}/add-customer/{customerId}', 'CgroupController@addCustomer');
    Route::resource('cgroups', 'CgroupController');
    Route::post('cgroups/upload-avatar', 'CgroupController@uploadAvatar');

    Route::resource('payment-histories', 'PaymentHistoryController');

    Route::get('promotions/{id}/statistic', 'PromotionController@statisticQuantityUsed');
    Route::get('promotions/{id}/statistic-by-time', 'PromotionController@statisticByTime');
    Route::get('promotions/{id}/used-customers', 'PromotionController@getListCustomerUsed');
    Route::post('promotions/{id}/active', 'PromotionController@active');
    Route::post('promotions/upload-image', 'PromotionController@uploadImage');
    Route::get('promotions/get-free', 'PromotionController@getFree');
    Route::resource('promotions', 'PromotionController');

    Route::get('helpers/{name}/{option?}', ['as' => 'helper.index', 'uses' => 'HelperController@index']);
    Route::post('check-promotion', ['as' => 'check-promotion.check', 'uses' => 'CheckPromotionController@check']);
    Route::resource('email-templates', 'EmailTemplateController');
    Route::post('email-templates/upload', 'EmailTemplateController@upload');

    Route::get('account', 'AccountController@index');
    Route::post('account/change-password', 'AccountController@changePassword');
    Route::post('account/update-profile', 'AccountController@updateProfile');
    Route::post('account/upload-avatar', 'AccountController@uploadAvatar');

    Route::resource('roles', 'RoleController');

    Route::get('permissions/by-role', 'PermissionController@getByRole');
    Route::resource('permissions', 'PermissionController');

    Route::resource('users', 'UserController');
    Route::post('users/upload-avatar', 'UserController@uploadAvatar');
    Route::put('users/{id}/reset-password', 'UserController@resetPassword');
    Route::put('users/{id}/active', 'UserController@active');

    Route::get('campaigns/preview-customer', 'CampaignController@previewCustomers');
    Route::get('campaigns/sms-coming', 'CampaignController@smsIncoming');
    Route::resource('campaigns', 'CampaignController');
    Route::get('campaigns/send-email/{id}', 'CampaignController@sendEmail');
    Route::post('campaigns/send-sms/{id}', 'CampaignController@sendSMS');
    Route::get('campaigns/statistic-sms/{id}', 'CampaignController@statisticSMS');
    Route::get('campaigns/statistic-email/{id}', 'CampaignController@statisticEmail');
    Route::get('campaigns/list-customer/{id}', 'CampaignController@showCustomers');

    Route::get('call-centers/get-token', 'CallCenterController@getToken123CS');
    Route::get('call-centers/users', 'CallCenterController@getListUser');
    Route::get('call-centers/users/{id}', 'CallCenterController@getUser');
    Route::put('call-centers/users/{id}', 'CallCenterController@updateUser');
    Route::delete('call-centers/users/{id}', 'CallCenterController@deleteUser');

    Route::get('call-centers/customers', 'CallCenterController@getListCustomer');
    Route::get('call-centers/customers/{id}', 'CallCenterController@getCustomer');
    Route::post('call-centers/customers', 'CallCenterController@createCustomer');
    Route::put('call-centers/customers/{id}', 'CallCenterController@updateCustomer');
    Route::delete('call-centers/customers/{id}', 'CallCenterController@deleteCustomer');

    Route::post('call-centers/click-to-call', 'CallCenterController@clickToCall');

    Route::get('call-histories', 'CallHistoryController@index');
    Route::resource('line-calls', 'LineCallController');
    // developer
    Route::get('/developer/client', [
        'as' => 'developer.client',
        'uses' => 'DeveloperController@getClient'
    ]);

    Route::get('/developer/events', [
        'as' => 'developer.events',
        'uses' => 'DeveloperController@getWebhookEvent'
    ]);

    Route::get('/developer/webhooks', [
        'as' => 'developer.webhooks',
        'uses' => 'DeveloperController@getWebhooks'
    ]);

    Route::delete('/developer/webhook/{id}', [
        'as' => 'developer.del_webhooks',
        'uses' => 'DeveloperController@deleteWebhook'
    ]);

    Route::post('/developer/generate', [
        'as' => 'developer.generate',
        'uses' => 'DeveloperController@generate'
    ]);

    Route::post('/developer/webhook/add', [
        'as' => 'developer.add_webhook',
        'uses' => 'DeveloperController@add'
    ]);

    Route::prefix('statistic')->group(function () {
        Route::get('rate-of-use-promotion', 'StatisticController@getRateOfUsePromotion');
    });

    Route::resource('cities', 'CityController')->only('index', 'show');

    Route::post('questions/{id}/active', 'QuestionController@active');
    
    Route::resource('questions', 'QuestionController');

    Route::resource('surveys', 'SurveyController');

    Route::resource('feedbacks', 'FeedbackController');

});