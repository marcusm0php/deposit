<?php 
namespace App\Libs;

use PhpParser\Node\Expr\Isset_;
class FormatResultErrors
{
    const CODE_MAP = [
        'SUCCESS' => [
            'code' => '100', 
        ], 
        'SYS.ERR' => [
            'code' => '101', 
            'message' => '系统错误', 
        ], 
        'SIGN.VERIFY.FAIL' => [
            'code' => '104', 
            'message' => '签名验证失败', 
        ], 
    ];
}

class FormatResult
{
	public $mch_no = '';
	public $timestamp = '';
	public $biz_type = '';
	public $code = '';
	public $message = '';
	public $biz_content = array();
	public $sign_type = '';

	public function __construct($data)
	{
		foreach($data as $k => $v){
			if(isset($this->$k) && $k != 'biz_content'){
				$this->$k = $v;
			}
		}
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
}