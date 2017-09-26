<?php
namespace Core\Config;

/**
 * Redis 配置
 */
class Redis{

/**
 * options for redis connections.
 * <ul>
 * <li>
 * dist_options主要包括autorehash和previous. 如果servers配置有变动,在使用一致性hash的分布式的情况下需要通过dist_options参数做key的转移.
 * </li>
 * <li>
 * options 参数主要包括Redis::OPT_PREFIX, Redis::OPT_SERIALIZER.这些选项一旦设置,将影响数据的存储方式,因此设置之后请不要修改.
 * 其中Redis::OPT_SERIALIZER可以让你直接将php数据结构或者对象存入redis, 类似memcache,但将限制与运算相关的方法的使用,如increase, range等.
 * </li>
 * <li>
 * 使用分布式服务时,将影响到multi/excec方法的使用(只能在单点上操作).请尽量避免使用此方法.
 * 多key的使用也有所不同,操作时请特别注意.
 * </li>
 * <ul>
 * @var array
 */
public $default = array(
		'nodes' => array(
			array('master' => '10.143.62.155:6379', 'slave' => '10.143.62.155:6379'),
		),
        'db' => 8,
//  'password' => null,
//  'options' => array(Redis::OPT_SERIALIZER => Redis::SERIALIZER_PHP),
//  'dist_options' => array('autorehash'=>True),
        );

//电商推荐存储redis配置
public $fav = array(
	'nodes' => array(
                        array('master' => '10.143.62.155:6379', 'slave' => '10.143.62.155:6379'),
        ),	
        'password' => null,
        // 'options' => array(Redis::OPT_SERIALIZER => Redis::SERIALIZER_PHP),
        'dist_options' => array('autorehash'=>True),
        'db' => 9,
);

//用户信息的redis配置
public $user = array(
	'nodes' => array(
                        array('master' => '10.143.62.155:6379', 'slave' => '10.143.62.155:6379'),
                ),
        'password' => null,
        // 'options' => array(Redis::OPT_SERIALIZER => Redis::SERIALIZER_PHP),
        'dist_options' => array('autorehash'=>True),
        'db' => 10,
);
public $sms = array(
    'nodes' => array(
        array('master' => '10.143.62.155:6379', 'slave' => '10.143.62.155:6379'),
    ),
    'password' => null,
    // 'options' => array(Redis::OPT_SERIALIZER => Redis::SERIALIZER_PHP),
    'dist_options' => array('autorehash'=>True),
    'db' => 3,
);
}
