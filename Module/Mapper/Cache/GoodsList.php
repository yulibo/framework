<?php

namespace Module\Mapper\Cache;

class GoodsList {

	//商品列表缓存
	public $queryGoodsList = array(
		'cache'=>'redis',
		'time' =>3600
	);

	//兑换商品详情缓存
	public $getWmVirGoods = array(
		'cache'=>'redis',
		'time' =>300
	);

	//推荐位缓存
	public $getIndexRecommend = array(
		'cache'=>'redis',
		'time' =>3600
	);

	//店铺导航缓存
	public $getStoreRecommend = array(
		'cache'=>'redis',
		'time' =>100000
	);

	//商品搜索热门词缓存
	public $getWmHotKeyWordList = array(
		'cache'=>'redis',
		'time' =>120
	);

}
