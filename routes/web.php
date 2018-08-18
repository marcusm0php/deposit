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
//    app('easysms')->send($phone,$sms_data);
    return ['1'];
    return view('welcome');
});

Route::group(['prefix'=>'mchsub'],function(){
    Route::get('create','TestController@subCreate');
    Route::get('bind-accnt','TestController@bindAccnt');
    Route::get('unbind-accnt','TestController@unbindAccnt');
    Route::get('batch-create','TestController@batchCreate');
    Route::get('query','TestController@query');
    Route::get('accnt-dispatch','TestController@accntDispatch');
});

Route::group(['prefix'=>'cibpay'],function(){

    Route::get('pyPay','TestCibController@pyPay');
    Route::get('acSingleAuth','TestCibController@acSingleAuth');
    Route::get('entrustAuth','TestCibController@entrustAuth');
    Route::get('quickAuthSMS','TestCibController@quickAuthSMS');

});