<?php 
namespace Core\Lib\Api;

use \Core\Lib\Sys;

abstract class ApiBase {
	
	protected $config; //配置
	protected $serviceName;//接口名称
	protected $params; //接口参数
	protected $model; //缓存的classname
	protected $apiCacheAda;//缓存对象
	protected $apiCacheKey;//接口缓存名称
	protected static $configdata;//配置数据
	protected static $instance;
	const ON_CACHE = true;//是否开启缓存
	
	protected function __construct($cfgname,$model){
		$this->getConfig($cfgname);
		$this->setConfig($model);
	}
	
    public static function instance(){
		$className = get_called_class();
        if(!static::$instance[$className]){
            static::$instance[$className] = new static();
        }
        return static::$instance[$className];
    }
	
	public function __get($key){
		if(isset(self::$configdata[get_class($this)][$key])){
			return self::$configdata[get_class($this)][$key];
		}
		return false;
	}
	
	//设置配置
	protected function getConfig($cfgname){
		$this->config = Sys::getCfg($cfgname);
	}
	
	
	//设置接口配置
	public function setConfig($model){
        self::$configdata[get_class($this)] = $this->config->$model;
        return $this;
    }
	
	//设置缓存KEY
	protected function setCacheKey(){
		$cacheKey = $this->getCacheKey();
		if(empty($cacheKey)){
			return false;
		}
		$cacheKey = md5($cacheKey);
		$this->setApiCacheAda()->setCacheKey($cacheKey);
	}
	
	//获取缓存KEY
	abstract public function getCacheKey();
	
	
	//设置api缓存
	protected function setApiCacheAda(){
		return  ApiCacheAda::getIns($this->model,$this->serviceName);
	}
	
	
	//格式化参数
	abstract public function formatParams(&$params);
	
	
	//接口实现
	public function httpRequest($params,$serviceName,$model=''){
		$this->serviceName = $serviceName; //接口名称
		$this->formatParams($params);//格式化参数
		$this->params = $params; //提交参数
		$this->model = $model; //model
		//关闭缓存
		if(empty(self::ON_CACHE)){
			return $this->curlResponse($params);//接口请求
		}
		$this->setCacheKey(); //设置缓存KEY
		$data = array();
		//开启缓存
		if(!($data = $this->setApiCacheAda()->getCacheData())){ //判断是否有缓存
			$data = $this->curlResponse($params);//接口请求
			$this->setApiCacheAda()->setCacheData($data);//设置缓存
		}
		return $data;
	}
	
	//接口实现curl
	abstract public function curlResponse($params);
	
} 
?>
