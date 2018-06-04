<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mchsub extends ModelBase
{
    protected $table = 'mch_sub';
    protected $primaryKey = 'id_mch_sub';
    
    protected $fillable = [
        'mch_sub_name', 'mch_sub_no', 'mch_no', 'link_name', 'link_phone', 'link_email', 'create_time'
    ];
    
    
    public static function generateMchSubNo()
    {
        list($msec, $sec) = explode(' ', microtime());
        $msec = str_replace('0.', '', $msec);
        $msec = substr($msec, 0, 3);
            
        $mchno = 
            '9' . 
            (date('Y', $sec)-2017) . 
            str_pad( date('z', $sec), 3, '0', STR_PAD_LEFT ) . 
            str_pad( date('H', $sec)*60*60 + date('i', $sec)*60 + date('s', $sec), 3, '0', STR_PAD_LEFT )
        ;
        
        $mchno .= $msec;
    
        $mchno .= str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
        
        return $mchno;
    }
}
