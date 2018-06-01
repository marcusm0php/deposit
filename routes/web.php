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
    dd('exit');
    return view('welcome');
});

Route::group(['prefix'=>'wxpay'],function(){
    //微信支付
    Route::get('prepay','TestController@wxPrepay');
    //支付查询
    Route::get('prepay-query','TestController@wxPrepayQuery');
    //jsapi支付
    Route::get('jspay','TestController@wxJspay');
    //app支付
    Route::get('app-pay','TestController@wxAppPay');
    //支付查询
    Route::get('query','TestController@wxQuery');
    //退款
    Route::get('refund','TestController@wxRefund');
});