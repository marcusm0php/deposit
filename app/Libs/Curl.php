<?php 
namespace App\Libs;

class Curl {
	protected $_curl;
	protected function _cInit()
	{
		$this->_curl = curl_init();
		curl_setopt($this->_curl, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($this->_curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($this->_curl, CURLOPT_POST, 1);
		curl_setopt($this->_curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($this->_curl, CURLOPT_HTTPHEADER, array(
            'application/x-www-form-urlencoded; charset=UTF-8',
			'Content-Type: text/plain'
        ));
	}
	
	public function __construct()
	{
		$this->_cInit();
	}
	
	public function setopt($k, $v)
	{
		curl_setopt($this->_curl, $k, $v);
	}
	
    public function postExe($target, $postRawData)
	{
		curl_setopt($this->_curl, CURLOPT_URL, $target);
		curl_setopt($this->_curl, CURLOPT_POSTFIELDS, $postRawData);
		$result = curl_exec($this->_curl);

		return $result;
	}
	
	public function getExe($target, $getData)
	{
		curl_setopt($this->_curl, CURLOPT_POST, 0);
		
		if(strpos($target, '?') === false){
			$url = $target . '?' . http_build_query($getData);
		}else{
			$url = $target . '&' . http_build_query($getData);
		}
		
		curl_setopt($this->_curl, CURLOPT_URL, $url);
		$result = curl_exec($this->_curl);

		return $result;
	}
}

