<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bankcard extends Model
{
    const CREATED_AT = 'create_time';
    const UPDATED_AT = '';
    protected $dateFormat = 'Y-m-d H:i:s';
    protected $table = 'bank_card';
    protected $primaryKey = 'id_bank_card';
    
    protected $fillable = [
        'mch_no', 'mch_sub_no', 'bank_no', 'bank_name', 'bank_branch_name', 'card_type', 'card_no', 'card_cvn', 'card_expire_date', 'cardholder_name', 'cardholder_phone', 'create_time',
    ];
     
}