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

Route::group(['middleware' => 'guest'], function () {
    Route::get('/', function () {
        return view('welcome');
    })->name('index');
    Route::any('login', ['as' => 'login', 'uses' => 'UserController@loginAction']);
});

Route::group(['middleware' => 'auth'], function () {
    Route::get('/', function () {
        return Redirect::to('shipments');
    })->name('index');

    Route::get('shipments',                ['as' => 'shipments',      'uses' => 'ShipmentController@shipments']);
    Route::any('shipment/create',          ['as' => 'shipmentCreate', 'uses' => 'ShipmentController@shipmentCreate']);
    Route::any('item/create/{shipmentId}', ['as' => 'itemCreate',     'uses' => 'ItemController@itemCreate']
        )->where('shipmentId', '[0-9]+');
    Route::get('item/delete/{id}',         ['as' => 'itemDelete',     'uses' => 'ItemController@itemDelete']
        )->where('id', '[0-9]+');
    Route::any('logout',                   ['as' => 'logout',         'uses' => 'UserController@logoutAction']);
});