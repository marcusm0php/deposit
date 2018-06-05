<?php 
namespace App\Libs;

class FormatResultErrors
{
    const CODE_MAP = [
        'SUCCESS' => ['code' => '100', 'message' => ''], 
        'SYS.ERR' => ['code' => '101', 'message' => '系统错误', ], 
        'SIGN.BIZ_TYPE.INVALID' => ['code' => '102', 'message' => 'biz_type无效', ], 
        'SIGN.VERIFY.FAIL' => ['code' => '104', 'message' => '签名验证失败', ], 
        'MCHSUB.CREATE.MCHSUB.NAME.REPEAT' => ['code' => '201', 'message' => '子商户名已存在', ], 
        'MCHSUB.CREATE.BANKCARD.EMPTY' => ['code' => '202', 'message' => '子商户银行卡信息不能为空', ], 
        'MCHSUB.CREATE.BANKCARD.ERROR' => ['code' => '203', 'message' => '子商户银行卡信息有误', ],
        'MCHSUB.MCHSUBNO.INVALID' => ['code' => '204', 'message' => '子商户号无效', ],
        'MCHSUB.CREATE.BANKCARD.REPEAT' =>  ['code' => '205', 'message' => '子商户绑定银行卡信息重复', ],

        'MCHSUB.QUERY.NOTFOUND' =>  ['code' => '404', 'message' => '子商户不存在',],
    ];
}
