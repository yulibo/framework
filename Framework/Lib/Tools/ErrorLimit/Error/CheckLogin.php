<?php


namespace Core\Lib\Tools\ErrorLimit;

class CheckLogin extends Core\Lib\Tools\ErrorLimit\Error {
	
	const ERROR_IP_PFEX ='error_ip'; //错误IP前缀

    protected $limit = 5; //错误的次数
	protected $lockTime = 3600; //锁定时间  秒
	

	//设置用户访问页的KEY
    protected function setCacheKey() {
        $this->cacheKey = self::ERROR_IP_PFEX.$this->getIp(); //请求地址
    }
	
	//获取存储方式
	protected function getStore(){
		return \Lib\ControllerBase::instance()->redis();
	}
	
	//获取IP
	private function getIp(){
		return \Module\Common::instance()->getIP();
	}
}
