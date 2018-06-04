<?php

namespace App\Models;

use App\Models;
use Illuminate\Database\Eloquent\Model;

class HisAccntPrepay2 extends ModelBase
{
    protected $table = 'his_accnt_prepay2';
    protected $primaryKey = 'id_his_accnt_prepay2';
    
    protected $fillable = [
        'mch_no', 'transaction_no', 'event', 'event_amt', 'event_time', 'mch_prepay2_chargeup_amt_before', 'mch_prepay2_chargeup_amt_after', 'create_time'
    ];
    
    
    
}
