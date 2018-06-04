<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HisAccntMch extends ModelBase
{
    protected $table = 'his_mch_acnt';
    protected $primaryKey = 'id_his_mch_acnt';
    
    protected $fillable = [
        'mch_no', 'transaction_no', 'event', 'event_amt', 'event_time', 'mch_remain_amt_before', 'mch_remain_amt_after', 'create_time'
    ];
    
    
    
}
