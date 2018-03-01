<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::group([
    'middleware' => ['auth', 'role:superadmin'],
], function () {
    Route::get('admin/logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index');
});


Route::get('/', function () {
    return "I'm ok";
});

// Disable auth function in api, chỉ giữ lại chức năng quên mật khẩu
//
Auth::routes();

// Route::get('/login', function() {
//     // return redirect()->to(getenv('APP_URL'));
// })->name('login');

// Route::post('/login', function() {
//     return redirect()->to(getenv('APP_URL'));
// });

Route::get('/register', function() {
    return redirect()->to(getenv('APP_URL'));
})->name('register');

Route::post('/register', function() {
    return redirect()->to(getenv('APP_URL'));
});

// Route::get('/home', 'HomeController@index')->name('home');

Route::get('/home', 'HomeController@index')->name('home');
