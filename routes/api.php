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

Route::any('/wx/prepay', function (Request $request) {
    
    $dataOri = $request->input('data', '');
    $sign = $request->input('sign', '');
    $data = json_decode($dataOri, true);
    
    
    echo app('api_traceno');
    
    
    $client = new \GuzzleHttp\Client();
    $response = $client->request('GET', 'http://www.baidu.com');
    $statusCode = $response->getStatusCode();
    $content = $response->getBody()->getContents();
    
    var_dump($statusCode, htmlspecialchars($content));
    
//     $ret = new FormatResult($data);
//     if(isset($data['biz_type'])){
//         $biz_type = $data['biz_type'];
//         if(!empty(InterfaceConfig::BIZ_TYPES[$biz_type])){
//             $bizRet = app('gclient')->doNormal(InterfaceConfig::BIZ_TYPES[$biz_type], json_encode([
//                 'data' => $dataOri,
//                 'sign' => $sign,
//                 'ga_traceno' => app('ga_traceno')
//             ]));
            
//             return $bizRet;
//         }
//     }
//     $ret->setError('SIGN.BIZ_TYPE.INVALID');
//     return json_encode(array(
//         'data' => $ret->getData(),
//         'sign' => ''
//     ), JSON_UNESCAPED_UNICODE);
});

