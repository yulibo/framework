<?php

namespace Module\Mapper\Cache;

class User {
	
	
	//用户基本信息
	public $getUserInfoByPhone = array(
		'cache'=>'redis',
		'time' =>1800
	);
	
}
