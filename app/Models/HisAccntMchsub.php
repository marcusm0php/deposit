<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HisAccntMchsub extends Model
{
    const CREATED_AT = 'create_time';
    const UPDATED_AT = '';
    protected $dateFormat = 'Y-m-d H:i:s';
    protected $table = 'his_accnt_mch_sub';
    protected $primaryKey = 'id_his_accnt_mch_sub';
    
    protected $fillable = [
        'mch_no', 'mch_sub_no', 'transaction_no', 'event', 'event_amt', 'event_time', 'mchsub_remain_amt_before', 'mchsub_remain_amt_after', 'create_time'
    ];
    
    
}
