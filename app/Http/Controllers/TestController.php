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
    }

    public function subCreate(Request $request)
    {
        $data = json_encode([ 
			'mch_no' => '8AAA',
			'timestamp' => date('YmdHis'),
			'biz_type' => '',
			'code' => '',
			'message' => '',
			'biz_content' => [
				'mchsub_no' => '',
				'mchsub_name' => '',
				'bankcard' => [
					[
						'mchno' => '',
						'mchsub_no' => '',
						'bankname' => '',
						'bankname_branch' => '',
						'cardno' => '',
						'createtime' => '',
					],
				],
			], 
			'sign_type' => ''
        ]);
        $token = 'TOKENTOKEN';
        $sign = SignMD5Helper::genSign($data, $token);
		
		dump($data);
		dump($sign);

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
