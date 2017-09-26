<?php
namespace Module\FreeStream\Extension;

use Module\FreeStream\FreeStream as FreeStream;
use \Exception as Exception;

// 首登 4G 网络奖励
class LoginFourG extends FreeStream
{
    const BORAD_ID = 2; // 首登 4G 网络奖励
	private $loginStream;//登录流量包信息


 
    // 领取流量前的判断
    protected function orderStreamCheck($data)
    {
		$row = $this->getFreeStream()->getOne(array('id'=>$data['fId']));
		if(empty($row)){
			throw new Exception('流量包不存在');
		}
		return true;
    }
	
	//获取未开始
	protected function getNotstart($row){
		$stock_number = intval($row['stock_number']);//存量月份
		$stime = $this->getStime($stock_number);//领取开始时间
        $time = time(); // 当前时间
        if ($time < $stime) {
            return true;
        }else{
			return false;
		}
	}
    
	//获取已经结束
	protected function getOver($row){
		$stock_number = intval($row['stock_number']);//存量月份
		$month = intval($row['month']);//几个月领取
		$stime = $this->getStime($stock_number);//领取开始时间
		$etime = strtotime("+$month months",$stime);//领取结束时间
        $time = time(); // 当前时间
        if ($time > $etime) {
            return true;
        }else{
			return false;
		}
	}

    //获取存量包的开始时间
    private function getStime($stock_number){
        return  strtotime("+$stock_number months",strtotime($this->loginStream['login_time']));//领取开始时间;
    }
	
    // 检查用户权限
    protected function getPer()
    {
		$this->checkUser();
        if (! $this->loginStream) {
            return false;
        } else {
            return true;
        }
    }
    
    // 检查用户权限
    private function checkUser()
    {
        $row = $this->getStreamPhone()->getMaxRow(array(
            'phone' => $this->userInfo['user_phone'],'login_time >'=>0
        ));
        if (empty($row)) {
            return false;
        }
        return $this->loginStream = $row;
    }
    
    // 获取号码是否有首登4G 权限
    private function getStreamPhone()
    {
        return \Model\FreeStream\StreamPhone::instance();
    }
	
	// 获取流量包Model
    private function getFreeStream()
    {
        return \Model\FreeStream\FreeStream::instance();
    }
}
?>