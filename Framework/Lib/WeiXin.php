<?php

namespace Core\Lib;

class WeiXin {
	protected static $instance;//

	/**
     *
     * @return self
     */
    public static function instance($appid,$appsecret) {
        if (!static::$instance) {
			 /* 导入WeiXin核心类   */
			require_once(FRAMEWORK_ROOT . 'Lib/WeiXin/JSSDK.php');
            static::$instance = new \JSSDK($appid,$appsecret);
        }
        return static::$instance;
    }

}

?>