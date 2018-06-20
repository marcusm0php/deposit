<?php
/**
 * Created by PhpStorm.
 * User: zhao
 * Date: 2018/6/8
 * Time: 9:17
 */

namespace App\Libs\Interfaces;


use Overtrue\EasySms\EasySms;

class SmsInterface
{
    /**
     * 发送手机验证码
     * @param $phone 手机号
     * @return array
     * @throws \Overtrue\EasySms\Exceptions\InvalidArgumentException
     * @throws \Overtrue\EasySms\Exceptions\NoGatewayAvailableException
     */
    public static function sendCode($phone, $sms_data)
    {
        try {
            app('easysms')->send($phone,$sms_data);
        } catch (\GuzzleHttp\Exception\ClientException $exception) {
            $response = $exception->getResponse();
            $rel_msg = json_decode($response->getBody()->getContents(), true);

            $res = ['code'=>500,'msg'=>'短信验证码发送错误','rel_msg'=>json_encode($rel_msg)];

            \Log::error($res['rel_msg']);

            return $res;
        }

        return ['code'=>200,'msg'=>'验证码发送成功','rel_msg'=>'验证码发送成功'];
    }
}