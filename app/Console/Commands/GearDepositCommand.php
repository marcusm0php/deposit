<?php

namespace App\Console\Commands;

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
                'mchsub_no' => '',
                'mchsub_name' => '',
                'bankcard' => [],
                'out_refund_no' => ''
            ], $bizContent);
            $bankcardFormat = [
                'mchno' => '', 
                'mchsub_no' => '', 
                'bankname' => '', 
                'bankname_branch' => '', 
                'cardno' => '', 
                'createtime' => '', 
            ];
        });
        echo "Command:Gear:Deposit.mchsub.create is registered.\n";
        
        

        echo "Command:Gear:Deposit Is Launched Successfully\n";
        while ($this->_worker->work());
    }
}
