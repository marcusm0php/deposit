<?php

namespace App\Models;

use App\Models;
use Illuminate\Database\Eloquent\Model;

class SysGearmanConfig extends ModelBase
{
    protected $table = 'sys_gearman_config';
    protected $primaryKey = 'id_sys_gearman_config';
    
    protected $fillable = [
        'gearmand_srv_ip', 'gearmand_srv_port', 'inetip', 'channel',  'shop', 'year', 'colorcode', 'color', 'price', 'spec_size', 'remain_num'
    ];
}
