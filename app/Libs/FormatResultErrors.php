<?php 
namespace App\Libs;

class FormatResultErrors
{
    const CODE_MAP = [
        'SUCCESS' => [
            'code' => '100', 
        ], 
        'SYS.ERR' => [
            'code' => '101', 
            'message' => '系统错误', 
        ], 
        'SIGN.VERIFY.FAIL' => [
            'code' => '104', 
            'message' => '签名验证失败', 
        ], 
    ];
}
