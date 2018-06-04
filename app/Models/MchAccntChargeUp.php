<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MchAccntChargeUp extends ModelBase
{
    protected $table = 'mch_accnt_charge_up';
    protected $primaryKey = 'id_mch_accnt_charge_up';
    
    protected $fillable = [
        'accnt_type_lender', 'accnt_type_borrower', 'charge_up_time', 'id_deposit_transaction', 'create_time'
    ];
    
    
    
}
