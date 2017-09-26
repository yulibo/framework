<?php
/**
 * Module Base file.
 * 
 * @author WangChengjin
 */

namespace Core\Lib;

abstract class ModuleBase
{
    /**
     *
     * Instances of the derived classes.
     * @var array
     */
    protected static $instances = array();

    /**
     * Get instance of the derived class.
     * @return \Core\Lib\ModuleBase
     */
    public static function instance()
    {
        $className = get_called_class();
        if (!isset(self::$instances[$className]))
        {
            self::$instances[$className] = new $className;
        }
        return self::$instances[$className];
    }

    /**
     * 获取cache.
     *
     * @param string $endpoint 获取的memcache名字.
     *
     * @return \Core\Lib\Memcache
     */
    public function cache($endpoint = 'default')
    {
        return \Core\Lib\MemcachePool::instance($endpoint);
    }

    /**
     * Get a redis instance.
     * @param string $endpoint connection configruation name.
     * @param string $as use redis as "cache" or storage.default: storage
     * @return \RedisCache|\RedisStorage
     */
    public function redis($endpoint = 'default', $as='storage')
    {
         return \Core\Lib\RedisDistributed::instance($endpoint, $as);
    }

    /**
     * 魔术方法 ，用来访问redis 或 memcahce.
     *
     * @param string $name 参数名.
     *
     * @return $mix       相关对象.
     */
    public function __get($name)
    {

        switch ($name)
        {
            case 'redis':
                return $this->redis();
            case 'cache':
                return $this->cache();
            default:
                trigger_error('try get undefined property: '.$name.' of class '.__CLASS__, E_USER_NOTICE);
                continue;
        }
    }

    /**
     * 获取平台架构监控日志对象.
     *
     * @param string $app 应用名称.
     *
     * @return mixed
     */
    public function getMNLogger($app)
    {
        static $loggers = array();
        if (!isset($loggers[$app])) {
            $config = Sys::getCfg('MNLogger');
            if (!property_exists($config, $app)) {
                throw new Exception('Missing configuration for `MNLogger::' . $app . '`');
            }
            $config = $config->$app;
            $loggers[$app] = new \MNLogger\MNLogger($config);
        }
        return $loggers[$app];
    }
    
    /**
     * 组装Cache key,变长参数，可以传若干字符或数字型参数.
     *
     * @return string 对应的key字符串.
     */
    public function generateKey()
    {
        $args = func_get_args();
        if ( $args ) {
            return implode('_', $args);
        }
        return '';
    }
	

    
    /**
     * 业务日志,需要根据具体业务配置Core\Config\Log.
     *
     * @param string $cfgName 日志配置.
     * @param array  $data    日志记录.
     */
    public function log($cfgName, $data) {
    	return \Core\Lib\Log::instance($cfgName)->log($data);
    }
    
    /**
     * 获取模块配置.
     *
     * @param string $key 配置参数.
     * @param string $np  模块配置的命名空间.
     *
     * @return mixed
     */
    public function getBiz($key, $np = \Core\Lib\ControllerBase::CTR_MODULE_MALL) {
    	$cnfClass = DEBUG_MODE ? 'BizDebug' : 'Biz';
    	$biz = \Core\Lib\Sys::getAppCfg($np, $cnfClass);
    	return $biz->{$key};
    }
}