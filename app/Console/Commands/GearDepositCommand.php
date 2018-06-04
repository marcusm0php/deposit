<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;
use phpDocumentor\Reflection\Types\Parent_;

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
                'mchsub_name' => '',
                'link_name' => '', 
                'link_phone' => '', 
                'link_email' => '', 
                'bankcard' => [],
            ], $bizContent);
            $bankcardFormat = [
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
            foreach($bizContentFormat['bankcard'] as $k => $bankcard){
                $bizContentFormat['bankcard'][$k] = array_merge($bankcardFormat, $bankcard);
            }
            $ret = new FormatResult($data);
            
            if(empty($bizContentFormat['bankcard'])){
                $ret->setError('MCHSUB.CREATE.BANKCARD.EMPTY');
                return $this->_signReturn($ret->getData());
            }
            
            $mchsub = \App\Models\Mchsub::where('mchsub_name', $data['mchsub_name'])
                                            ->where('mch_sub_no', $mch_sub_no)
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
            $mchsub->mchsub_name = $bizContentFormat['mchsub_name']; 
            $mchsub->link_name = $bizContentFormat['link_name']; 
            $mchsub->link_phone = $bizContentFormat['link_phone'];
            $mchsub->link_email = $bizContentFormat['link_email'];
            $flight->save();
            
            foreach($bizContentFormat['bankcard'] as $k => $bankcard){
                if(empty($bankcard['bank_no']) /* && other bankcard info checks*/){

                    DB::rollBack();
                    $ret->setError('MCHSUB.CREATE.BANKCARD.ERROR');
                    return $this->_signReturn($ret->getData());
                }
                
                $bankcard = new \App\Models\Bankcard;
                $bankcard->mch_no = $data['mch_no']; 
                $bankcard->mch_sub_no = $mch_sub_no; 
                $bankcard->bank_no = $bankcard['bank_no']; 
                $bankcard->bank_name = $bankcard['bank_name']; 
                $bankcard->bank_branch_name = $bankcard['bank_branch_name']; 
                $bankcard->card_type = $bankcard['card_type']; 
                $bankcard->card_no = $bankcard['card_no']; 
                $bankcard->card_cvn = $bankcard['card_cvn']; 
                $bankcard->card_expire_date = $bankcard['card_expire_date']; 
                $bankcard->cardholder_name = $bankcard['cardholder_name']; 
                $bankcard->cardholder_phone = $bankcard['cardholder_phone']; 
                $bankcard->save();
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
