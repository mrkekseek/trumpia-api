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

Route::group(['prefix' => 'v1', 'middleware' => 'token'], function() {
	//Route::any('{unit}/{method}', 'ApiController@run');
	Route::post('company/name', 'CompanyController@name');
	Route::delete('company/{name}', 'CompanyController@remove');
	
	Route::post('message/send', 'MessageController@send');

	Route::post('keyword/create', 'KeywordController@create');
});

Route::post('push', 'TrumpiaController@push');
Route::get('inbox', 'TrumpiaController@inbox');