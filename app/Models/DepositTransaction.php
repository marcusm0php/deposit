<?php

namespace App\Models;

use App\Models;
use Illuminate\Database\Eloquent\Model;

class DepositTransaction extends ModelBase
{
    protected $table = 'deposit_transaction';
    protected $primaryKey = 'id_deposit_transaction';
    
    protected $fillable = [
        'transaction_no', 'transaction_type', 'transaction_time', 'id_accnt_charge_up', 'create_time'
    ];
    
    public static function generateTransNo()
    {
        //TODO generate strategy implement
        return create_uuid();
    }
    
    
}
