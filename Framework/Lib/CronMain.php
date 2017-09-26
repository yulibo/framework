<?php

namespace Core\Lib;

class CronMain {

    protected static $instance;
    protected $pathMap = array();

    /**
     *
     * @return self
     */
    public static function instance() {
        if (!static::$instance) {
            static::$instance = new static;
        }
        return static::$instance;
    }
    
	public function run($data) {
        header("Content-Type: text/html; charset=utf-8");
		date_default_timezone_set('Asia/Chongqing');
        define('SYS_LOG','/global/womall/logs' . DIRECTORY_SEPARATOR);
        define('MONITOR_LOG', SYS_LOG . 'monitor' . DIRECTORY_SEPARATOR);
        array_shift($data);
        $route = empty($data) ? 0 : $data;
        $fun = $route[1];
        unset($route[1]);
        $class = implode('\\', array('Crontab',$route[0]));
        //模组，控制器，ACTION行相关常亮为初始化;
        defined('MOUDLE_NAME') or define('MOUDLE_NAME', 'Crontab'); //当前模组名
        defined('MODULE_ASSET') or define('MODULE_ASSET', SITE_URL . '/' . MOUDLE_NAME . "/Template/asset"); //当前模组静态资源地址
        defined('CONTROLLER_NAME') or define('CONTROLLER_NAME', $route[0]); //当前控制器名
        defined('ACTION_NAME') or define('ACTION_NAME', $fun); //当前ACTION
		
        try {
            unset($route[0],$route[1]);
            $_REQUEST = array_values($route);
            $class::instance()->$fun();
        } catch (\Exception $e) {
            \Core\Lib\MNLogger\EXLogger::instance()->log($e);
        }
    }


}
