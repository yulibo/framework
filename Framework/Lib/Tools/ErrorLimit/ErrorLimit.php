<?php


namespace Core\Lib\Tools\ErrorLimit;

abstract class ErrorLimit {
	
	protected $cacheKey = ''; //key
    protected $limit; //错误的次数
	protected $lockTime; //锁定时间  秒

	
    //检查是否已经限制
    public function checkLimit() {
        $this->setCacheKey();
        if ($this->getErrorNum() >= $this->limit) {
            throw new \Exception($this->errorShow());
        }else{
			return true;
		}
    }

    //获取错误数量
    protected function getErrorNum() {
        return $this->getStore()->get($this->cacheKey);
    }

    //获取错误数量
    public function setErrorNum($inc = 1) {
        $this->getStore()->set($this->cacheKey, $this->getErrorNum() + $inc, $this->lockTime);
    }

	//设置用户访问页的KEY
    abstract protected function setCacheKey();
	
	
	//获取存储方式
	abstract protected function getStore();
	
	
	//错误显示
    abstract protected function errorShow();

}
