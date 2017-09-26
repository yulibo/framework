<?php
namespace Core\Lib;

use Core\Lib\Sys;

/**
 * Redis client with distributed servers support.Port for webservice legacy.
 * @global $CONFIG['Redis']['groups']
 */
class RedisDistributed
{
    /**
     * instance pool.Each configuration should has only one instance.
     *
     * @var array
     */
    protected static $instances = array ();


    /**
     * Get a redis instance.
     * @param string $endpoint connection configruation name.
     * @param string $as use redis as "cache" or storage.default: storage
     * @return \RedisCache|\RedisStorage
     */
    public static function instance($endpoint = 'default', $as='storage')
    {
        static $configs;
        if(!$configs)
        {
            $configs = \Core\Lib\Sys::getCfg('Redis');
            $configs = get_object_vars($configs);
        }

        $classNameBase = $as === 'storage' ? 'RedisMultiStorage' : 'RedisMultiCache';
        $className = 'Core\\Lib\\Redis\\'.$classNameBase;

        if(!class_exists($className, false))
        {
            $className::config($configs);
        }
        return $className::getInstance($endpoint);
    }
}
