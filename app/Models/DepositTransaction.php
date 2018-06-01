<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepositTransaction extends Model
{
    const CREATED_AT = 'create_time';
    const UPDATED_AT = '';
    protected $dateFormat = 'Y-m-d H:i:s';
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
