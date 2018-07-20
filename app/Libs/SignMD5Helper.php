<?php
/**
 * User: zhao
 * Date: 2018/6/1
 * Time: 11:46
 */
namespace App\Libs;


class SignMD5Helper
{
    public static function verify($data, $sign, $token, &$signCal = '')
    {
        $signCal = self::genSign($data, $token);

        return $signCal == $sign;
    }

    public static function genSign($data, $token)
    {
        $dataDe = json_decode($data, true);
        $timestamp = empty($dataDe['timestamp'])? '' : $dataDe['timestamp'];
        dump($data);
        dump($timestamp);
dump($token);
        return strtolower(
            md5(
                $data . '&' . $timestamp . $token
            )
        );
    }

    public static function genNotiMchSign($data, $token)
    {
        $dataDe = json_decode($data, true);
        $out_trade_no = empty($dataDe['out_trade_no'])? '' : $dataDe['out_trade_no'];

        return strtolower(
            md5(
                $data . '&' . $out_trade_no . $token
            )
        );
    }
    public static function genNotiHlbSign($data,$token){
        $dataDe = json_decode($data, true);
        $orderId = empty($dataDe['rt5_orderId'])? '' : $dataDe['rt5_orderId'];

        return strtolower(
            md5(
                $data . '&' . $orderId . $token
            )
        );
    }
}