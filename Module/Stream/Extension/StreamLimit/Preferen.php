<?php

namespace Module\Stream\Extension\StreamLimit;

use Module\Stream\StreamOrder;
use Module\Stream\DragonBoatStatus;
use \Exception;

//优惠流量订购
class Preferen extends StreamOrder {

    const R_TYPE = 30; //活动类型
   

    //获取流量包名称
    protected function getPackageName() {
        $row = $this->getRow(); //当前订购流量包
        return $row['name'];
    }
	

    //获取行
    private function getRow() {
        return   $this->getStreamModel()->getOne(array('stream_code' => $this->streamCode));
    }

    //成功发送短信
	protected function orderSuccessOpt(){
		if($msg = $this->getMessage()){
			return \Module\PhoneCode::instance()->sendMessage($this->userPhone,$msg);
		}
	}
	
	//获取短信内容
	private function getMessage(){
		$row = $this->getSinglesStreamModel()->getRow(['stream_code'=> $this->streamCode]);
		return $row['phone_msg'];
	}
	
	
	//获取model stream 
    private function getSinglesStreamModel() {
        return \Model\SinglesDay\Stream::instance();
    }

	
  
    //获取model stream 
    private function getStreamModel() {
        return \Model\StreamProduct::instance();
    }

}

?>