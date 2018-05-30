<?php 
/**
 * @author MarcusM. works for yinzhun. QQ:2453302174
 */
 

class CibReq
{
	protected $_params = array();
	protected $_key;
	
	public function __construct($appid, $mch_id, $key)
	{
		$this->initParams();

		$this->setParam('appid', $appid);
		$this->setParam('mch_id', $mch_id);
		$this->setKey($key);
	}
	
	public function initParams()
	{
		$this->_params = array(
			'version' => '2.0',
			'charset' => 'UTF-8',
			'sign_type' => 'MD5',
			'is_raw' => '1',
			'sign' => '',
		);
	}

	public static function toXml($array){
		$xml = '<xml>';
		forEach($array as $k=>$v){
			$xml.='<'.$k.'><![CDATA['.$v.']]></'.$k.'>';
		}
		$xml.='</xml>';
		return $xml;
	}
	
	public function setKey($key)
	{
		$this->_key = $key;
	}
	
	public function getKey()
	{
		return $this->_key;
	}
	
	public function setParam($key, $value)
	{
		$this->_params[$key] = $value;
	}
	
	public function getParams($format = 'xml', $filter = true)
	{
		$params = $this->_params;
		if($filter){
			$params = array_filter($params, function($v){
				return !($v == '' || $v == null);
			});
		}
		
		if($format == 'xml'){
			$paramsRet = self::toXml($params);
		}else{
			$paramsRet = $params;
		}
		
		return $paramsRet;
	}
	
	public function createSign()
	{   
		$signPars = '';
		ksort($this->_params);
		foreach($this->_params as $k => $v){
			if("" != $v && "sign" != $k){
				$signPars .= $k . "=" . $v . "&";
			}
		}
		$signPars .= "key=" . $this->getKey();
		$sign = strtoupper(md5($signPars));
		$this->setParam("sign", $sign);
		
		return $sign;
	}
}

class CibRes
{
	protected $_data = array();

	const STATUS_OK = '0';
	const RETURN_CODE_OK = '0';
	const ERROR_CODE_USERPAYING = 'USERPAYING';

	const TRADE_STATE_USERPAYING = 'USERPAYING';
	const TRADE_STATE_SUCCESS = 'SUCCESS';
	const TRADE_STATE_REVOKED = 'REVOKED';
	const TRADE_STATE_NOTPAY = 'NOTPAY';

	const NEED_QUERY_Y = 'Y';
	const NEED_QUERY_N = 'N';
	
	public function __construct($xml)
	{
		$this->_data = self::parseXML($xml);
	}
	
	public function getData($key = null)
	{
		if($key !== null){
			return isset($this->_data[$key])? $this->_data[$key] : null;
		}
		
		return $this->_data;
	}

	public static function parseXML($xmlSrc){
		if(empty($xmlSrc)){
			return false;
		}
		$array = array();
		
		try{
    		@$xml = simplexml_load_string($xmlSrc);
    		@$encode = self::getXmlEncode($xmlSrc);
		}catch (Exception $e){
		    return false;
		}
		
		if(empty($xml)){
		    echo 'xmlSrc error: ' . $xmlSrc;
		    
		    return array(
		        'status' => CibRes::STATUS_OK, 
		        'result_code' => '1', 
                'err_code' => 'ACQ.SYSTEM_ERROR', 
                'err_msg' => '网关响应错误', 
		    );
		}
	
		if($xml && $xml->children()) {
			foreach ($xml->children() as $node){
				//有子节点
				if($node->children()) {
					$k = $node->getName();
					$nodeXml = $node->asXML();
					$v = substr($nodeXml, strlen($k)+2, strlen($nodeXml)-2*strlen($k)-5);
						
				} else {
					$k = $node->getName();
					$v = (string)$node;
				}
	
				$k = iconv("UTF-8", $encode, $k);
				$v = iconv("UTF-8", $encode, $v);
				$array[$k] = $v;
			}
		}
		return $array;
	}
	
	public static function getXmlEncode($xml) {
		$ret = preg_match ("/<?xml[^>]* encoding=\"(.*)\"[^>]* ?>/i", $xml, $arr);
		if($ret) {
			return strtoupper ( $arr[1] );
		} else {
			return "";
		}
	}
}


class CibInterface
{
	protected $_curl;
	protected $_gateway_base = 'https://pay.cibpass.cn/pay/gateway';

	protected $_appid;
	protected $_mch_id;
	protected $_key;
	
	public function init()
	{
		$this->_curl = new Curl();
	}
	
