<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HisAccntAssurance extends ModelBase
{
    protected $table = 'his_accnt_assurance';
    protected $primaryKey = 'id_his_accnt_assurance';
    
    protected $fillable = [
        'mch_no', 'transaction_no', 'event', 'event_amt', 'event_time', 'mch_assurance_amt_before', 'mch_assurance_amt_after', 'create_time'
    ];
    
}
