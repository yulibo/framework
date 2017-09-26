<?php

namespace Core\Lib;



/**
 * 内置的Dispatcher类
 * 完成URL解析、路由和调度
 */

class Dispatcher {

    protected static $instance;
    public $route;

    /**
     *
     * @return self
     */
    public static function instance()
    {
        if(!static::$instance)
        {
            static::$instance = new static;
        }
        return static::$instance;
    }

    /**
     * URL映射到控制器
     * @access public
     * @return void
     */
    public function dispatch($uri = null) {
        if(is_string($uri)){
            $uri = strpos($uri,'/') === 0 ? substr($uri,1):$uri;
            if(substr($uri, -1) === '/'){
                $uri = substr($uri,0,strlen($uri) - 1);
            }
            $route_arr = explode('/', $uri);
	        switch (count($route_arr)) {
                case 0:
                    $this->route = ['Mall','Home','index'];
                    break;
                case 1:
                    # code...
                    break;
                case 2:
                    $this->route = array_slice($route_arr,0,2);
                    $this->route[3] = 'index';
                    break;
                default:
                    $this->route = array_slice($route_arr,0,3);
                    break;
            }
    
        }
        if(isset($this->route[0])){
            $this->route[0] = ucfirst($this->route[0]);
        }
        
        if(isset($this->route[1])){
            $this->route[1] = ucfirst($this->route[1]);
        }

        return $this;
    }

}