	public function initParam($appid, $mch_id, $key)
	{
		$this->_appid = $appid;
		$this->_mch_id = $mch_id;
		$this->_key = $key;
	}
	
	protected function _execPost($data, $logfilename = 'wxpay_interface')
	{
		$uuid = create_uuid();
		lgcib('req('.$uuid.'):' . $data, null, $logfilename);
		$ret = $this->_curl->postExe($this->_gateway_base, $data);

		lgcib('response('.$uuid.'):' . $ret, null, $logfilename);
		
		return $ret;
	}
	
	/************************************** wx **********************************/
	public function WxPrepay($total_fee, $out_trade_no, $body = '', $mch_create_ip = null, $attach = '', $notifyUrl = null, $time_start = null, $time_expire = null)
	{
		$mch_create_ip = empty($mch_create_ip)? $_SERVER['REMOTE_ADDR'] : $mch_create_ip;
		$body = empty($body)? '微信支付' : $body;
		
		$reqParams = new CibReq($this->_appid, $this->_mch_id, $this->_key);
		$reqParams->setParam('service', 'pay.weixin.native');
		$reqParams->setParam('out_trade_no', $out_trade_no);
		$reqParams->setParam('body', $body);
		$reqParams->setParam('sub_openid', '');
		$reqParams->setParam('total_fee', $total_fee * 100);
		$reqParams->setParam('mch_create_ip', $mch_create_ip);
		$reqParams->setParam('nonce_str', $this->_mch_id . time());
		$reqParams->setParam('notify_url', $notifyUrl);
		$reqParams->setParam('attach', $attach);

		if(!empty($time_expire)){
			$reqParams->setParam('time_start', (empty($time_start)? date('YmdHis') : $time_start));
			$reqParams->setParam('time_expire', $time_expire);
		}
		$reqParams->createSign();
		
		$ret = $this->_execPost($reqParams->getParams());
		
		$response = new CibRes($ret);
		return $response->getData();
	}
	
	public function WxJspay($total_fee, $out_trade_no, $sub_appid = '', $openid = '', $sub_openid = '', $is_minipg = '0', $body = '', $mch_create_ip = null, $attach = '', $callback_url = '', $notifyUrl = null, $time_start = null, $time_expire = null)
	{
		$mch_create_ip = empty($mch_create_ip)? $_SERVER['REMOTE_ADDR'] : $mch_create_ip;
		$body = empty($body)? '微信支付' : $body;
		
		$reqParams = new CibReq($this->_appid, $this->_mch_id, $this->_key);
		$reqParams->setParam('service', 'pay.weixin.jspay');
		$reqParams->setParam('sub_appid', $sub_appid);
		$reqParams->setParam('out_trade_no', $out_trade_no);
		$reqParams->setParam('body', $body);
		//$reqParams->setParam('openid', $openid);
		if(!empty($openid)){
            $reqParams->setParam('sub_openid', $openid);
		}else if(!empty($sub_openid)){
		    $reqParams->setParam('sub_openid', $sub_openid);
		}
		$reqParams->setParam('total_fee', $total_fee * 100);
		$reqParams->setParam('is_minipg', $is_minipg);
		$reqParams->setParam('mch_create_ip', $mch_create_ip);
		$reqParams->setParam('nonce_str', $this->_mch_id . time());
		$reqParams->setParam('callback_url', $callback_url);
		$reqParams->setParam('notify_url', $notifyUrl);
		$reqParams->setParam('attach', $attach);

		if(!empty($time_expire)){
			$reqParams->setParam('time_start', (empty($time_start)? date('YmdHis') : $time_start));
			$reqParams->setParam('time_expire', $time_expire);
		}
		$reqParams->createSign();
		
		$ret = $this->_execPost($reqParams->getParams());
		
		$response = new CibRes($ret);
		return $response->getData();
	}
	
