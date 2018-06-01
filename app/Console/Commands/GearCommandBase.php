<?php 

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models;
use App\Libs;
use App\Libs\FormatResult;

class GearCommandBase extends Command 
{
    protected $signature = 'command:name {param}';

    protected $description = 'Description';

    protected $_worker;
    public function beforeRun()
    {
        $this->_worker= new \GearmanWorker();
        $gearmanIp = '127.0.0.1';
        $gearmanPort = '4730';
    

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
            
            $interfaceConfig = DB::table('interface_config')->where('mch_no', $mch_no)->first();
            if(empty($interfaceConfig)){
                $ret = new FormatResult($data);
                $ret->setError('SIGN.VERIFY.FAIL');
                
                $this->_signReturn($ret->getData());
            }
        });
        
//         $this->addWorkerFunction('worker.router', function($bizContent, $data){
            
//         });

    }

    protected function _signReturn($data, $token = null, $format = 'md5')
    {
        $sign = '';
        if($format == 'md5'){
            $sign = SignMD5Helper::genSign($data, $token);
        }
        
        $response = json_encode(array(
            'data' => $data,
            'sign' => $sign
        ), JSON_UNESCAPED_UNICODE);

        app('galog')->log($response, 'worker_deposit', 'WorkerReturn');
    
        return $response;
    }
}