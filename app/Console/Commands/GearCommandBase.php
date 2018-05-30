<?php 

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GearCommandBase extends Command 
{
    protected $signature = 'command:name {param}';

    protected $description = 'Description';

    protected $_worker;
    public function beforeRun()
    {
        $this->_worker= new GearmanWorker();
        $gearmanIp = '127.0.0.1';
        $gearmanPort = '4730';
    
        exec("ip addr |grep global|awk '{print \$2}'|awk -F\/ '{print \$1}'", $out, $ret);
        $inetIp = empty($out[0])? '' : $out[0];
        if(empty($inetIp)){
            echo "启动失败\n";
            echo "获取inet ip失败\n";
            exit();
        }
    
        echo "本机INETIP: {$inetIp}\n";die();
        $gearmanConfig = app()->db->createCommand("
			SELECT * FROM GEARMAN_CONFIG
		    WHERE INETIP = '".$inetIp."'
		")->queryRow();

        if(!empty($gearmanConfig)){
            $gearmanIp = $gearmanConfig['WORKERS_IP'];
            $gearmanPort = $gearmanConfig['WORKERS_PORT'];
        }else{
            echo "启动失败\n";
            echo "获取WORKERS_IP失败\n";
            exit();
        }
    
        echo "Gearman Workers工作组IP: {$gearmanIp}\n";
        echo "Gearman Workers工作组端口{$gearmanPort}\n";
        $this->_worker->addServer($gearmanIp, $gearmanPort);
    
        return true;
    }
}