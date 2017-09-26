<?php

namespace Module\Mapper\Cache;

class Goods {
	//商品详情缓存
	 public $queryGoodsDetail = array(
	 	'cache'=>'redis',
	 	'time' =>600
	 ); 

	//评论缓存
	public $queryCommentList = array(
		'cache'=>'redis',
		'time' =>600
	);

	//收货地址缓存
	public $getWmAddressCodeAndName = array(
		'cache'=>'redis',
		'time' =>600
	);
	
}