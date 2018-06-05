<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bankcard extends ModelBase
{
    const CARD_TYPE = [
        '0', '1'
    ];
    
    protected $table = 'bank_card';
    protected $primaryKey = 'id_bank_card';
    
    protected $fillable = [
        'mch_no', 'mch_sub_no', 'bank_no', 'bank_name', 'bank_branch_name', 'card_type', 'card_no', 'card_cvn', 'card_expire_date', 'cardholder_name', 'cardholder_phone', 'verify_phone_code', 'verify_token', 'status', 'create_time'
    ];
    
    public static function generateVerifyToken()
    {
        return create_uuid();
    }
}
