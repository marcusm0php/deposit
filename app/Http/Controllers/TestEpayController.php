<?php

namespace App\Http\Controllers;

use App\Handlers\EpayHandler;
use Illuminate\Http\Request;

class TestEpayController extends Controller
{
    protected  $_epay;

    public function __construct(EpayHandler $epay)
    {
        $this->_epay = $epay;
    }

    /**
     * Ex.3-1 智能代付单笔付款
     */
    public function pyPay()
    {

        // 流程：商户系统 --post--> 收付直通车
        // $order_no是商户订单号，由商户系统生成，应当注意订单号在商户系统中应当全局唯一，即不会出现两笔订单有相同的订单号

        $order_no       = "SDK".now()->toDateTimeString();       //这里示例使用SDK20150806120001格式的订单号
        $trans_amt      = "10.00";                                  //支付金额
        $to_bank_no     = "309391000011";                           //收款行行号
        $to_acct_no     = "622909115001762912";                     //收款人账号
        $to_acct_name   = "华英雄";                                 //收款人户名
        $trans_usage    = "这笔订单是由SDK发起的示例订单";          //订单详情
        $acc_type       = 0;                                    //账户类型(0-储蓄卡;1-信用卡;2-企业账户)

        $result    = $this->_epay->pyPay($order_no, $to_bank_no, $to_acct_no, $to_acct_name, '0', $trans_amt, $trans_usage);
        dd($result);
        // 返回结果为JSON格式的字符串，具体含义请参看收付直通车代收接口文档
    }

}
