<?php

namespace App\Console\Commands;

use App\Libs\Interfaces\SmsInterface;
use App\Models\Bankcard;
use App\Models\Mchsub;
use App\Models\MchAccnt;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;
use App\Libs\FormatResult;

class GearDepositCommand extends GearCommandBase
{
    protected $signature = 'command:gear:deposit';
    protected $description = 'Gearman Working: Deposit around functions.';
    
    public function __construct()
    {
        parent::__construct();
        
        $this->beforeRun();
    }
    
    public function handle()
    {
        parent::handle();
        
        //商户分账
        $this->addWorkerFunction('deposit.mchaccnt.dispatch',function($dataOri, $sign, $data, $bizContent, $bizContentFormat, $depoTrans){
            foreach($bizContentFormat['split_accnt_detail'] as $k => $split_accnt_detail){
                $bizContentFormat['split_accnt_detail'][$k] = array_merge([
                    'mch_accnt_no' => '', 
                    'dispatch_event' => '', 
                    'amount' => '', 
                ], $split_accnt_detail);
            }
            
            $split_accnt_detail_return = [];
            
            foreach($bizContentFormat['split_accnt_detail'] as $k => $split_accnt_detail){
                $mchAccnt = MchAccnt::where('mch_accnt_no', $split_accnt_detail['mch_accnt_no'])->first();
                
                if(empty($mchAccnt)){
                    $this->_formatResult->setError('MCHACCNT.MCHACCNTNO.INVALID');
                    return $this->_signReturn($this->_formatResult->getData());
                }
                
                $hisAccntModel = $mchAccnt->createHisAccntModel();
                $hisAccntModel->transaction_no = $depoTrans->transaction_no;
                $hisAccntModel->event = $split_accnt_detail['dispatch_event'];
                $hisAccntModel->event_amt = $split_accnt_detail['amount'] * 100;
                $hisAccntModel->accnt_amt_after = $hisAccntModel->accnt_amt_before + $hisAccntModel->event_amt;
                $hisAccntModel->save();
                
                $mchAccnt->remain_amt = $hisAccntModel->accnt_amt_after;
                $mchAccnt->save();
                
                $split_accnt_detail_return[] = [
                    'mch_accnt_no' => $mchAccnt->mch_accnt_no, 
                    'dispatch_event' => $hisAccntModel->event, 
                    'amount' => round($hisAccntModel->event_amt / 100, 2), 
                    'amount_after_event' => round($hisAccntModel->accnt_amt_after / 100, 2), 
                ];
            }
            
            $this->_formatResult->setSuccess([
                'split_accnt_detail' => $split_accnt_detail_return
            ]);
            return $this->_signReturn($this->_formatResult->getData());
        }, [
            'split_accnt_detail' => [],
        ]);
        echo "Command:Gear:Deposit.mchaccnt.dispatch is registered.\n";
        
        // 商户开设子账户
        $this->addWorkerFunction('deposit.mchsub.create', function($dataOri, $sign, $data, $bizContent, $bizContentFormat, $depoTrans){

            $mchaccnt = \App\Models\MchAccnt::where('out_mch_accnt_no', $bizContentFormat['out_mch_accnt_no'])
                                            ->where('mch_no', $data['mch_no'])
                                            ->first();
            if($mchaccnt){
                $this->_formatResult->setError('MCHSUB.CREATE.MCHSUB.OUTMCHSUBNO.REPEAT');
                return $this->_signReturn($this->_formatResult->getData());
            }

            $mch_accnt_no = \App\Models\MchAccnt::generateMchAccntNo();
            
            $mchaccnt = new \App\Models\MchAccnt;
            $mchaccnt->mch_no = $data['mch_no'];
            $mchaccnt->mch_accnt_no = $mch_accnt_no;
            $mchaccnt->out_mch_accnt_no = $bizContentFormat['out_mch_accnt_no'];
            $mchaccnt->mch_sub_name = $bizContentFormat['mch_sub_name'];
            $mchaccnt->link_name = $bizContentFormat['link_name'];
            $mchaccnt->link_phone = $bizContentFormat['link_phone'];
            $mchaccnt->link_email = $bizContentFormat['link_email'];
            $mchaccnt->save();
            
            $this->_formatResult->setSuccess([
                'mch_accnt_no' => $mch_accnt_no,
                'out_mch_accnt_no' => $mchaccnt->out_mch_accnt_no
            ]);
            return $this->_signReturn($this->_formatResult->getData());
        }, [
            'mch_accnt_name' => '',
            'out_mch_accnt_no' => '',
            'link_name' => '',
            'link_phone' => '',
            'link_email' => '',
        ]);
        echo "Command:Gear:Deposit.mchsub.create is registered.\n";
        
        // 子商户绑定银行卡-提交资料
        $this->addWorkerFunction('deposit.mchsub.bind.bankcard', function($dataOri, $sign, $data, $bizContent, $bizContentFormat, $depoTrans){

            $mchaccnt = \App\Models\MchAccnt::where('mch_no', $data['mch_no'])
                                        ->where('mch_accnt_no', $bizContentFormat['mch_accnt_no'])
                                        ->first();

            if(empty($mchaccnt)){
                $this->_formatResult->setError('MCHSUB.MCHSUBNO.INVALID');
                return $this->_signReturn($this->_formatResult->getData());
            }
            
            $bank_card = $bizContentFormat['bank_card'];
            $bank_card['card_type'] = in_array($bank_card['card_type'], \App\Models\Bankcard::CARD_TYPE)? $bank_card['card_type'] : '0';
            $bank_card['card_expire_date'] = date('Y-m-d', strtotime($bank_card['card_expire_date']));

            //TODO call cib_interface
            $bank_card_existed = \App\Models\Bankcard::where('mch_no', $data['mch_no'])
                ->where('mch_accnt_no', $bizContentFormat['mch_accnt_no'])
                ->where('bank_name', $bank_card['bank_name'])
                ->where('card_type', $bank_card['card_type'])
                ->where('card_no', $bank_card['card_no'])
                ->where('card_cvn', $bank_card['card_cvn'])
                ->first();
            if($bank_card_existed){
                $this->_formatResult->setError('MCHSUB.CREATE.BANKCARD.REPEAT');
                return $this->_signReturn($this->_formatResult->getData());
            }
            //鉴权
            $auth_data = [
                'trac_no' => uniqid(),
                'card_no' => $bizContentFormat['card_no'],
                'bank_no' => $bizContentFormat['bank_no'],
                'acct_type' => $bizContentFormat['acct_type'],
                'cert_type' => $bizContentFormat['cert_type'],
                'cert_no' => $bizContentFormat['cert_no'],
                'card_phone' => $bizContentFormat['card_phone'],
                'expireDate' => $bizContentFormat['card_expire_date'],
                'cvn' => $bizContentFormat['card_cvn'],
                'user_name' => $bizContentFormat['user_name'],
            ];
            $result    = json_encode($this->_cibpay->acSingleAuth($auth_data),true);
dump($result);
            if(!empty($result['auth_status']) && $result['auth_status'] == '1'){
                $bankCardModel = new \App\Models\Bankcard;
                $bankCardModel->mch_no = $data['mch_no'];
                $bankCardModel->mch_sub_no = $bizContentFormat['mch_sub_no'];
                $bankCardModel->bank_no = $bank_card['bank_no'];
                $bankCardModel->bank_name = $bank_card['bank_name'];
                $bankCardModel->bank_branch_name = $bank_card['bank_branch_name'];
                $bankCardModel->card_type = $bank_card['card_type'];
                $bankCardModel->card_no = $bank_card['card_no'];
                $bankCardModel->card_cvn = $bank_card['card_cvn'];
                $bankCardModel->card_expire_date = $bank_card['card_expire_date'];
                $bankCardModel->cardholder_name = $bank_card['cardholder_name'];
                $bankCardModel->cardholder_phone = $bank_card['cardholder_phone'];
            }



            //发送验证码
            //$sms_code = $bankCardModel->sendCode();

            /*if(!$sms_code){
                $this->_formatResult->setError('SMS.SEND.ERR');
                return $this->_signReturn($this->_formatResult->getData());
            }*/
            $bankCardModel->verify_phone_code = $sms_code;
            $bankCardModel->save();

            $mchAccntSub = new \App\Models\MchAccnt;
            $mchAccntSub->mch_no = $data['mch_no'];
            $mchAccntSub->mch_sub_no = $bizContentFormat['mch_sub_no'];
            $mchAccntSub->accnt_type = \App\Models\MchAccnt::ACCNT_TYPE_MCHSUB;
            $mchAccntSub->id_bank_card = $bankCardModel->id_bank_card;
            $mchAccntSub->mch_accnt_no = \App\Models\MchAccnt::generateMchAccntNo();
            $mchAccntSub->save();
            
            $this->_formatResult->setSuccess([
                'mch_sub_no' => $bizContentFormat['mch_sub_no'],
                'mch_accnt_no' => $mchAccntSub->mch_accnt_no,
                'bank_card' => [
                    'card_no' => $bank_card['card_no'],
                ]
            ]);
            return $this->_signReturn($this->_formatResult->getData());
        }, [
            'mch_accnt_no' => '',
            'bank_no' => '',
            'bank_name' => '',
            'card_type' => '',
            'card_no' => '',
            'card_cvn' => '',
            'card_expire_date' => '',
            'user_name' => '',
            'card_phone' => '',
            'cert_no' => '',
        ]);
        echo "Command:Gear:Deposit.mchsub.bind.bankcard is registered.\n";

        // 子商户绑定银行卡-回填手机验证码
        /*$this->addWorkerFunction('deposit.mchsub.bind.bankcardverify', function($dataOri, $sign, $data, $bizContent, $bizContentFormat, $depoTrans){
            1. 根据mch_accnt_no查找账户$MchAccnt
            2. 根据账户关联银行卡id_bank_card找到银行卡信息$bankCard = $MchAccnt->getBankcard();
            3. 使用银行卡信息$bankCard['cardholder_phone']+verify_code+sms_code进行验证
            $mch_acnt = MchAccnt::where('mch_accnt_no',$bizContentFormat['mch_accnt_no'])
                                ->where('mch_sub_no',$bizContentFormat['mch_sub_no'])
                                ->with('bankCard')->first();

            if(empty($mch_acnt) || empty($mch_acnt->bankCard)){
                $this->_formatResult->setError('MCHACCNT.MCHACCNTNO.INVALID');
                return $this->_signReturn($this->_formatResult->getData());
            }

            if(!$mch_acnt->bankCard->validateSmsCode($bizContentFormat['sms_code'])){
                $this->_formatResult->setError('SMS.VERIFY.ERR');
                return $this->_signReturn($this->_formatResult->getData());
            }

            $mch_acnt->bankCard->status = 'success';
            $mch_acnt->bankCard->save();

            $this->_formatResult->setSuccess([
                'mch_sub_no' => $mch_acnt->mch_sub_no,
                'mch_accnt_no' => $mch_acnt->mch_sub_no,
            ]);
            return $this->_signReturn($this->_formatResult->getData());
        }, [
            'mch_sub_no' => '',
            'mch_accnt_no' => '',
            'sms_code' => '',
        ]);
        echo "Command:Gear:Deposit.mchsub.bind.bankcardverify is registered.\n";*/


        //子商户查询
        $this->addWorkerFunction('deposit.mchsub.query',function($dataOri, $sign, $data, $bizContent, $bizContentFormat, $depoTrans){
            $mch_sub = Mchsub::where('mch_no',$data['mch_no'])->where('mch_sub_no', $bizContentFormat['mch_sub_no'])->first();

            if(empty($mch_sub)){
                $this->_formatResult->setError('MCHSUB.MCHSUBNO.INVALID');
                return $this->_signReturn($this->_formatResult->getData());
            }

            $mchaccts = MchAccnt::where('mch_sub_no', $mch_sub->mch_sub_no)
                                ->where('')
                                ->get()
                                ->map(function($item){
                                    return [$item,$item->bankCard()->first()];
                                })->toArray();

            $mch_sub_arr['mch_accnt'] = $mchaccts;

            $this->_formatResult->setSuccess([
                'mch_sub_no' => $bizContentFormat['mch_sub_no'],
                'mch_sub'=>$mch_sub_arr,
            ]);
            return $this->_signReturn($this->_formatResult->getData());
        }, [
            'mch_sub_no' => '',
        ]);
        echo "Command:Gear:Deposit.mchsub.query is registered.\n";

        
        echo "Command:Gear:Deposit Is Launched Successfully\n";
        while ($this->_worker->work());
    }
}
