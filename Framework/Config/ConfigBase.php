<?php
namespace Core\Config;

class ConfigBase{
    public $token = false ;
    public $tokenName = '__TOKEN__';

    public function __construct(){
        if(defined("TOKEN_ON")){$this->token = TOKEN_ON;}        
    }

    public function __get($name)
    {
        return isset(self::$name) ? self::$name : null;
    }
    /**
     * 生成token
     */
    public function getToken(){
        $tokenName = $this->tokenName;
        if (! isset($_SESSION[$tokenName])) {
            $_SESSION[$tokenName] = array();
        }
        // 标识当前页面唯一性
        $tokenKey = md5($_SERVER['REQUEST_URI']);
        if (isset($_SESSION[$tokenName][$tokenKey])) { // 相同页面不重复生成session
            $tokenValue = $_SESSION[$tokenName][$tokenKey];
        } else {
            $tokenValue = md5(microtime(true));
            $_SESSION[$tokenName][$tokenKey] = $tokenValue;
            //ajax处理
            if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest"){
                header($tokenName . ': ' . $tokenKey . '_' . $tokenValue);
            }
        }
        return array(
            $tokenName,
            $tokenKey,
            $tokenValue
        );
    }
    /**
     * 验证token
     */
    public function checkToken()
    {
        if (strtoupper($_SERVER['REQUEST_METHOD']) == 'POST'){
            if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest"){
				$ajaxIgnore = array();
                //获取相关模块的配置文件
                if(strtolower(MOUDLE_NAME) == 'mall'){
                    $ajaxIgnore = \Mall\Config\Biz::$ajaxIgnore;
                }else if(strtolower(MOUDLE_NAME) == 'store'){
                    $ajaxIgnore = \Store\Config\Biz::$ajaxIgnore;
                }else if(strtolower(MOUDLE_NAME) == 'admin'){
                    $ajaxIgnore = \Admin\Config\Biz::$ajaxIgnore;
                }
                if(in_array(CONTROLLER_NAME.'\\'.ACTION_NAME, $ajaxIgnore)){
                    return true;
                }
            }
            if(empty($_POST[$this->tokenName])){
                return false;
            }
            // 令牌验证
            list ($key, $value) = explode('_',$_POST[$this->tokenName]);
            $name = $this->tokenName;
            if (isset($_SESSION[$name][$key]) && $value && $_SESSION[$name][$key] === $value) { // 防止重复提交
                unset($_POST[$this->tokenName]);
                unset($_SESSION[$name][$key]); // 验证完成销毁session
                return true;
            }
            return false;
        }
        return true;
    }
}