	public function WxWappay($total_fee, $out_trade_no, $device_info = '', $mch_app_name = '', $mch_app_id = '', $groupno = '', $body = '', $mch_create_ip = null, $attach = '', $callback_url = '', $notifyUrl = null, $time_start = null, $time_expire = null)
	{
		$mch_create_ip = empty($mch_create_ip)? $_SERVER['REMOTE_ADDR'] : $mch_create_ip;
		$body = empty($body)? '微信支付' : $body;
		
		$reqParams = new CibReq($this->_appid, $this->_mch_id, $this->_key);
		$reqParams->setParam('service', 'pay.weixin.wappay');
		$reqParams->setParam('out_trade_no', $out_trade_no);
		$reqParams->setParam('body', $body);
		$reqParams->setParam('total_fee', $total_fee * 100);
		$reqParams->setParam('mch_create_ip', $mch_create_ip);
		$reqParams->setParam('device_info', $device_info);
		$reqParams->setParam('mch_app_name', $mch_app_name);
		$reqParams->setParam('mch_app_id', $mch_app_id);
		$reqParams->setParam('groupno', $groupno);
		$reqParams->setParam('nonce_str', $this->_mch_id . time() . rand(1000, 9999));
		$reqParams->setParam('callback_url', $callback_url);
		$reqParams->setParam('notify_url', $notifyUrl);
		$reqParams->setParam('attach', $attach);

		if(!empty($time_expire)){
			$reqParams->setParam('time_start', (empty($time_start)? date('YmdHis') : $time_start));
			$reqParams->setParam('time_expire', $time_expire);
		}
		$reqParams->createSign();
		
		$ret = $this->_execPost($reqParams->getParams());
		
		$response = new CibRes($ret);
		return $response->getData();
	}
    
    public function AliJspay($total_fee, $out_trade_no, $body = '', $mch_create_ip = null, $attach = '', $callback_url = '', $notifyUrl = null, $time_start = null, $time_expire = null, $qr_code_timeout_express = '120m', $op_user_id = '',$product_id = '',$buyer_logon_id = '',$buyer_id = '')
	{
		$mch_create_ip = empty($mch_create_ip)? $_SERVER['REMOTE_ADDR'] : $mch_create_ip;
		$body = empty($body)? '支付宝支付' : $body;
		
		$reqParams = new CibReq($this->_appid, $this->_mch_id, $this->_key);
		$reqParams->setParam('service', 'pay.alipay.jspay');
		$reqParams->setParam('out_trade_no', $out_trade_no);
		$reqParams->setParam('body', $body);
		//$reqParams->setParam('openid', $openid);
		$reqParams->setParam('total_fee', $total_fee * 100);
		$reqParams->setParam('mch_create_ip', $mch_create_ip);
		$reqParams->setParam('nonce_str', $this->_mch_id . time());
		$reqParams->setParam('callback_url', $callback_url);
		$reqParams->setParam('notify_url', $notifyUrl);
		$reqParams->setParam('attach', $attach);

// 		if(!empty($time_expire)){
// 			$reqParams->setParam('time_start', (empty($time_start)? date('YmdHis') : $time_start));
// 			$reqParams->setParam('time_expire', $time_expire);
// 		}
		if(!empty($qr_code_timeout_express)){
			$reqParams->setParam('qr_code_timeout_express', $qr_code_timeout_express);
		}
        $reqParams->setParam('buyer_logon_id', $buyer_logon_id);
        $reqParams->setParam('buyer_id', $buyer_id);
        $reqParams->setParam('op_user_id', $op_user_id);
        $reqParams->setParam('product_id', $product_id);
        
		$reqParams->createSign();
		
		$ret = $this->_execPost($reqParams->getParams(),'alipay_interface');
		
		$response = new CibRes($ret);
		return $response->getData();
	}
	
	public function WxApppay($total_fee, $out_trade_no, $body = '', $appid = '', $sub_appid = '', $mch_create_ip = null, $attach = '', $callback_url = '', $notifyUrl = null)
	{
		$mch_create_ip = empty($mch_create_ip)? $_SERVER['REMOTE_ADDR'] : $mch_create_ip;
		$body = empty($body)? '微信支付' : $body;
	
		$reqParams = new CibReq($this->_appid, $this->_mch_id, $this->_key);
		$reqParams->setParam('service', 'pay.weixin.raw.app');
		$reqParams->setParam('appid', $appid);
		$reqParams->setParam('sub_appid', $sub_appid);
		$reqParams->setParam('out_trade_no', $out_trade_no);
		$reqParams->setParam('body', $body);
		$reqParams->setParam('sub_openid', '');
		$reqParams->setParam('total_fee', $total_fee * 100);
		$reqParams->setParam('mch_create_ip', $mch_create_ip);
		$reqParams->setParam('nonce_str', $this->_mch_id . time());
		$reqParams->setParam('callback_url', $callback_url);
		$reqParams->setParam('notify_url', $notifyUrl);
		$reqParams->setParam('attach', $attach);
		$reqParams->createSign();
	
		$ret = $this->_execPost($reqParams->getParams());
	
		$response = new CibRes($ret);
		return $response->getData();
	}
	
