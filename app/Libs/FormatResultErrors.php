<?php 
namespace App\Libs;

class FormatResultErrors
{
    const CODE_MAP = [
        'SUCCESS' => [
            'code' => '100', 
            'message' => ''
        ], 
        'SYS.ERR' => [
            'code' => '101', 
            'message' => '系统错误', 
        ], 
        'SIGN.BIZ_TYPE.INVALID' => [
            'code' => '102', 
            'message' => 'biz_type无效', 
        ], 
        'SIGN.VERIFY.FAIL' => [
            'code' => '104', 
            'message' => '签名验证失败', 
        ], 
    ];
}
