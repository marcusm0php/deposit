<?php

namespace App\Models;

use App\Libs\Interfaces\SmsInterface;
use Illuminate\Database\Eloquent\Model;

class Bankcard extends ModelBase
{
    const CARD_TYPE = [
        '0', '1'
    ];

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

    public function sendCode()
    {
        // 生成4位随机数，左侧补0
        $code = str_pad(random_int(1, 999999), 6, 0, STR_PAD_LEFT);

        $sms_data =  [
            'template' => 'SMS_126971169',
            'data' => [
                'code' => $code
            ],
        ];

        $res = SmsInterface::sendCode($this->cardholder_phone,$sms_data);

        if($res['code']==200){
            $key = 'verficationCode_'.md5(md5($this->bank_no.'code'));
            $expiredAt = now()->addMinute(self::EXPIRED_TIME);

            \Cache::put($key,['id_bank_card'=>$this->id_bank_card,'code'=>$code],$expiredAt);

            return [
                'code'=>200,
                'verfication_key'=>$key,
                'expired_at' => $expiredAt->toDateTimeString()
            ];
        }

        return $res;
    }

    /**
     * 验证手机验证码
     * @param $code client端传入验证码
     * @param $validate_key 手机验证码key
     * @return array
     */
    public static function validateCode($verfication_key, $code)
    {

        $save_data = \Cache::get($verfication_key);

        if(empty($save_data)) return ['code' => 422,'msg' => '验证码已过期'];

        if(!hash_equals("{$save_data['code']}","$code")) return ['code' => 401,'msg' => '验证码错误'];

        $bank_card_model = self::find($save_data['id_bank_card']);

        if(!$bank_card_model) return ['code' => 404, 'msg' => '信息验证错误'];

        $bank_card_model->status = 'success';
        $bank_card_model->save();

        // 清除验证码缓存
        \Cache::forget($verfication_key);

        return ['code' => 200, 'msg' => '绑卡成功', 'response_data' => ['mch_sub_no' => $bank_card_model->mch_sub_no, 'bank_no' => $bank_card_model->bank_no], ];
    }
}
