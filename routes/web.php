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

Route::get('/', function () {
	$banana_config = \Config::get('banana');
	$banana_config['pack_sizes_str'] = implode(', ', $banana_config['pack_sizes']);
    return view('welcome')->with('banana_config', $banana_config);
});


Route::post( '/ajaxbanana', array(
    'as' => 'ajaxbananacontroller.calculate',
    'uses' => 'ajaxbananacontroller@calculate'
));
Route::get( '/ajaxbanana', array(
    'as' => 'ajaxbananacontroller.calculate',
    'uses' => 'ajaxbananacontroller@calculate'
));
