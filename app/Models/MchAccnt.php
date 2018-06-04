<?php

namespace App\Models;

use App\Models;
use Illuminate\Database\Eloquent\Model;

class MchAccnt extends ModelBase
{
    protected $table = 'mch_accnt';
    protected $primaryKey = 'id_mch_accnt';
    
    protected $fillable = [
        'mch_no', 'mch_sub_no', 'settle_duration', 'id_bank_card', 'remain_amt', 'accnt_type', 'create_time'
    ];
}
