<?php

namespace App\Models;

use App\Models;
use Illuminate\Database\Eloquent\Model;

class Mch extends ModelBase
{
    protected $table = 'mch';
    protected $primaryKey = 'id_mch';
    
    protected $fillable = [
        'mch_no', 'mch_name', 'link_name', 'link_phone', 'link_email', 'busi_license_no', 'busi_license_img', 'create_time'
    ];


    public static function generateMchNo()
    {
        list($msec, $sec) = explode(' ', microtime());
        $msec = str_replace('0.', '', $msec);
        $msec = substr($msec, 0, 3);
    
        $mchno =
        '8' .
        (date('Y', $sec)-2017) .
        str_pad( date('z', $sec), 3, '0', STR_PAD_LEFT ) .
        str_pad( date('H', $sec)*60*60 + date('i', $sec)*60 + date('s', $sec), 3, '0', STR_PAD_LEFT )
        ;
    
        $mchno .= $msec;
    
        $mchno .= str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
    
        return $mchno;
    }
}
