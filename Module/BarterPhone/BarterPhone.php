<?php
namespace Module\BarterPhone;
use \Exception as Exception;

abstract class BarterPhone
{
    public $err; // 错误信息
	public $userInfo;//用户信息
	
	protected $permissions;//用户是否查看权限
	
    public function __construct(){
        $this->init();
    }
    
    // 初始化
    protected function init(){
       $this->getUserInfo();
    }
	
	//获取权限
	private function getPermiss(){
        return $this->permissions = $this->getPer(); // 获取用户权限
	}
    
    // 获取用户信息
    private function getUserInfo(){
        return $this->userInfo = $_SESSION['user_info'];
    }
    
    // 检查用户权限
    abstract protected function getPer();
    
	
	// 获取数据列表
    abstract protected function getDataList();
	
	
	//获取数据结果
	public function getDataResult(){
		if(!$this->getPer()){
			return false;
		}
		return $this->getDataList();
	}
    
}

?>