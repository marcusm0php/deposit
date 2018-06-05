<?php

namespace App\Console\Commands;

use App\Models\Bankcard;
use App\Models\Mchsub;
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
        
        // 商户开设子账户
        $this->addWorkerFunction('deposit.mchsub.create', function($dataOri, $sign, $data, $bizContent){
            $bizContentFormat = array_merge([
                'mch_sub_name' => '',
                'link_name' => '', 
                'link_phone' => '', 
                'link_email' => '', 
            ], $bizContent);
            $ret = new FormatResult($data);
            
            DB::beginTransaction();
            
            $mchsub = \App\Models\Mchsub::where('mch_sub_name', $bizContentFormat['mch_sub_name'])
                                            ->where('mch_no', $data['mch_no'])
                                            ->first();
            if($mchsub){
                $ret->setError('MCHSUB.CREATE.MCHSUB.NAME.REPEAT');
                return $this->_signReturn($ret->getData());
            }

            $mch_sub_no = \App\Models\Mchsub::generateMchSubNo();
            
            $mchsub = new \App\Models\Mchsub;
            $mchsub->mch_no = $data['mch_no']; 
            $mchsub->mch_sub_no = $mch_sub_no; 
            $mchsub->mch_sub_name = $bizContentFormat['mch_sub_name']; 
            $mchsub->link_name = $bizContentFormat['link_name']; 
            $mchsub->link_phone = $bizContentFormat['link_phone'];
            $mchsub->link_email = $bizContentFormat['link_email'];
            $mchsub->save();
            
            DB::commit();
            $ret->setError('SUCCESS');
            $ret->biz_content = [
                'mch_sub_no' => $mch_sub_no
            ];
            return $this->_signReturn($ret->getData());
        });
        echo "Command:Gear:Deposit.mchsub.create is registered.\n";
        
        // 子商户绑定银行卡
        $this->addWorkerFunction('deposit.mchsub.bind.bankcard', function($dataOri, $sign, $data, $bizContent){
            $bizContentFormat = array_merge([
                'mch_sub_no' => '', 
                'bank_card' => [],
            ], $bizContent);

            $bank_cardFormat = [
                'bank_no' => '',
                'bank_name' => '',
                'bank_branch_name' => '',
                'card_type' => '',
                'card_no' => '',
                'card_cvn' => '',
                'card_expire_date' => '',
                'cardholder_name' => '',
                'cardholder_phone' => '',
                'createtime' => '',
            ];
            $bizContentFormat['bank_card'] = array_merge($bank_cardFormat, $bizContentFormat['bank_card']);
            $ret = new FormatResult($data);
            
            DB::beginTransaction();
            
            $mchsub = \App\Models\Mchsub::where('mch_no', $data['mch_no'])
                                        ->where('mch_sub_no', $bizContentFormat['mch_sub_no'])
                                        ->first();
            if(empty($mchsub)){
                $ret->setError('MCHSUB.MCHSUBNO.INVALID');
                return $this->_signReturn($ret->getData());
            }
            
            $bank_card = $bizContentFormat['bank_card'];
            $bank_card['card_type'] = in_array($bank_card['card_type'], \App\Models\Bankcard::CARD_TYPE)? $bank_card['card_type'] : '0';
            $bank_card['card_expire_date'] = date('Y-m-d', strtotime($bank_card['card_expire_date']));
            if(empty($bank_card['card_no']) /* && other bank_card info checks*/){
                $ret->setError('MCHSUB.CREATE.BANKCARD.ERROR');
                return $this->_signReturn($ret->getData());
            }

            
            //TODO call cib_interface 
            $bank_card_existed = \App\Models\Bankcard::where('mch_no', $data['mch_no'])
                                                     ->where('mch_sub_no', $bizContentFormat['mch_sub_no'])
                                                     ->where('bank_name', $bank_card['bank_name'])
                                                     ->where('card_type', $bank_card['card_type'])
                                                     ->where('card_no', $bank_card['card_no'])
                                                     ->where('card_cvn', $bank_card['card_cvn'])
                                                     ->first();
            if($bank_card_existed){
                $ret->setError('MCHSUB.CREATE.BANKCARD.REPEAT');
                return $this->_signReturn($ret->getData());
            }
        
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
            $bankCardModel->save();
        
            DB::commit();
            $ret->setError('SUCCESS');
            $ret->biz_content = [
                'mch_sub_no' => $bizContentFormat['mch_sub_no'], 
                'bank_name' => $bank_card['bank_name'] ,
                'bank_no' => $bank_card['bank_no'], 
                'bank_branch_name' => $bank_card['bank_branch_name'], 
                'card_no' => $bank_card['card_no'], 
                'card_type' => $bank_card['card_type'], 
                'card_cvn' => $bank_card['card_cvn'], 
                'card_expire_date' => $bank_card['card_expire_date'], 
                'cardholder_name' => $bank_card['cardholder_name'], 
                'cardholder_phone' => $bank_card['cardholder_phone'], 
            ];
            return $this->_signReturn($ret->getData());
        });
        echo "Command:Gear:Deposit.mchsub.bind.bankcard is registered.\n";
        
        
        //商户查询
        $this->addWorkerFunction('deposit.mchsub.query',function($dataOri,$sign,$data,$bizContent){

            $bizContentFormat = array_merge([
                'mch_sub_no' => '',
            ], $bizContent);

            $ret = new FormatResult($data);

            $mch_sub = Mchsub::where('mch_no',$data['mch_no'])->where('mch_sub_no',$bizContentFormat['mch_sub_no'])->first();

            if(empty($mch_sub)){
                $ret->setError('MCHSUB.MCHSUBNO.INVALID');
                return $this->_signReturn($ret->getData());
            }

            $bank_cards = Bankcard::where('mch_sub_no',$mch_sub->mch_sub_card)->get()->toArray();

            $mch_sub_arr = $mch_sub->toarray();
            $mch_sub_arr['bank_cards'] = $bank_cards;
            $ret->setError('SUCCESS');
            $ret->biz_content = [
                'mch_sub_no' => $bizContentFormat['mch_sub_no'],
                'mch_sub'=>$mch_sub_arr,

            ];
            return $this->_signReturn($ret->getData());


        });
        echo "Command:Gear:Deposit.mchsub.qurery is registered.\n";

        echo "Command:Gear:Deposit Is Launched Successfully\n";
        while ($this->_worker->work());
    }
}
