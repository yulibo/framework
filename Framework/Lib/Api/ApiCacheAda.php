<?php


namespace Core\Lib\Api;
use \Exception as Exception;

class ApiCacheAda implements \Core\Lib\Api\Face\ApiCache{
	
	public $getCacheInfo;
	public static $cacheKey;
	public $instance;
	const DEFAULT_CACHE = 'redis'; //默认缓存方式
	const DEFAULT_CACHE_TIME = 30;//默认缓存时间
	
	public static function getIns($model,$serviceName){
		$key = $model.'[]'.$serviceName;
		if(!isset($instance[$key])){
			$instance[$key] = new self($model,$serviceName);
		}
		return $instance[$key];
	}
	
	//获取缓存信息
	private function __construct($model,$serviceName){
		$class = "\Module\Mapper\Cache\\$model";
		if(!class_exists($class) ){
			return false;
		}
		$obj = new $class();
		if(!property_exists($obj,$serviceName)){
			return false;
		}
		$this->getCacheInfo = $obj->{$serviceName};
		$this->formatCacheInfo($this->getCacheInfo); //格式化缓存
	}
	
	//格式化设置缓存目标信息
	private function formatCacheInfo(&$cacheInfo){
		//设置默认缓存方式
		if(!isset($cacheInfo['cache'])){
			$cacheInfo['cache'] = self::DEFAULT_CACHE;
		}
		//设置默认缓存时间
		if(!isset($cacheInfo['time'])){
			$cacheInfo['time'] = self::DEFAULT_CACHE_TIME;
		}
	}
	
	//获取缓存KEY
	public function setCacheKey($str=''){
		self::$cacheKey =  md5($str);
	}
	
	//获取缓存对象
	public	function getCacheObj(){
		$cacheInfo = $this->getCacheInfo;
		if(!isset($cacheInfo['cache']) || empty($cacheInfo['cache'])){
			return false;
		}
		return ApiCache::instance($cacheInfo['cache']);
	}
	
	//获取缓存数据
	public function getCacheData(){
		$obj = $this->getCacheObj();
		if(empty($obj)){
			return false;
		}
		return $obj->getData(self::$cacheKey);
	}
	
	//设置缓存数据
	public function setCacheData($data){
		$obj = $this->getCacheObj();
		if(empty($obj)){
			return false;
		}
		$caheInfo = $this->getCacheInfo;
		return $obj->setData(self::$cacheKey,$data,$caheInfo['time']);
	}
}
