<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HisAccntOnway extends Model
{
    const CREATED_AT = 'create_time';
    const UPDATED_AT = '';
    protected $dateFormat = 'Y-m-d H:i:s';
    protected $table = 'his_accnt_onway';
    protected $primaryKey = 'id_his_accnt_onway';
    
    protected $fillable = [
        'mch_no', 'transaction_no', 'event', 'event_amt', 'event_time', 'mch_onway_amt_before', 'mch_onway_amt_after', 'create_time'
    ];
    
    
    
}
