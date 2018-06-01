<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MchAccnt extends Model
{
    const CREATED_AT = 'create_time';
    const UPDATED_AT = '';
    protected $dateFormat = 'Y-m-d H:i:s';
    protected $table = 'mch_accnt';
    protected $primaryKey = 'id_mch_accnt';
    
    protected $fillable = [
        'mch_no', 'mch_sub_no', 'settle_duration', 'id_bank_card', 'remain_amt', 'accnt_type', 'create_time'
    ];
}
