<?php 
namespace App\Libs;

class FormatResult
{
	public $transaction_no = '';
	public $mch_no = '';
	public $timestamp = '';
	public $biz_type = '';
	public $code = '';
	public $message = '';
	public $biz_content = array();
	public $sign_type = '';

	public function __construct($data, $transaction_no = '')
	{
		foreach($data as $k => $v){
			if(isset($this->$k) && $k != 'biz_content'){
				$this->$k = $v;
			}
		}
		$this->transaction_no = $transaction_no;
		$this->sign_type = 'MD5';
		$this->timestamp = date('YmdHis');
	}

	public function getData()
	{
		$ret = get_object_vars($this);
		if(isset($ret['code'])){
			$ret['code'] = $ret['code'] . '';
		}
		foreach($ret['biz_content'] as $k => $v){
			if(is_string($v) || is_numeric($v) || is_float($v) || is_double($v)){
				$ret['biz_content'][$k] = $v . '';
			}
		}
		
		if(empty($ret['biz_content'])){
			$ret['biz_content'] = new \stdClass();
		}

		return $ret;
	}
	
	public function setError($code)
	{
	    $codemap = isset(FormatResultErrors::CODE_MAP[$code])? FormatResultErrors::CODE_MAP[$code] : FormatResultErrors::CODE_MAP['SYS.ERR'];
        $this->code = $codemap['code'];
        $this->message = $codemap['message'];
	}
	
	public function setSuccess($biz_content)
	{
	    $codemap = FormatResultErrors::CODE_MAP['SUCCESS'];
	    $this->code = $codemap['code'];
	    $this->message = $codemap['message'];
	    
	    $this->biz_content = $biz_content;
	}
}