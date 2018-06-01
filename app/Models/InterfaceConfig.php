<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InterfaceConfig extends Model
{
    const BIZ_TYPES = [
        'SIGN.VERIFY' => 'deposit.sign.verify',					// 验签
    ];
    
    
    const CREATED_AT = 'create_time';
    const UPDATED_AT = '';
    protected $dateFormat = 'Y-m-d H:i:s';
    protected $table = 'interface_config';
    protected $primaryKey = 'id_interface_config';
    
    protected $fillable = [
        'mch_no', 'md5_token', 'cid_xx', 'create_time'
    ];
    
    
    
}
