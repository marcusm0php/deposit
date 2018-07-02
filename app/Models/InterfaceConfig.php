<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InterfaceConfig extends ModelBase
{
    const BIZ_TYPES = [
        'sign.verify' => 'deposit.sign.verify', // 验签
        'outtransno.verify' => 'deposit.outtransno.verify', // 验证外部商户号
        'mchsub.create' => 'deposit.mchsub.create', // 开设子账户
        'mchsub.bind.bankcard' => 'deposit.mchsub.bind.bankcard', // 子账户绑定银行卡
        'mchsub.unbind.bankcard' => 'deposit.mchsub.unbind.bankcard', // 子账户解绑定银行卡
        'mchsub.batchcreate' => 'deposit.mchsub.batchcreate', // 批量开设子账户
        'mchsub.query' => 'deposit.mchsub.query', // 子账户查询
        'mchaccnt.dispatch' => 'deposit.mchaccnt.dispatch', // 子账户分账
        'mchaccnt.withdraw' => 'deposit.mchaccnt.withdraw', // 子账户提现
    ];

    protected $table = 'interface_config';
    protected $primaryKey = 'id_interface_config';
    
    protected $fillable = [
        'mch_no', 'md5_token', 'cid_xx', 'create_time'
    ];
    
    
    
}
