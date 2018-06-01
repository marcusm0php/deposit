<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MchAccntChargeUp extends Model
{
    const CREATED_AT = 'create_time';
    const UPDATED_AT = '';
    protected $dateFormat = 'Y-m-d H:i:s';
    protected $table = 'mch_accnt_charge_up';
    protected $primaryKey = 'id_mch_accnt_charge_up';
    
    protected $fillable = [
        'accnt_type_lender', 'accnt_type_borrower', 'charge_up_time', 'id_deposit_transaction', 'create_time'
    ];
    
    
    
}
