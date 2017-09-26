<?php


namespace Core\Lib\Api\Face;


interface ApiCache{
	
	//获取缓存KEY
	public function setCacheKey($str='');
	
		
	//获取缓存数据
	public function getCacheData();
	
	
	//设置缓存数据
	public function setCacheData($data);
	
	//设置缓存对象
	public function getCacheObj();
}
