<?php

namespace App\Models;

use App\Models;
use Illuminate\Database\Eloquent\Model;

class HisAccntOnway2 extends ModelBase
{
    protected $table = 'his_accnt_onway2';
    protected $primaryKey = 'id_his_accnt_onway2';
    
    protected $fillable = [
        'mch_no', 'transaction_no', 'event', 'event_amt', 'event_time', 'mch_onway2_amt_before', 'mch_onway2_amt_after', 'create_time'
    ];
    
    
    
}