	/************************************** Ali **********************************/
	public function AliPrepay($total_fee, $out_trade_no, $body = '', $mch_create_ip = null, $attach = '', $notifyUrl = null, $time_start = null, $time_expire = null, $qr_code_timeout_express = '120m')
	{
		$mch_create_ip = empty($mch_create_ip)? $_SERVER['REMOTE_ADDR'] : $mch_create_ip;
		$body = empty($body)? '微信支付' : $body;
	
		$reqParams = new CibReq($this->_appid, $this->_mch_id, $this->_key);
		$reqParams->setParam('service', 'pay.alipay.native');
		$reqParams->setParam('out_trade_no', $out_trade_no);
		$reqParams->setParam('body', $body);
		$reqParams->setParam('total_fee', $total_fee * 100);
		$reqParams->setParam('mch_create_ip', $mch_create_ip);
		$reqParams->setParam('nonce_str', $this->_mch_id . time());
		$reqParams->setParam('notify_url', $notifyUrl);
		$reqParams->setParam('attach', $attach);
	
// 		if(!empty($time_expire)){
// 			$reqParams->setParam('time_start', (empty($time_start)? date('YmdHis') : $time_start));
// 			$reqParams->setParam('time_expire', $time_expire);
// 		}
		if(!empty($qr_code_timeout_express)){
			$reqParams->setParam('qr_code_timeout_express', $qr_code_timeout_express);
		}
		$reqParams->createSign();
	
		$ret = $this->_execPost($reqParams->getParams(), 'alipay_interface');
	
		$response = new CibRes($ret);
		return $response->getData();
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	

	public function Micropay($total_fee, $auth_code, $out_trade_no, $body = '', $mch_create_ip = null, $attach = '', $notifyUrl = null, $time_expire = 60, $logfilename = 'wxpay_interface')
	{
		$mch_create_ip = empty($mch_create_ip)? $_SERVER['REMOTE_ADDR'] : $mch_create_ip;
		$body = empty($body)? '微信支付' : $body;
	
		$reqParams = new CibReq($this->_appid, $this->_mch_id, $this->_key);
		$reqParams->setParam('service', 'unified.trade.micropay');
		$reqParams->setParam('auth_code', $auth_code);
		$reqParams->setParam('out_trade_no', $out_trade_no);
		$reqParams->setParam('body', $body);
		$reqParams->setParam('total_fee', $total_fee * 100);
		$reqParams->setParam('mch_create_ip', $mch_create_ip);
		$reqParams->setParam('nonce_str', $this->_mch_id . time());
		$reqParams->setParam('notify_url', $notifyUrl);
		$reqParams->setParam('attach', '');
	
		$reqParams->createSign();
	
		$ret = $this->_execPost($reqParams->getParams(), $logfilename);
		$response = new CibRes($ret);

		if($response->getData('status') == CibRes::STATUS_OK){
		    if($response->getData('result_code') != CibRes::RETURN_CODE_OK){
		        if($response->getData('err_code') == CibRes::ERROR_CODE_USERPAYING){
		            if($response->getData('need_query') == CibRes::NEED_QUERY_Y){
		                $time_expire = empty($time_expire)? 60 : $time_expire;
		                $time_expire = ($time_expire > 60)? 60 : $time_expire;
		                $timeWait = $time_expire;
		    
		                while(true){
		                    sleep(4);
		                    $timeWait -= 5;
		                    if($timeWait <= 0){
		                        break;
		                    }
		                    
		                    $reqRetryQueryParams = new CibReq($this->_appid, $this->_mch_id, $this->_key);
		                    $reqRetryQueryParams->setParam('service', 'unified.trade.query');
		                    $reqRetryQueryParams->setParam('out_trade_no', $out_trade_no);
		                    $reqRetryQueryParams->setParam('nonce_str', $this->_mch_id . time());
		                    $reqRetryQueryParams->createSign();
		                    $retryQueryRet = $this->_execPost($reqRetryQueryParams->getParams(), $logfilename);
		    
		                    $retryQueryResponse = new CibRes($retryQueryRet);
		    
		                    if($retryQueryResponse->getData('status') == CibRes::STATUS_OK){
		                        if($retryQueryResponse->getData('trade_state') == CibRes::TRADE_STATE_USERPAYING){
		                            continue;
		                        }else if($retryQueryResponse->getData('trade_state') == CibRes::TRADE_STATE_SUCCESS){
		                            return $retryQueryResponse->getData();
		                        }else if(in_array($retryQueryResponse->getData('trade_state'), array(CibRes::TRADE_STATE_NOTPAY, CibRes::TRADE_STATE_REVOKED))){
		                            return array(
		                                'status' => CibRes::STATUS_OK,
		                                'result_code' => '99',
		                                'err_code' => 'USERNOTPAY'
		                            );
		                        }
		                    }else{
		                        if($retryQueryResponse->getData('status') == '400'){
		                            return array(
		                                'status' => $retryQueryResponse->getData('status'),
		                                'result_code' => '99',
		                                'err_code' => 'REQFREQUENT'
		                            );
		                        }else{
		                            return array(
		                                'status' => $retryQueryResponse->getData('status'),
		                                'result_code' => '99',
		                                'err_code' => 'SYSTEMERROR'
		                            );
		                        }
		                    
		                    }
		                }
		    
		                return array(
		                    'status' => CibRes::STATUS_OK,
		                    'result_code' => '99',
		                    'err_code' => 'USERPAYINGOVERDUE'
		                );
		            }
		        }
		    }
		}else{
		    if($response->getData('status') == '400'){
		        return array(
		            'status' => $response->getData('status'),
		            'result_code' => '99',
		            'err_code' => 'REQFREQUENT'
		        );
		    }else{
		        return array(
		            'status' => $response->getData('status'),
		            'result_code' => '99',
		            'err_code' => 'SYSTEMERROR'
		        );
		    }
		    
		}
		
		return $response->getData();
	}
	
	public function Query($trade_no = '', $wx_trade_no = '', $logfilename = 'wxpay_interface')
	{
		$reqParams = new CibReq($this->_appid, $this->_mch_id, $this->_key);
		$reqParams->setParam('service', 'unified.trade.query');
		$reqParams->setParam('out_trade_no', $trade_no);	
		$reqParams->setParam('transaction_id', $wx_trade_no);	
		$reqParams->setParam('nonce_str', $this->_mch_id . time());
		$reqParams->createSign();
		
		$ret = $this->_execPost($reqParams->getParams(), $logfilename);
		
		$response = new CibRes($ret);
		return $response->getData();
	}
	
	public function Refund($refund_fee, $total_fee, $trade_no = '', $wx_trade_no = '', $out_refund_no = '', $logfilename = 'wxpay_interface')
	{
	    $out_refund_no = empty($out_refund_no)? $this->_mch_id . time() : $out_refund_no;
	    
		$reqParams = new CibReq($this->_appid, $this->_mch_id, $this->_key);
		$reqParams->setParam('service', 'unified.trade.refund');
		$reqParams->setParam('out_trade_no', $trade_no);
		$reqParams->setParam('transaction_id', $wx_trade_no);
		$reqParams->setParam('refund_fee', $refund_fee * 100);
		$reqParams->setParam('total_fee', $total_fee * 100);		
		$reqParams->setParam('op_user_id', $this->_mch_id);	
		$reqParams->setParam('out_refund_no', $out_refund_no);	
		$reqParams->setParam('nonce_str', $this->_mch_id . time());
		$reqParams->createSign();
		
		$ret = $this->_execPost($reqParams->getParams(), $logfilename);
		
		$response = new CibRes($ret);
		return $response->getData();
	}
	
	public function RefundQuery($refund_id, $out_trade_no = null, $transaction_id = null, $out_refund_no = null, $logfilename = 'wxpay_interface')
	{
		$reqParams = new CibReq($this->_appid, $this->_mch_id, $this->_key);
		$reqParams->setParam('service', 'unified.trade.refundquery');
		$reqParams->setParam('out_trade_no', $out_trade_no);	
		$reqParams->setParam('transaction_id', $transaction_id);	
		$reqParams->setParam('out_refund_no', $out_refund_no);	
		$reqParams->setParam('refund_id', $refund_id);	
		$reqParams->setParam('nonce_str', $this->_mch_id . time());
		$reqParams->createSign();
		
		$ret = $this->_execPost($reqParams->getParams(), $logfilename);
		
		$response = new CibRes($ret);
		return $response->getData();
	}
	
	public function Notify($url, $data, $logfilename = 'wxpay_mchnoti')
	{
		$curl = new Curl();
		$curl->setopt(CURLOPT_TIMEOUT, 20);
		
		$id = create_uuid();
		
		lgcib_mchnoti('post to [['. $url .']]:('.$id.')' . $data, null, $logfilename);
		$ret = $curl->postExe($url, $data);
		lgcib_mchnoti('response:('.$id.')' . $ret, null, $logfilename);
		
		return strtolower($ret) == 'success';
	}
	
}




