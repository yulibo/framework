<?php

/**
 * 接口父类
 */

namespace Core\Lib\Api;
use \Exception as Exception;

class ApiCache{
	
	protected $cacheAdapter;//缓存方式
	protected static $instance;
	
	private static $cahecList = array('redis','mem');
	
	private function __construct($cacheName){
		if(!in_array($cacheName,self::$cahecList)){
			return false;
		}
		switch ($cacheName) {
            case 'redis':
                $cacheAdapter = \Core\Lib\ControllerBase::instance()->redis();
				break;
            case 'mem':
                $cacheAdapter = \Core\Lib\ControllerBase::instance()->cache();
				break;
            default:
                trigger_error('try get undefined property: '.$name.' of class '.__CLASS__, E_USER_NOTICE);
                break;
        }
		$this->cacheAdapter = $cacheAdapter;
	}
	
	public static function instance($cacheName){
        if(!self::$instance[$cacheName]){
            self::$instance[$cacheName] = new self($cacheName);
        }
        return self::$instance[$cacheName];
    }
	
	//获取
	public function getData($key){
		return $this->cacheAdapter->get($key);
	}
	
	//设置
	public function setData($key,$value,$time=60){
		return $this->cacheAdapter->set($key,$value,$time);
	}
}
