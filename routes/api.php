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

// Log routes
Route::group(['middleware' => ['check']], function() {
    Route::post('logs/add', 'LogsController@addLog');
    Route::get('logs/search', 'LogsController@search');
    Route::get('logs/detail', 'LogsController@detail');
});