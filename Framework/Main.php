<?php

/**
 * 引导类.
 *
 * @author ylb
 */

namespace Core;

class Main {

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
    
	
	
    public function init($map) {
        date_default_timezone_set('Asia/Chongqing');
        define('SYS_LOG','/global/womall/logs' . DIRECTORY_SEPARATOR);
        define('MONITOR_LOG', SYS_LOG . 'monitor' . DIRECTORY_SEPARATOR);

        //加载系统初始化设置公共设置
        if (file_exists(SYS_ROOT . '/config.ini.php')) {
            require_once SYS_ROOT . '/config.ini.php';
        }
        $this->pathMap = $map;
        return $this;
    }

    public function run() {
        header("Content-Type: text/html; charset=utf-8");
        $uri = empty($_REQUEST['_rp_']) ? 0 : $_REQUEST['_rp_'];
	
        if (empty($this->pathMap[$uri])) {
            // 当入口路由表中不存在对应值时
            // 调用Dispatcher自动定向已有模块、控制器、及方法
            $dispatcher = Lib\Dispatcher::instance()->dispatch($uri);
            $route = $dispatcher->route;
            if (empty($route)) {
                if (!DEBUG_MODE) {
                    header("Location: /404.html");
                    exit;
                }
            }
        } else {
            $route = explode('/', $this->pathMap[$uri]);
        }
        unset($_REQUEST['_rp_']);
        
        if (PHP_SAPI != 'cli') {
        	session_start();
        }
        if (empty($_SESSION)) {
        	// 访问登陆验证.
        	if ($route[0] == 'Store') {
        		$route = array('Store', 'Admin', 'loginSeller');
        	} elseif ($route[0] == 'Admin') {
        		//$route = array('Admin', 'Home', 'login');
        	}
        }
        $fun = array_pop($route);
        $class = implode('\\', $route);
        
        //模组，控制器，ACTION行相关常亮为初始化;
        defined('MOUDLE_NAME') or define('MOUDLE_NAME', $route[0]); //当前模组名
        defined('MODULE_ASSET') or define('MODULE_ASSET', SITE_URL . '/' . MOUDLE_NAME . "/Template/asset"); //当前模组静态资源地址
        defined('CONTROLLER_NAME') or define('CONTROLLER_NAME', $route[1]); //当前控制器名
        defined('ACTION_NAME') or define('ACTION_NAME', $fun); //当前ACTION
        if (!DEBUG_MODE) {
            if (!is_dir(SYS_ROOT . MOUDLE_NAME)) {
                header("Location: /404.html");
                exit;
            }
            if (!is_file(SYS_ROOT . MOUDLE_NAME . DS . CONTROLLER_NAME . '.php')) {
                header("Location: /404.html");
                exit;
            }
            if (!method_exists($class, $fun)) {
            	header("Location: /404.html");
            	exit;
            }
        }
        //载入模组级别公用配置及公用函数
        $moudleConfDir = SYS_ROOT . MOUDLE_NAME . DS . 'Config';
        if (is_dir($moudleConfDir)) {
            $fileConf = $moudleConfDir . '/Biz.php'; //模组配置
            $debugFileConfig = $moudleConfDir . '/Biz.debug.php'; //调试配置,调试配置会覆盖正式配置,不能提交生产环境
            if (is_file($fileConf)) {
                require_once $fileConf;
            }
            if (is_file($debugFileConfig)) {
                require_once $debugFileConfig;
            }
        }
        try {
            if (!get_magic_quotes_gpc()) {
                \Module\Common::instance()->addslashesextended($_POST);
                \Module\Common::instance()->addslashesextended($_GET);
                \Module\Common::instance()->addslashesextended($_REQUEST);
                \Module\Common::instance()->addslashesextended($_COOKIE);
            }
            $dao = new \Core\Config\ConfigBase();

            if($dao->token){
                if(!$dao->checkToken()){
                    $dao = new \Core\Lib\ControllerBase();
                    $dao->showMsg('表单令牌过期','',$dao::CTR_SHOW_MESSAGE,$dao::CTR_MODULE_MALL);
                }
            }
			$class::instance()->$fun();
        } catch (\Exception $e) {
            \Core\Lib\MNLogger\EXLogger::instance()->log($e);
            if (!DEBUG_MODE) {
                header("Location: /500.php");
            }
        }
    }

}
