<?php

namespace App\Http\Controllers;

use App\Libs\Interfaces\CibInterface;
use App\Libs\Interfaces\CibUtil;


class TestCibController extends Controller
{
    protected  $_cibpay;

    /*public function __construct(CibInterface $cibpay)
    {
        dd(1);
        $this->_cibpay = $cibpay;
    }*/

    /**
     * Ex.3-1 智能代付单笔付款
     */
    public function pyPay()
    {
        // 流程：商户系统 --post--> 收付直通车
        // $order_no是商户订单号，由商户系统生成，应当注意订单号在商户系统中应当全局唯一，即不会出现两笔订单有相同的订单号

        $order_no       = "SDK".date('YmdHis');       //这里示例使用SDK20150806120001格式的订单号
        $trans_amt      = "0.01";                                  //支付金额
        $to_bank_no     = "103100000026";                           //收款行行号
        $to_acct_no     = "62284807691010783761";                     //收款人账号
        $to_acct_name   = "冷朝";                                 //收款人户名
        $trans_usage    = "这笔订单是由SDK发起的示例订单";          //订单详情
        $acc_type       = 1;                                    //账户类型(0-储蓄卡;1-信用卡;2-企业账户)

        $data = [
            'order_no' => "SDK".date('YmdHis'),
            'to_bank_no' => '103100000026',
            'to_acct_no' => '62284807691010783761',
            'to_acct_name' => '冷朝',
            'acct_type' => 1,
            'trans_amt' => '0.01',
            'trans_usage' => '这笔订单是由SDK发起的示例订单',
        ];

        $this->_cibpay = new CibInterface();
        $result    = $this->_cibpay->pyPay($data);
        dd($result);
        // 返回结果为JSON格式的字符串，具体含义请参看收付直通车代收接口文档
    }

    //无页面独立鉴权接口
    public function acSingleAuth()
    {
        $trac_no       = date('YmdHis');       //系统跟踪号
        $card_no      = "622848076910107837";        //卡号
        $bank_no     = "103100000026";               //银行代码 //http://220.250.30.210:7052/cibhall/images/bank.unl
        $card_phone     = "18348086697";             //银行预留手机号码
        $cert_no   = "420281199410057236";           //证件号
        $cert_type    = "0";                         //证件类型 0-身份证(目前仅支持身份证)
        $acct_type       = 0;                        //账户类型(0-储蓄卡;1-信用卡;)
        $user_name='冷朝';                   //行用卡认证必填
        //$expireDate='', $cvn=''            //行用卡认证必填
        $auth_data = [
            'trac_no' => uniqid(),
            'card_no' => '6228480769101078376',
            'bank_no' => '103100000026',
            'acct_type' => 0,
            'cert_type' => 0,
            'cert_no' => $cert_no,
            'card_phone' => '18348086697',
//            'expireDate' => $bizContentFormat['card_expire_date'],
//            'cvn' => $bizContentFormat['card_cvn'],
            'user_name' => $user_name,
        ];
        $this->_cibpay = new CibInterface();
        $result    = $this->_cibpay->acSingleAuth($auth_data);
        dd($result);
    }

    //  托收账户异步认证接口
    public function entrustAuth()
    {
        $request_data = [
            'timestamp'=>CibUtil::getDateTime(),
            'trac_no'=>CibUtil::getDateTime(),
            'card_no'=>'6228480769101078376',
            'bank_no'=>'103100000026',
            'acct_type'=>0,
            'cert_type'=>0,
            'cert_no'=>'420281199410057236',
            'card_phone'=>'420281199410057236',
            'user_name'=>'冷朝',
            //可选
            'expireDate' => '',
            'cvn' => '',
        ];

        $this->_cibpay = new CibInterface();
        $result    = $this->_cibpay->entrustAuth($request_data);
        dd($result);
    }

    //  托收线上认证接口( 同步)
    public function quickAuthSMS()
    {
        $request_data = [
            'timestamp'=>CibUtil::getDateTime(),
            'trac_no'=>CibUtil::getDateTime(),
            'card_no'=>'6228480769101078376',
            'bank_no'=>'103100000026',
            'acct_type'=>0,
            'cert_type'=>0,
            'cert_no'=>'420281199410057236',
            'card_phone'=>'15057485412',
            'user_name'=>'冷朝',
            //可选
            'expireDate' => '',
            'cvn' => '',
        ];

        $this->_cibpay = new CibInterface();
        $result    = $this->_cibpay->quickAuthSMS($request_data);
        dd($result);
    }

}
