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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/register', 'Api\AuthController@register');
Route::post('/login', 'Api\AuthController@login');
Route::get('/verify/{token}', 'Api\AuthController@verifyToken');

Route::group([
    'middleware' => 'auth:api',
    'prefix'     => 'user',
], function () {
    Route::get('/', 'Api\UserController@getMe');
    Route::put('/', 'Api\UserController@update');
    Route::delete('/', 'Api\UserController@delete');
});

Route::group([
    'middleware' => 'auth:api',
    'prefix'     => 'wellcome',
], function () {
    Route::get('/', function () {
        return view('welcome');
    });
});
