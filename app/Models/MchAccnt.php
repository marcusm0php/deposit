<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MchAccnt extends ModelBase
{
    const ACCNT_TYPE_DEPOSITORY = 'depository'; //存管
    const ACCNT_TYPE_PREPAY = 'prepay'; //预付
    const ACCNT_TYPE_PREPAY2 = 'prepay2'; //预付
    const ACCNT_TYPE_ONWAY = 'onway'; //在途
    const ACCNT_TYPE_ONWAY2 = 'onway2'; //在途2
    const ACCNT_TYPE_PROFIT = 'profit'; //分润
    const ACCNT_TYPE_ASSURANCE = 'assurance'; //担保
    const ACCNT_TYPE_MCHSUB = 'mchsub'; //子商户

    const BATCH_ACCNT_MAX = 100; //批量单次最多子商户条数
    const BATCH_CARD_MAX = 20; //批量单次每个子商户下银行卡最大条数

    const BACTH_SUCCESS_STATUS = 1; //批量开设子商户成功状态
    const BACTH_FAIL_STATUS = 2; //批量开设子商户失败状态

    const DISPATCH_SUCCESS_STATUS = 1; //分账成功状态
    const DISPATCH_FAIL_STATUS = 2; //分账失败状态

    const DISPATCH_MAX = 1000; //单次分账最多条数

    //pay(支付)；refund(退款)；transfer(转账);subsidy（补贴）；fine(罚款)；consume(余额消费);award(奖励);
    const EVENTS = ['pay', 'refund', 'transfer', 'subsidy', 'fine', 'consume', 'award'];
    //1：正交易；2：反交易
    const DISPATCH_TYPE = ['1', '2'];

    protected $table = 'mch_accnt';
    protected $primaryKey = 'id_mch_accnt';

//    protected $hidden = ['id_mch_accnt'];

    protected $fillable = [
        'mch_no', 'mch_accnt_no', 'out_mch_accnt_no', 'settle_duration', 'remain_amt', 'accnt_type', 'create_time', 'link_name', 'link_phone', 'link_email',
    ];
    
    public static function generateMchAccntNo()
    {
        list($msec, $sec) = explode(' ', microtime());
        $msec = str_replace('0.', '', $msec);
        $msec = substr($msec, 0, 3);
    
        $mchno =
        '1' .
        (date('Y', $sec)-2017) .
        str_pad( date('z', $sec), 3, '0', STR_PAD_LEFT ) .
        str_pad( date('H', $sec)*60*60 + date('i', $sec)*60 + date('s', $sec), 3, '0', STR_PAD_LEFT )
        ;
    
        $mchno .= $msec;
    
        $mchno .= str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
    
        return $mchno;
    }
    
    public function createHisAccntModel($transaction_no = '')
    {
        $hisAccntModel = null;
        if($this->accnt_type == self::ACCNT_TYPE_DEPOSITORY){
            $hisAccntModel = new \App\Models\HisAccntMch;
        }else if($this->accnt_type == self::ACCNT_TYPE_PREPAY){
            $hisAccntModel = new \App\Models\HisAccntPrepay;
        }else if($this->accnt_type == self::ACCNT_TYPE_PREPAY2){
            $hisAccntModel = new \App\Models\HisAccntPrepay2;
        }else if($this->accnt_type == self::ACCNT_TYPE_ONWAY){
            $hisAccntModel = new \App\Models\HisAccntOnway;
        }else if($this->accnt_type == self::ACCNT_TYPE_ONWAY2){
            $hisAccntModel = new \App\Models\HisAccntOnway2;
        }else if($this->accnt_type == self::ACCNT_TYPE_PROFIT){
            $hisAccntModel = new \App\Models\HisAccntProfile;
        }else if($this->accnt_type == self::ACCNT_TYPE_ASSURANCE){
            $hisAccntModel = new \App\Models\HisAccntAssurance;
        }else if($this->accnt_type == self::ACCNT_TYPE_MCHSUB){
            $hisAccntModel = new \App\Models\HisAccntMchsub;
            $hisAccntModel->mch_sub_no = $this->mch_sub_no;
        }
        
        if($hisAccntModel){
            $hisAccntModel->transaction_no = $transaction_no;
            $hisAccntModel->mch_no = $this->mch_no;
            $hisAccntModel->accnt_amt_before = $this->remain_amt;
            $hisAccntModel->event_time = date('Y-m-d H:i:s');
        }

        return $hisAccntModel;
    }

    //relation_bank_card
    public function bankCard()
    {
        return $this->hasMany(Bankcard::class,'mch_accnt_no','mch_accnt_no')->where('status','success');
    }

}
