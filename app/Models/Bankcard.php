<?php

namespace App\Models;

use App\Libs\Interfaces\SmsInterface;
use Illuminate\Database\Eloquent\Model;

class Bankcard extends ModelBase
{
    const CARD_TYPE = [
        '0', '1'
    ];
    const SUCCESS='SUCCESS';
    const UNBIND='unbind';

    const EXPIRED_TIME = 10;//短信验证码过期时间（分钟）

    protected $table = 'bank_card';
    protected $primaryKey = 'id_bank_card';
    
    protected $fillable = [
        'mch_no', 'mch_sub_no', 'bank_no', 'bank_name', 'bank_branch_name', 'card_type', 'card_no', 'card_cvn', 'card_expire_date', 'cardholder_name', 'cardholder_phone', 'verify_phone_code', 'verify_token', 'status', 'create_time'
    ];
    
    public static function generateVerifyToken()
    {
        return create_uuid();
    }

    public function sendCode($sms_code='')
    {
        // 生成4位随机数，左侧补0
        if(empty($sms_code)){
            $sms_code = str_pad(random_int(1, 999999), 6, 0, STR_PAD_LEFT);
        }

        $res = SmsInterface::sendCode($this->cardholder_phone, [
            'template' => 'SMS_126971169',
            'data' => [
                'code' => $sms_code
            ],
        ]);

        if($res['code'] == 200){
            /*$verifykey = 'verifyCode_' . md5($this->bank_no . time());
            $expiredAt = now()->addMinute(self::EXPIRED_TIME);

            \Cache::put(
                $verifykey,
                ['id_bank_card'=>$this->id_bank_card, 'sms_code' => $sms_code], 
                $expiredAt
            );*/

            return $sms_code;
        }

        return false;
    }

    /**
     * 验证手机验证码
     * @param $code client端传入验证码
     * @param $validate_key 手机验证码key
     * @return array
     */
    public function validateSmsCode($sms_code)
    {
        $sms_code = $sms_code.'';
        return hash_equals($this->verify_phone_code,$sms_code);
    }
}
