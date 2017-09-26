<?php

namespace Core\Lib\Api;

class CurlRequest{
	
	public $beginTimeT;//请求开始时间
	public $endTimeT;//请求结束时间
	public $costTimeT;//请求总的花费时间
	public $requestState=1;//请求状态 
	public $url;//请求地址
	public $curlObj;//curl对象
	public $header;//curl header
	private $timeOut=35;//curl 默认超时时间
	private $requestData;//请求数据
	private $requestMethod = 'post';//请求方式
	public $response;//请求返回信息
	public $headerInfo;//请求返回头信息
	public $httpCode;//状态码
	private $logName;//日志记录地方
	
	const CURL_POST = 'post';//是否是post请求

	
	public function __construct($url,$logName){
		$this->initCurl($url);
		$this->setHeader();
		$this->logName = $logName;
		return $this;
	}
	
	//初始化curl 对象
	private function initCurl($url){
		$this->beginTimeT =  round(microtime(1),3);
		$this->beginTime = date("Y-m-d H:i:s",$this->beginTimeT);
		$this->curlObj = curl_init();
		curl_setopt($this->curlObj,CURLOPT_URL,$url);
		curl_setopt($this->curlObj,CURLOPT_FOLLOWLOCATION,1);
        curl_setopt($this->curlObj,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($this->curlObj,CURLOPT_TIMEOUT, $this->timeOut);
		$this->url = $url;
	}
	
	//设置头信息
	public function setHeader(array $header=array()){
        if (isset($_SESSION['sessionId'])){
            $header[]   = 'Cookie:JSESSIONID='.$_SESSION['sessionId'];
        }
	    curl_setopt($this->curlObj,CURLOPT_HTTPHEADER, $header);
        curl_setopt($this->curlObj,CURLOPT_HEADER,0);
		return $this;
	}
	
	//设置超时时间
	public function timeOut($time){
		curl_setopt($this->curlObj,CURLOPT_TIMEOUT, $time);
		return $this;
	}
	
	//设置请求方式
	public function requestMethod($method){
		$method = strtolower($method);
		if(!in_array($method,array('post','get'))){
			throw new \Exception('请求方式错误');
		}
		$this->requestMethod = $method;
		return $this;
	}
	
	//设置代理
	public function setProxy($prox=''){

		if(!empty($prox)){
			curl_setopt($this->curlObj, CURLOPT_PROXY, $prox);
		}
		return $this;
	}
	
	//设置请求数据
	public function setRequestData(array $data=array()){
		if($this->requestMethod==self::CURL_POST){
			curl_setopt($this->curlObj,CURLOPT_POST,1);
            curl_setopt($this->curlObj,CURLOPT_POSTFIELDS,http_build_query($data));
		}else{
			curl_setopt($this->curlObj,CURLOPT_URL,$this->url.'?'.http_build_query($data));
		}
		$this->requestData = $data;
		return $this;
	}
	
	//获取请求返回的信息
	public function getRequestInfo(){
		try {

			$this->response = curl_exec($this->curlObj);
			$this->headerInfo = curl_getinfo($this->curlObj);
			$this->httpCode = curl_getinfo($this->curlObj,CURLINFO_HTTP_CODE);
			$this->endTimeT = round(microtime(1),3); //结束时间
			$this->costTimeT = round($this->endTimeT - $this->beginTimeT,3); //花费时间
			if($this->response === false || trim($this->response) == ""){
                $this->requestState = 0;
            }
			curl_close($this->curlObj);

		} catch (\Exception $ex) {
		   $this->response = false;
        }
		$this->addLog();
		return $this->response;
	}
	
	//添加日志
	private function addLog(){
		$begin_time = $this->beginTime;
		$url = $this->url;
		$data = $this->requestData;
		$cost_time = $this->costTimeT;
		$request_state = $this->requestState;
		$response = $this->response;
		$headerInfo = $this->headerInfo;
		$httpCode =  $this->httpCode;
		$remoteAddr = $_SERVER['REMOTE_ADDR'];
		$api_log = compact("begin_time","url","data","cost_time","request_state","response","headerInfo","httpCode","remoteAddr");
        \Core\Lib\Log::instance($this->logName)->log($api_log);
	}
}
