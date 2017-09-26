<?php 
/**
 * 用于实现商场接口的抽象类
 *
 **/

namespace Core\Lib;

use \Core\Lib\MNLogger\EXLogger;
use \Core\Config\WmApi as WmApiConfig;
use \Core\Lib\Api\CurlRequest as CurlRequest;

class WmApi extends \Core\Lib\Api\ApiBase{
    public  $httpCode = '';
    public  $httpHeader = ''; 
    public  $content = array();
    public  $result  = false;
    public  $data = '';
    public  $lastUrl;

	protected function __construct(){
		parent::__construct('WmApi','read');
	}
	
	//格式化参数
	public function formatParams(&$params){
		$result = array();
		\Module\Common::addSid($params); //统一添加SESSIONID
        $result['data'] = [
                'spNumber'       => $this->spNumber,  //渠道ID
                'serviceName'    => $this->serviceName,  //接口名称
                'params'         => $params,  //接口参数列表
            ];
        $result['reqData'] =  [
            "data" => json_encode($result['data']),
            'sign' => sha1( $this->sign .json_encode($result['data']))  //签名
        ];
		$params = $result;
	}

	
	//获取缓存KEY
	public function getCacheKey(){
		$params = $this->params['data']['params'];
		unset($params['sessionId']);
		return $this->url.$this->serviceName.json_encode($params);
	}
	
	//curl请求
	public function curlResponse($params){
		$obj = new CurlRequest($this->url,'WmApi');
		$obj->requestMethod('post')->setRequestData($params['reqData'])->setProxy($this->prox);
		return $obj->getRequestInfo();
	}
	
} 

	
?>