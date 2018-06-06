<?php

namespace App\Http\Controllers;

use App\Handlers\EpayHandler;
use Illuminate\Http\Request;

class EpayNotifyController extends Controller
{
    public function notify()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $epay = new EpayHandler();

        if('GET' === $method && $epay -> VerifyMac($_GET, config('epay')['commKey']) ||
            'POST' === $method && $epay -> VerifyMac($_POST, config('epay')['commKey'])) {	//验签成功

            if('GET' === $method) {				//前台通知

                // 商户可以在这边进行 [前台] 回调通知的业务逻辑处理
                // 注意：后台通知和前台通知有可能同时到来，注意 [需要防止重复处理]
                // 前台跳转回来的通知，需要显示内容，如支付成功等
                if("NOTIFY_ACQUIRE_SUCCESS" === $_GET["event"]) {			//支付成功通知

                    $order_no = $_GET["order_no"];
                    // $order_amount = ......
                    // 商户可以从$_GET中获取通知中的数据
                    // 然后进行支付成功后的业务逻辑处理，这里为写入notify_log.txt文件
                    file_put_contents("notify_log.txt", "[前台通知]订单".$order_no."支付成功@".date('YmdHis')."\r\n", FILE_APPEND);

                    // 这里是用户跳转到商户回调地址时显示的内容
                    echo "订单".$order_no."支付成功@".date('YmdHis');

                } else if("NOTIFY_ACQUIRE_FAIL" === $_GET["event"])	{		// 支付失败通知

                    // 支付失败业务逻辑处理

                } else if("NOTIFY_REFUND_SUCCESS" === $_GET["event"]) {		// 退款成功通知

                    // 退款成功业务逻辑处理

                } else if("NOTIFY_AUTH_SUCCESS" === $_GET["event"]) {		// 快捷支付认证成功通知

                    // 认证成功业务逻辑处理
                }

            } else if('POST' === $method) {		// 后台通知

                // 商户可以在这边进行 [后台] 回调通知的业务逻辑处理
                // 注意：后台通知和前台通知有可能同时到来，注意 [需要防止重复处理]
                if("NOTIFY_ACQUIRE_SUCCESS" === $_POST["event"]) {			// 支付成功通知

                    // 支付成功业务逻辑处理
                    $order_no = $_POST["order_no"];
                    file_put_contents("notify_log.txt", "[后台通知]订单".$order_no."支付成功@".date('YmdHis')."\r\n", FILE_APPEND);

                    //后台通知用户不会看到页面，所以不需要显示页面内容

                } else if("NOTIFY_ACQUIRE_FAIL" === $_POST["event"])	{	// 支付失败通知

                    // 支付失败业务逻辑处理

                } else if("NOTIFY_REFUND_SUCCESS" === $_POST["event"]) {	// 退款成功通知

                    // 退款成功业务逻辑处理

                } else if("NOTIFY_AUTH_SUCCESS" === $_POST["event"]) {		// 快捷支付认证成功通知

                    // 认证成功业务逻辑处理
                    file_put_contents("notify_log.txt", "[后台通知]认证成功@".date('YmdHis')."\r\n", FILE_APPEND);
                }
            }

        } else {					// 验签失败

            // 不应当进行业务逻辑处理，即把该通知当无效的处理
            // 商户可以在此记录日志等
            echo "验签失败";
        }
    }
}
