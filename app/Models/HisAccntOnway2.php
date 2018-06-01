<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HisAccntOnway2 extends Model
{
    const CREATED_AT = 'create_time';
    const UPDATED_AT = '';
    protected $dateFormat = 'Y-m-d H:i:s';
    protected $table = 'his_accnt_onway2';
    protected $primaryKey = 'id_his_accnt_onway2';
    
    protected $fillable = [
        'mch_no', 'transaction_no', 'event', 'event_amt', 'event_time', 'mch_onway2_amt_before', 'mch_onway2_amt_after', 'create_time'
    ];
    
    
    
}
