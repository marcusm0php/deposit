<?php

namespace App\Console\Commands;

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
                'bank_card' => [],
            ], $bizContent);
            $bank_cardFormat = [
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
            foreach($bizContentFormat['bank_card'] as $k => $bank_card){
                if(is_array($bank_card)){
                    $bizContentFormat['bank_card'][$k] = array_merge($bank_cardFormat, $bank_card);
                }
            }
            $ret = new FormatResult($data);
            
            if(empty($bizContentFormat['bank_card'])){
                $ret->setError('MCHSUB.CREATE.BANKCARD.EMPTY');
                return $this->_signReturn($ret->getData());
            }
            
            $mchsub = \App\Models\Mchsub::where('mch_sub_name', $bizContentFormat['mch_sub_name'])
                                            ->where('mch_no', $data['mch_no'])
                                            ->first();
            if($mchsub){
                $ret->setError('MCHSUB.CREATE.MCHSUB.NAME.REPEAT');
                return $this->_signReturn($ret->getData());
            }

            DB::beginTransaction();
            $mch_sub_no = \App\Models\Mchsub::generateMchSubNo();
            
            $mchsub = new \App\Models\Mchsub;
            $mchsub->mch_no = $data['mch_no']; 
            $mchsub->mch_sub_no = $mch_sub_no; 
            $mchsub->mch_sub_name = $bizContentFormat['mch_sub_name']; 
            $mchsub->link_name = $bizContentFormat['link_name']; 
            $mchsub->link_phone = $bizContentFormat['link_phone'];
            $mchsub->link_email = $bizContentFormat['link_email'];
            $mchsub->save();
            
            foreach($bizContentFormat['bank_card'] as $k => $bank_card){
                if(empty($bank_card['bank_no']) /* && other bank_card info checks*/){

                    DB::rollBack();
                    $ret->setError('MCHSUB.CREATE.BANKCARD.ERROR');
                    return $this->_signReturn($ret->getData());
                }
                
                $bank_card = new \App\Models\Bankcard;
                $bank_card->mch_no = $data['mch_no']; 
                $bank_card->mch_sub_no = $mch_sub_no; 
                $bank_card->bank_no = $bank_card['bank_no']; 
                $bank_card->bank_name = $bank_card['bank_name']; 
                $bank_card->bank_branch_name = $bank_card['bank_branch_name']; 
                $bank_card->card_type = $bank_card['card_type']; 
                $bank_card->card_no = $bank_card['card_no']; 
                $bank_card->card_cvn = $bank_card['card_cvn']; 
                $bank_card->card_expire_date = $bank_card['card_expire_date']; 
                $bank_card->cardholder_name = $bank_card['cardholder_name']; 
                $bank_card->cardholder_phone = $bank_card['cardholder_phone']; 
                $bank_card->save();
            }
            
            DB::commit();
            $ret->setError('SUCCESS');
            $ret->biz_content = [
                'mch_sub_no' => $mch_sub_no
            ];
            return $this->_signReturn($ret->getData());
        });
        echo "Command:Gear:Deposit.mchsub.create is registered.\n";
        
        

        echo "Command:Gear:Deposit Is Launched Successfully\n";
        while ($this->_worker->work());
    }
}
