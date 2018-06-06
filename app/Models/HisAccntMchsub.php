<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HisAccntMchsub extends ModelBase
{
    protected $table = 'his_accnt_mch_sub';
    protected $primaryKey = 'id_his_accnt_mch_sub';
    
    protected $fillable = [
        'mch_no', 'mch_sub_no', 'transaction_no', 'event', 'event_amt', 'event_time', 'accnt_amt_before', 'accnt_amt_after', 'create_time'
    ];
    
    
}
