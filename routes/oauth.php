<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
| Attention !! Les routes sont préfixées avec 'oauth/'
| Ces routes modifient celles définies par laravel-passport
*/


// Liste des Scopes
Route::get('scopes', '\App\Services\Scopes@all');
Route::get('scopes/categories', '\App\Services\Scopes@getAllByCategories');

// Clients
Route::get('clients', '\App\Http\Controllers\Passport\ClientController@index')
		->middleware(['forceJson', 'web', 'auth']);
Route::post('clients', '\App\Http\Controllers\Passport\ClientController@store')
		->middleware(['forceJson', 'web', 'auth', 'permission:client']);
Route::put('clients/{client_id}', '\App\Http\Controllers\Passport\ClientController@update')
		->middleware(['forceJson', 'web', 'auth', 'permission:client']);
Route::delete('clients/{client_id}', '\App\Http\Controllers\Passport\ClientController@destroy')
		->middleware(['forceJson', 'web', 'auth', 'permission:client']);

// Authorizations
Route::get('authorize', '\Laravel\Passport\Http\Controllers\AuthorizationController@authorize')
		->middleware(['web', 'auth', 'checkPassport']);

// Tokens
Route::post('token', '\Laravel\Passport\Http\Controllers\AccessTokenController@issueToken')
		->middleware(['forceJson', 'throttle', 'checkPassport']);
Route::post('personal-access-tokens', '\Laravel\Passport\Http\Controllers\PersonalAccessTokenController@store')
		->middleware(['forceJson', 'web', 'auth', 'checkPassport']);
