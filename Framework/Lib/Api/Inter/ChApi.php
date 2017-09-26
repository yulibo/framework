<?php 

namespace Core\Lib\Api\Inter;

use \Core\Lib\Api\CurlRequest as CurlRequest;

class ChApi  extends \Core\Lib\Api\ApiBase{

	private static $signFormat = 'PHONE=%s:TS=%s'; //默认签名格式
	private static $requestMethod = 'get'; //接口请求方式
	protected function __construct(){
		parent::__construct('ChApi','default');
	}
	
	//格式化参数
	public function formatParams(&$params){
		$params['TS'] = \Module\Common::udate('YmdHisu');
		if(!isset($params['SIGN_F'])){
		    $params['SIGN_F'] = self::$signFormat;//默认签名格式
		} 
		$params['MAC'] = \Module\Common::getMac($params,$this->sign); //获取mac
		$params['SYSTEM_ID'] = $this->system_id;
		if(isset($params['requestMethod'])){
			self::$requestMethod = $params['requestMethod']; //请求方式
		}
		self::$requestMethod = strtolower(self::$requestMethod);
		unset($params['SIGN_F'],$params['requestMethod']);
	}
	
	//获取缓存KEY
	public function getCacheKey(){
		unset($this->params['TS'],$this->params['MAC'],$this->params['sessionId']);
		return $this->url.$this->serviceName.json_encode($this->params);
	}
	
	
	//curl请求
	public function curlResponse($params){
		$url =$this->url.'/'.$this->serviceName;
		$obj = new CurlRequest($url,'ChApi');
		$obj->requestMethod(self::$requestMethod)->setProxy($this->prox);
		return $obj->setRequestData($params)->getRequestInfo();
	}
} 

	
?>