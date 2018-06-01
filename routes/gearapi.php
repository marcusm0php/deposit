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
    list($msec, $sec) = explode(' ', microtime());
    $msec = str_replace('0.', '', $msec);
    $msec = substr($msec, 0, 3);
        
    $mchno = 
        '8' . 
        (date('Y', $sec)-2017) . 
        str_pad( date('z', $sec), 3, '0', STR_PAD_LEFT ) . 
        str_pad( date('H', $sec)*60*60 + date('i', $sec)*60 + date('s', $sec), 3, '0', STR_PAD_LEFT )
    ;
    pre( $mchno );
    
    $mchno .= $msec;

    $mchno .= str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
    pre( $mchno );
});
