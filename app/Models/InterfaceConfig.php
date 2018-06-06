<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InterfaceConfig extends ModelBase
{
    const BIZ_TYPES = [
        'sign.verify' => 'deposit.sign.verify',					        // 验签
        
        'mchsub.create' => 'deposit.mchsub.create',                     // 创建子商户
        'mchsub.bind.bankcard' => 'deposit.mchsub.bind.bankcard',       // 子商户绑定银行卡

        'mchsub.query' => 'deposit.mchsub.query',                       // 子商户查询
        'mchaccnt.dispatch' => 'deposit.mchaccnt.dispatch'               // 商户分账
    ];
    
    
    protected $table = 'interface_config';
    protected $primaryKey = 'id_interface_config';
    
    protected $fillable = [
        'mch_no', 'md5_token', 'cid_xx', 'create_time'
    ];
    
    
    
}
