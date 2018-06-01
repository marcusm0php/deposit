<?php 
namespace App\Libs;

use Monolog\Logger;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;

define('GALOG_PATH', [
    'landingtouch' => storage_path('/galogs/landingtouch/'), 

    'interface_cib' => storage_path('/galogs/interface_cib/'),

    'worker_router' => storage_path('/galogs/worker_router/'),
    'worker_sign' => storage_path('/galogs/worker_sign/'),
]);

class GearApiLog
{
    protected $_Loggers = [];
    
    public function __construct()
    {
        $this->init();
    }
    
    public function init()
    {
        foreach(GALOG_PATH as $log_type => $log_path){
            $formatter = new LineFormatter("%datetime% %context%: %message%\n", 'Y-m-d H:i:s');
            $stream = new StreamHandler($log_path . date('Ymd'). '.log', Logger::INFO);
            $stream->setFormatter($formatter);
            
            $logger = new Logger($log_type);
            $logger->pushHandler($stream);
            
            $this->_Loggers[$log_type] = $logger;
        }
    }
    
	public function log($message, $log_type, $event = '', $ga_traceno = null)
	{
	    if(isset($this->_Loggers[$log_type])){
	        $ga_traceno = $ga_traceno === null? app('ga_traceno') : $ga_traceno;
	        $context = ['log.no' => $ga_traceno];
	        if(!empty($event)){
	            $context['log.event'] = $event;
	        }
	        return $this->_Loggers[$log_type]->info($message, $context);
	    }
	    
	    return false;
	}
}

