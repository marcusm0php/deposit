<?php 

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models;
use App\Models\InterfaceConfig;

class GearCommandBase extends Command 
{
    protected $signature = 'command:name {param}';

    protected $description = 'Description';

    protected $_worker;
    public function beforeRun()
    {
        exec("ip addr |grep global|awk '{print \$2}'|awk -F\/ '{print \$1}'", $out, $ret);
        $inetIp = empty($out[0])? '' : $out[0];
        if(empty($inetIp)){
            echo "启动失败\n";
            echo "获取inet ip失败\n";
            exit();
        }
    
        echo "本机INETIP: {$inetIp}\n";
        $gearmanConfig = DB::table('sys_gearman_config')->where('inetip', $inetIp)->first();

        if(!empty($gearmanConfig)){
            echo "Gearman Workers工作组IP: {$gearmanConfig->gearmand_srv_ip}\n";
            echo "Gearman Workers工作组端口{$gearmanConfig->gearmand_srv_port}\n";
            
            $this->_worker= new \GearmanWorker();
            $this->_worker->addServer($gearmanConfig->gearmand_srv_ip, $gearmanConfig->gearmand_srv_port);

            return true;
        }else{
            echo "启动失败\n";
            echo "获取WORKERS_IP失败\n";
            exit();
            return false;
        }
    }
    
    public function handle()
    {
        $this->addWorkerFunction('sign.verify', function($dataOri, $sign, $bizContent, $data){
            $mch_no = $data['mch_no'];
            
            
        });
        
//         $this->addWorkerFunction('worker.router', function($bizContent, $data){
            
//         });
    }
}