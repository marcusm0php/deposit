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
    return view('welcome');
});

Route::group(['prefix'=>'merchant'],function(){
    Route::get('create','TestController@subCreate');
    Route::get('subbind','TestController@subBind');
    Route::get('subquery','TestController@subQuery');
    Route::get('accntDispatch','TestController@accntDispatch');
});
