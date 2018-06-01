<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HisAccntMch extends Model
{
    const CREATED_AT = 'create_time';
    const UPDATED_AT = '';
    protected $dateFormat = 'Y-m-d H:i:s';
    protected $table = 'his_mch_acnt';
    protected $primaryKey = 'id_his_mch_acnt';
    
    protected $fillable = [
        'mch_no', 'transaction_no', 'event', 'event_amt', 'event_time', 'mch_remain_amt_before', 'mch_remain_amt_after', 'create_time'
    ];
    
    
    
}
