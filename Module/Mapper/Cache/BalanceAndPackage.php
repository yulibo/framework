<?php

namespace Module\Mapper\Cache;

class BalanceAndPackage {
	
	//余额查询
	public $queryPhoneBalance = array(
		'cache'=>'redis',
		'time' =>30
	);

	//套餐详情缓存
	public $queryPhoneInfo = array(
		'cache'=>'redis',
		'time' =>30
	);
	
}