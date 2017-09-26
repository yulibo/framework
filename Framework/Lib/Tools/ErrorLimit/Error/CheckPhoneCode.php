<?php


namespace Core\Lib\Tools\ErrorLimit;

class CheckPhoneCode extends Core\Lib\Tools\ErrorLimit\Error {
	
	//const ERROR_IP_PFEX ='error_ip'; //错误IP前缀

    protected $limit = 5; //错误的次数
	protected $lockTime = 3600; //锁定时间  秒
	

	//设置用户访问页的KEY
    protected function setCacheKey($phone,$url) {
        $this->cacheKey = md5($phone . '_' . $url); //请求地址
    }
	
	//获取存储方式
	protected function getStore(){
		return \Lib\ControllerBase::instance()->redis();
	}

	//错误显示
    protected function errorShow(){
    	//发送短信
    }
}
