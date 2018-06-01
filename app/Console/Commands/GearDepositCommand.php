<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use phpDocumentor\Reflection\Types\Parent_;

class GearDepositCommand extends GearCommandBase
{
    protected $signature = 'command:gear:deposit';
    protected $description = 'Gearman Working: Deposit around functions.';
    
    protected $_worker;

    public function __construct()
    {
        parent::__construct();
        
        $this->beforeRun();
    }
    
    public function addWorkerFunction($funcName, $realDo)
    {
        $this->_worker->addFunction($funcName, function($job, $outParamEntities){
            extract($outParamEntities);
            $workLoadArgs = json_decode($job->workload(), true);

            $ga_traceno = $workLoadArgs['ga_traceno'];
            app()->singleton('ga_traceno', function($app) use ($ga_traceno){
                return $ga_traceno;
            });
            echo $funcName . '('.app('ga_traceno').') is called at ' . date('Y-m-d H:i:s') . "\n";

            app('galog')->log(json_encode([
                'data' => $data,
                'sign' => $sign
            ]), 'worker_deposit', 'WorkerLoaded');
            
            $dataOri = $workLoadArgs['data'];
            $data = json_decode($dataOri, true);
            $sign = $workLoadArgs['sign'];
            $bizContent = $data['bizContent'];
            	
            $realDoRet = $realDo($dataOri, $sign, $bizContent, $data);
            	
            return $realDoRet;
        }, array(
            'funcName' => $funcName,
            'realDo' => $realDo
        ));
    }

    public function handle()
    {
        parent::handle();
        
        // 商户开设子账户
        $this->addWorkerFunction('mchsub.create', function($data, $bizContent){
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
        
        
        $this->addWorkerFunction('', function($bizContent, $data){
            // TODO
            /**
             *
             */
        });
        
        $this->addWorkerFunction('', function($bizContent, $data){
            // TODO
            /**
             *
             */
        });

        echo "Command:Gear:Deposit is started\n";
        while ($this->_worker->work());
    }
}
