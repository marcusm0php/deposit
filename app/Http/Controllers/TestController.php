<?php

namespace App\Http\Controllers;

use App\Libs\SignMD5Helper;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class TestController extends Controller
{
    protected $_client;
    protected $_request_url = 'http://t2.visastandards.com/gclients';

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

    public function subCreate(Request $request)
    {
        $data = json_encode([
            'mch_no' => '8AAA',
            'timestamp' => date('YmdHis'),
            'biz_type' => 'mchsub.create',
            'biz_content' => [
                'mch_sub_name' => 'sub1',
                'link_name' => '',
                'link_phone' => '',
                'link_email' => '',
                'bank_card' => [
                    [
                        'bank_name' => '农业银行',
                        'bank_branch_name' => '',
                        'card_type' => '',
                        'card_no' => '111',
                        'card_cvn' => '',
                        'card_expire_date' => '',
                        'cardholder_name' => '',
                        'cardholder_phone' => '',
                        'createtime' => '',
                    ]
                ],
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

    public function subBind(Request $request)
    {

        $data = json_encode([
            'mch_no' => '8AAA',
            'timestamp' => date('YmdHis'),
            'biz_type' => 'mchsub.bind.bankcard',
            'biz_content' => [
                'mch_sub_no' => '9115418458655591',
                'bank_card' => [
                    'bank_no' => '155846881',
                    'bank_name' => '中国银行',
                    'bank_branch_name' => '',
                    'card_type' => '',
                    'card_no' => '1', 
                    'card_cvn' => '',
                    'card_expire_date' => '',
                    'cardholder_name' => '',
                    'cardholder_phone' => '13264706948',
                    'createtime' => '',
                ],
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

    public function validateCode()
    {
        $data = json_encode([
            'mch_no' => '8AAA',
            'timestamp' => date('YmdHis'),
            'biz_type' => 'deposit.mchsub.bind.bankcardverify',
            'biz_content' => [
                'mch_sub_no' => '9115418458655591',
                'verfication_key' => 'verficationCode_6174bb935a4c0086927f9c65df6e18bd',
                'code' => '863621'
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

    public function subQuery()
    {
        $data = json_encode([
            'mch_no' => '8AAA',
            'timestamp' => date('YmdHis'),
            'biz_type' => 'mchsub.query',
            'biz_content' => [
                'mch_sub_no' => 'sub1',
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
                        'amount' => '1.1',
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
