<?php 

namespace App\Console\Commands;

use App\Libs\Interfaces\CibInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Libs\FormatResult;
use App\Libs\FormatResultErrors;

class GearCommandBase extends Command 
{
    protected $signature = 'command:name {param}';

    protected $description = 'Description';

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

            if(env('APP_ENV') == 'local'){//本地工作配置
                $this->_worker->addServer('127.0.0.1', '4730');
            }else{
                $this->_worker->addServer($gearmanConfig->gearmand_srv_ip, $gearmanConfig->gearmand_srv_port);
            }


            return true;
        }else{
            echo "启动失败\n";
            echo "获取WORKERS_IP失败\n";
            exit();
            return false;
        }
    }
    
    protected $_formatResult;
    protected $_cibpay;
    public function addWorkerFunction($funcName, $realDo, $bizContentFormat = [])
    {
        $this->_worker->addFunction($funcName, function($job, $outParamEntities){
            extract($outParamEntities);

            $workLoadArgs = json_decode($job->workload(), true);
            
            $mch_md5_token = isset($workLoadArgs['mch_md5_token'])? $workLoadArgs['mch_md5_token'] : null;
            
            $ga_traceno = $workLoadArgs['ga_traceno'];
            app()->singleton('ga_traceno', function($app) use ($ga_traceno){
                return $ga_traceno;
            });
            echo $funcName . '('.app('ga_traceno').') is called at ' . date('Y-m-d H:i:s') . "\n";
    
            $dataOri = $workLoadArgs['data'];
            $data = json_decode($dataOri, true);
            $data = empty($data)? [] : $data;
            $data = array_merge([
                'mch_no' => '',
                'out_trant_no' => '',
                'timestamp' => '', 
                'biz_type' => '', 
                'code' => '', 
                'message' => '', 
                'biz_content' => [], 
                'sign_type' => '', 
            ], $data);
            $sign = $workLoadArgs['sign'];
            $bizContent = $data['biz_content'];
    
            app('galog')->log(json_encode([
                'data' => $data,
                'sign' => $sign, 
                'mch_md5_token' => $mch_md5_token, 
                'ga_traceno' => $ga_traceno, 
            ]), 'worker_deposit', 'WorkerLoaded');

            $this->_cibpay = new CibInterface();

            DB::beginTransaction();
            if($funcName != 'deposit.sign.verify'){
                $depoTrans = \App\Models\DepositTransaction::Factory(app('ga_traceno'), $funcName);
                $depoTrans->out_trant_no = $data['out_trant_no'];
                $depoTrans->mch_no = $data['mch_no'];
                $depoTrans->save();
                $this->_formatResult = new FormatResult($data, $depoTrans->transaction_no);
            }else{
                $this->_formatResult = new FormatResult($data);
                $depoTrans = null;
            }
            
            $bizContentFormat = array_merge($bizContentFormat, $bizContent);
            $realDoRet = $realDo($dataOri, $sign, $data, $bizContent, $bizContentFormat, $depoTrans);
            
            if($this->_formatResult->code == FormatResultErrors::CODE_MAP['SUCCESS']['code']){
                DB::commit();
            }else{
                DB::rollBack();
            }
            
            return $realDoRet;
        }, array(
            'funcName' => $funcName,
            'realDo' => $realDo,
            'bizContentFormat'=>$bizContentFormat
        ));
    }

    public function handle()
    { 
        $this->addWorkerFunction('deposit.sign.verify', function($dataOri, $sign, $data, $bizContent, $bizContentFormat, $depoTrans){
            $mch_no = $data['mch_no'];
            
            $interfaceConfig = DB::table('interface_config')->where('mch_no', $mch_no)->first();
			
            if(!empty($interfaceConfig)){
                $signCal = \App\Libs\SignMD5Helper::genSign($dataOri, $interfaceConfig->md5_token);
				
                if($signCal == $sign){
                    $this->_formatResult->setSuccess([
                        'mch_md5_token' => $interfaceConfig->md5_token
                    ]);
					return $this->_signReturn($this->_formatResult->getData());
                }
            }

            $this->_formatResult->setError('SIGN.VERIFY.FAIL');
            return $this->_signReturn($this->_formatResult->getData());
        });
        echo "Command:Gear:Deposit.sign.verify is registered.\n";

    }

    protected function _signReturn($data, $token = null, $format = 'md5')
    {
        $dataJson = json_encode($data, JSON_UNESCAPED_UNICODE);
        $sign = '';
        if($format == 'md5'){
            $sign = \App\Libs\SignMD5Helper::genSign($dataJson, $token);
        }
        
        $response = json_encode(array(
            'data' => $dataJson,
            'sign' => $sign
        ), JSON_UNESCAPED_UNICODE);

        app('galog')->log($response, 'worker_deposit', 'WorkerReturn');
    
        return $response;
    }
}