<?php

namespace App\Http\Controllers;

use App\Libs\SignMD5Helper;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class TestController extends Controller
{
    protected $_client;
    protected $_request_url = 'http://yzallpay.test/gclients';

    public function __construct(Client $client)
    {
        $this->_client = $client;


        $this->_curl = curl_init();
        curl_setopt($this->_curl, CURLOPT_URL, $this->_request_url);
        curl_setopt($this->_curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($this->_curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($this->_curl, CURLOPT_POST, 1);
        curl_setopt($this->_curl, CURLOPT_HTTPHEADER, array());
        curl_setopt($this->_curl, CURLOPT_RETURNTRANSFER, 1);
    }

//Route::get('create','TestController@subCreate');
//Route::get('bind-accnt','TestController@bindAccnt');
//Route::get('unbind-accnt','TestController@unbindAccnt');
//Route::get('batch-create','TestController@batchCreate');
//Route::get('query','TestController@query');
//Route::get('accnt-dispatch','TestController@accntDispatch');
    public function subCreate(Request $request)
    {
        $methods = get_class_methods(__CLASS__);

        dd(json_decode('edew',1));
        $data = json_encode([
            'mch_no' => '8AAA',
            'timestamp' => date('YmdHis'),
            'biz_type' => 'mchsub.create',
            'out_trant_no' => time(),
            'biz_content' => [
                'mch_accnt_name' => 'sub1',
                'out_mch_accnt_no' => time(),
                'link_name' => '',
                'link_phone' => '',
                'link_email' => '',
            ],
            'sign_type' => ''
        ]);
        $token = 'TOKENTOKEN';
        $sign = SignMD5Helper::genSign($data, $token);

        curl_setopt($this->_curl, CURLOPT_POSTFIELDS, array(
            'data' => $data,
            'sign' => $sign
        ));
        $ret = curl_exec($this->_curl);
        dump($ret);
        dump(json_decode($ret,1));
        dump(json_decode($ret,true));
        echo '<br /><br />';
        die();
    }

    public function bindAccnt(Request $request)
    {
        $data = json_encode([
            'mch_no' => '8AAA',
            'timestamp' => date('YmdHis'),
            'biz_type' => 'mchsub.bind.bankcard',
            'out_trant_no' => time(),

            'biz_content' => [
                'mch_accnt_no' => '1117480490396200',
                'bank_no' => '103100000026',
                'bank_name' => '中国银行',
                'card_type' => '0',
                'card_no' => '6228480769101078376',
                'card_cvn' => '',
                'card_expire_date' => '',
                'user_name' => '冷朝',
                'card_phone' => '13264706948',
                'cert_type'=>'0',
                'cert_no' => '420281199410057236',
            ],
            'sign_type' => 'md5'
        ]);
        $token = 'TOKENTOKEN';
        $sign = SignMD5Helper::genSign($data, $token);

        curl_setopt($this->_curl, CURLOPT_POSTFIELDS, array(
            'data' => $data,
            'sign' => $sign
        ));
        $ret = curl_exec($this->_curl);
        dump($ret);
        dump(json_decode($ret,1));
        dump(json_decode($ret,true));
        echo '<br /><br />';
        die();
    }

    public function unbindAccnt()
    {
        $data = json_encode([
            'mch_no' => '8AAA',
            'timestamp' => date('YmdHis'),
            'biz_type' => 'mchsub.unbind.bankcard',
            'out_trant_no' => time(),

            'biz_content' => [
                'mch_accnt_no' => '1117480490396200',
                'card_no' => '6228480769101078376',
            ],
            'sign_type' => 'md5'
        ]);
        $token = 'TOKENTOKEN';
        $sign = SignMD5Helper::genSign($data, $token);

        curl_setopt($this->_curl, CURLOPT_POSTFIELDS, array(
            'data' => $data,
            'sign' => $sign
        ));
        $ret = curl_exec($this->_curl);
        dump($ret);
        dump(json_decode($ret,1));
        dump(json_decode($ret,true));
        echo '<br /><br />';
        die();
    }

    public function batchCreate()
    {
        $data = json_encode([
            'mch_no' => '8AAA',
            'timestamp' => date('YmdHis'),
            'biz_type' => 'mchsub.batchcreate',
            'out_trant_no' => time(),
            'biz_content' => [
                'mch_accnts'=>[
                    [
                        'mch_sub_name' => 'sub1',
                        'out_mch_accnt_no' => 1,
                        'link_name' => '1',
                        'link_phone' => '2',
                        'link_email' => '3',
                        'bank_cards' => [
                            [
                                'bank_no' => '103100000026',
                                'bank_name' => '中国银行',
                                'card_type' => '0',
                                'card_no' => '6228480769101078376',
                                'card_cvn' => '',
                                'card_expire_date' => '',
                                'user_name' => '冷朝',
                                'card_phone' => '13264706948',
                                'cert_type'=>'0',
                                'cert_no' => '420281199410057236',
                            ],
                            [
                                'bank_no' => '103100000026',
                                'bank_name' => '中国银行',
                                'card_type' => '0',
                                'card_no' => '6228480769101078376',
                                'card_cvn' => '',
                                'card_expire_date' => '',
                                'user_name' => '冷朝',
                                'card_phone' => '13264706948',
                                'cert_type'=>'0',
                                'cert_no' => '420281199410057236',
                            ],
                        ],
                    ],
                    [
                        'mch_sub_name' => 'sub4',
                        'out_mch_accnt_no' => 1,
                        'link_name' => '2',
                        'link_phone' => '2',
                        'link_email' => '2',
                        'bank_cards' => [
                            [
                                'bank_no' => '103100000026',
                                'bank_name' => '中国银行',
                                'card_type' => '0',
                                'card_no' => '6228480769101078376',
                                'card_cvn' => '',
                                'card_expire_date' => '',
                                'user_name' => '冷朝',
                                'card_phone' => '13264706948',
                                'cert_type'=>'0',
                                'cert_no' => '420281199410057236',
                            ],
                            [
                                'bank_no' => '103100000026',
                                'bank_name' => '中国银行',
                                'card_type' => '0',
                                'card_no' => '6228480769101078376',
                                'card_cvn' => '',
                                'card_expire_date' => '',
                                'user_name' => '冷朝',
                                'card_phone' => '13264706948',
                                'cert_type'=>'0',
                                'cert_no' => '420281199410057236',
                            ],
                        ],
                    ],
                ],

            ],
            'sign_type' => 'md5'
        ]);
        $token = 'TOKENTOKEN';
        $sign = SignMD5Helper::genSign($data, $token);

        curl_setopt($this->_curl, CURLOPT_POSTFIELDS, array(
            'data' => $data,
            'sign' => $sign
        ));
        $ret = curl_exec($this->_curl);
        dump($ret);
        dump(json_decode($ret,1));
        $res_arr = json_decode($ret,1);
        dump(json_decode($res_arr['data'],true));
        echo '<br /><br />';
        die();
    }

    public function query()
    {
        $data = json_encode([
            'mch_no' => '8AAA',
            'timestamp' => date('YmdHis'),
            'biz_type' => 'mchsub.query',
            'out_trant_no' => time(),
            'biz_content' => [
                'mch_accnt_no' => '1117480490396200',
            ],
            'sign_type' => ''
        ]);

        $token = 'TOKENTOKEN';
        $sign = SignMD5Helper::genSign($data, $token);

        curl_setopt($this->_curl, CURLOPT_POSTFIELDS, array(
            'data' => $data,
            'sign' => $sign
        ));
        $ret = curl_exec($this->_curl);
        dump($ret);
        dump(json_decode($ret,1));
        dump(json_decode($ret,true));
        echo '<br /><br />';
        die();
    }

    public function accntDispatch(Request $request)
    {
        $data = json_encode([
            'mch_no' => '8AAA',
            'timestamp' => date('YmdHis'),
            'biz_type' => 'mchaccnt.dispatch',
            'biz_content' => [
                'split_accnt_detail' => [
                    [
                        'mch_accnt_no' => '1',
                        'dispatch_event' => 'withdraw',
                        'amount' => '100',
                    ]
                ]
            ],
            'sign_type' => ''
        ]);
        $token = 'TOKENTOKEN';
        $sign = SignMD5Helper::genSign($data, $token);

        dump($data);
        dump($sign);

        curl_setopt($this->_curl, CURLOPT_POSTFIELDS, array(
            'data' => $data,
            'sign' => $sign
        ));
        $ret = curl_exec($this->_curl);
        dump($ret);
        echo '<br /><br />';
        die();
    }

}
