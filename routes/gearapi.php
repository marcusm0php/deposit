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

Route::any('/gclients', function (Request $request) {
    $dataOri = $request->input('data', '');
    $sign = $request->input('sign', '');
    $data = json_decode($dataOri, true);
    dd($mch_md5_token = $request->all());
    die();
    
    $bizRet = app('gclient')->doNormal(InterfaceConfig::BIZ_TYPES['SIGN.VERIFY'], json_encode([
        'data' => $dataOri,
        'sign' => $sign,
        'ga_traceno' => app('ga_traceno')
    ]));
});
