<?php

namespace Module\Mapper\Cache;

class Recommend {

	//推荐位缓存
	public $getIndexRecommend = array(
		'cache'=>'redis',
		'time' =>120
	);
}
