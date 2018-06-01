<?php

namespace App\Http\Controllers;

use App\Libs\SignMD5Handler;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class TestController extends Controller
{
    protected $_client;
    protected $_request_url = 'http://t2.visastandards.com/gclients';

    public function __construct(Client $client)
    {
        $this->_client = $client;
    }

    public function wxPrepay(Request $request)
    {
        $data = json_encode(array(
            'merchant_id' => '80000002',
            'term_id' => '',
            'timestamp' => '',
            'biz_type' => 'wx.prepay',
            'biz_content' => array(
                'trade_no' => time(),
                'total_amount' => '0.01',
                'body' => '',
                'notify_url' => 'http://seven.tunnel.echomod.cn/sleb/test',
                'attach' => ''
            ),
            'sign_type' => 'md5'
        ));
        $token = '6927916343854833973ed27dcc574353';
        $sign = SignMD5Handler::genSign($data, $token);

        $response = $this->_client->request('post',$this->_request_url,[
            'form_params'=>[
                'data'=>$data,
                'sign'=>$sign
            ],
        ]);

        $result = $response->getBody();
        dump($result);

        dd(json_decode($result,true));
    }

    public function wxPrepayQuery()
    {
        $data = json_encode(array(
            'merchant_id' => '8881',
            'term_id' => '88810001',
            'timestamp' => '',
            'biz_type' => 'wx.prepayquery',
            'biz_content' => array(
                'trade_no' => '1498446928',
            ),
            'sign_type' => 'md5'
        ));
        $sign = '';

        $response = $this->_client->request('post',$this->_request_url,[
            'form_params'=>[
                'data'=>$data,
                'sign'=>$sign
            ],
        ]);

        $result = $response->getBody();
        dump($result);
        dd(json_decode($result,true));
    }

    public function wxJspay()
    {
        $data = json_encode(array(
            'merchant_id' => '80000001',
            'term_id' => 'ttt80000001ttt',
            'timestamp' => '',
            'biz_type' => 'wx.jspay',
            'biz_content' => array(
                'trade_no' => time(),
                'total_amount' => '0.01',
                'body' => '',
                'notify_url' => '',
                'attach' => '',
                'openid' => '',
                'exts' => json_encode(array(
                    'requestFrom' => 'WAP',
                    'app_name' => '',
                    'bundle_id' => '',
                    'package_name' => '',
                    'wap_url' => 'http://baidu.com'
                )),
                'callback_url' => 'http://baidu.com',
                'type' => ''
            ),
            'sign_type' => 'md5'
        ));
        $token = 'a4147fb821c24fc0bc8275c7e5d09f8d';
        $sign = SignMD5Handler::genSign($data, $token);

        $response = $this->_client->request('post',$this->_request_url,[
            'form_params'=>[
                'data'=>$data,
                'sign'=>$sign
            ],
        ]);

        $result = $response->getBody();
        dump($result);
        dd(json_decode($result,true));
    }

    public function wxAppPay()
    {
        $data = json_encode(array(
            'merchant_id' => '8881',
            'term_id' => '88810001',
            'timestamp' => '',
            'biz_type' => 'wx.apppay',
            'biz_content' => array(
                'trade_no' => time(),
                'total_amount' => '0.01',
                'body' => '',
                'notify_url' => '',
                'attach' => '',
                'callback_url' => '',
            ),
            'sign_type' => 'md5'
        ));
        $sign = '';

        $response = $this->_client->request('post',$this->_request_url,[
            'form_params'=>[
                'data'=>$data,
                'sign'=>$sign
            ],
        ]);

        $result = $response->getBody();
        dump($result);
        dd(json_decode($result,true));
    }

    public function wxQuery()
    {
        $data = json_encode(array(
            'merchant_id' => '8881',
            'term_id' => '88810001',
            'timestamp' => '',
            'biz_type' => 'wx.query',
            'biz_content' => array(
                'trade_no' => '1498446928',
                'wx_trade_no' => '',
            ),
            'sign_type' => 'md5'
        ));
        $sign = '';

        $response = $this->_client->request('post',$this->_request_url,[
            'form_params'=>[
                'data'=>$data,
                'sign'=>$sign
            ],
        ]);

        $result = $response->getBody();
        dump($result);
        dd(json_decode($result,true));
    }

    public function wxRefund()
    {
        $data = json_encode(array(
            'merchant_id' => '80000002',
            'term_id' => '800000010001',
            'timestamp' => '',
            'biz_type' => 'wx.refund',
            'biz_content' => array(
                'trade_no' => '1500287719',
                'wx_trade_no' => '',
                'refund_amount' => '0.01'
            ),
            'sign_type' => 'md5'
        ));
        $token = '6927916343854833973ed27dcc574353';
        $sign = SignMD5Handler::genSign($data, $token);

        $response = $this->_client->request('post',$this->_request_url,[
            'form_params'=>[
                'data'=>$data,
                'sign'=>$sign
            ],
        ]);

        $result = $response->getBody();
        dump($result);
        dd(json_decode($result,true));
    }
}
