<?php
/**
 * Description of ControllerBase
 *
 * @author Chengjin Wang
 */

namespace Core\Lib;

class ControllerBase {
    // 异常提示类型
    const CTR_ERROR_MESSAGE = 'error';//错误
    const CTR_WARNING_MESSAGE = 'warning';//警告
    const CTR_NOTICE_MESSAGE = 'notice';//提示
    const CTR_SHOW_MESSAGE = 'show';//提示
    // 模块命名空间
    const CTR_MODULE_MALL = 'Mall';
    const CTR_MODULE_STORE = 'Store';
    const CTR_MODULE_ADMIN = 'Admin';
	const CTR_MODULE_MOBILE = 'Mobile';
	const CTR_MODULE_STREAM = 'Stream';
	
    protected static $instance;
	
	protected function __construct(){
		$this->testUser(); //随机用户测试
		$this->init();
	}
	
	protected function testUser(){
		if(!isset($_GET['rand'])){
			return false;
		}
		$num = mt_rand(1,49999);
		$_SESSION['user_info']['user_phone'] = 13550361902+$num;
		$_SESSION['user_info']['user_id'] = $num;
	}
	
	protected function init(){
		\Module\Common::checkOnMobiel();//判断是否在手机端访问 mall
		\Module\Common::setChannel(); //设置渠道号
		SqlSafe::instance(); //sql注入 安全过滤
	}
    
    /**
     *
     * @return self
     */
    public static function instance() {
        if(!static::$instance) {
            static::$instance = new static;
        }
        return static::$instance;
    }
    /**
     * 
     * @param unknown $templateFile
     * @param unknown $var
     */
    public function setToken($value){
        Sys::getCfg('Smarty')->token=$value;
    }
	//模板解析前
	protected function fetchStart(){
		
	}
    public function fetch($templateFile, $var) {
		$this->fetchStart();
        $classPath = str_replace('\\', DIRECTORY_SEPARATOR, get_called_class());
        $portion = explode(DIRECTORY_SEPARATOR, $classPath);
        array_pop($portion);
        $namespaceDir = implode('\\', $portion);
        \Module\Common::instance()->stripslashesextended($var);
        \Core\Lib\Smarty::instance()->fetch($templateFile, $var, $namespaceDir);
		return $this->fetchEnd();
    }
	
		
	//模板解析后
	protected function fetchEnd(){
		
	}
    public function fetchout($templateFile, $var) {	
        $classPath = str_replace('\\', DIRECTORY_SEPARATOR, get_called_class());
        $portion = explode(DIRECTORY_SEPARATOR, $classPath);
        array_pop($portion);
        $namespaceDir = implode('\\', $portion);
        \Module\Common::instance()->stripslashesextended($var);
        return \Core\Lib\Smarty::instance()->fetchout($templateFile, $var, $namespaceDir);
    }
    /**
     * get a memcache instance
     * @return \Core\Lib\Memcache
     */
    public function cache($endpoint = 'default') {
        return \Core\Lib\MemcachePool::instance($endpoint);
    }

    /**
     * Get a redis instance.
     * @param string $endpoint connection configruation name.
     * @param string $as use redis as "cache" or storage. default: storage
     * @return \RedisCache|\RedisStorage
     */
    public function redis($endpoint = 'default', $as='storage') {
        return \Core\Lib\RedisDistributed::instance($endpoint, $as);
    }

    public function __get($name) {
        switch ($name) {
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
     * URL重定向
     * @param string $url 重定向的URL地址
     * @param integer $time 重定向的等待时间（秒）
     * @param string $msg 重定向前的提示信息
     * @return void
     */
    public function redirect($url, $time = 0, $msg = '') {
        //多行URL地址支持
        $url = str_replace(array("\n", "\r"), '', $url);
        if (empty($msg))
            $msg = "系统将在{$time}秒之后自动跳转到{$url}！";
        if (!headers_sent()) {
            // redirect
            if (0 === $time) {
                header('Location: ' . $url);
            } else {
                header("refresh:{$time};url={$url}");
                echo($msg);
            }
            exit();
        } else {
            $str = "<meta http-equiv='Refresh' content='{$time};URL={$url}'>";
            if ($time != 0)
                $str .= $msg;
            exit($str);
        }
    }
    
    /**
     * 获取模块配置.
     * 
     * @param string $key 配置参数.
     * @param string $np  模块配置的命名空间.
     * 
     * @return mixed
     */
    public function getBiz($key, $np = self::CTR_MODULE_MALL) {
        $cnfClass = DEBUG_MODE ? 'BizDebug' : 'Biz';
        $biz = \Core\Lib\Sys::getAppCfg($np, $cnfClass);
        return $biz->{$key};
    }
	
	 /**
     * 获取模块配置.
     * 
     * @param string $key 配置参数.
     * 
     * @return mixed
     */
    public function getConfig($key) {
        return \Core\Config\Config::getIns()->{$key};
    }
    
    /**
     * 提醒并跳转.
     * @param string $msg  提示信息.
     * @param string $url  跳转目标地址,为空则默认跳转到来源地址.
     * @param string $type 类型.
     * @param string $np   模块命名空间.
     */
    public function showMsg($msg, $url = '', $type = self::CTR_NOTICE_MESSAGE, $np = self::CTR_MODULE_MALL) {
        $url = empty($url) ? !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : SITE_URL : $url;
        $res = array(
            'msg' => $msg,
            'url' => $url,
            'type' => $type
        );
        exit(\Core\Lib\Smarty::instance()->fetch('error/index.tpl', $res, $np));
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
	
	

}
