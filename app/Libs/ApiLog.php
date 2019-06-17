<?php 
namespace App\Libs;

use Monolog\Logger;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;

define('GALOG_PATH', [
    'landingtouch' => storage_path( env('API_LOGS_ROOT') . '/landingtouch/' ), 


]);

class ApiLog
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
    
	public function log($message, $log_type, $event = '', $api_traceno = null)
	{
	    if(isset($this->_Loggers[$log_type])){
	        $api_traceno = $api_traceno === null? app('api_traceno') : $api_traceno;
	        $context = ['log.api_traceno' => $api_traceno];
	        if(!empty($event)){
	            $context['log.event'] = $event;
	        }
	        return $this->_Loggers[$log_type]->info($message, $context);
	    }
	    
	    return false;
	}
}

