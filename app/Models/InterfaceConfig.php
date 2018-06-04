<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InterfaceConfig extends ModelBase
{
    const BIZ_TYPES = [
        'sign.verify' => 'deposit.sign.verify',					// 验签
        
        'mchsub.create' => 'deposit.mchsub.create'              //创建子商户
    ];
    
    
    protected $table = 'interface_config';
    protected $primaryKey = 'id_interface_config';
    
    protected $fillable = [
        'mch_no', 'md5_token', 'cid_xx', 'create_time'
    ];
    
    
    
}
