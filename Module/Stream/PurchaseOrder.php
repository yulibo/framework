<?php

namespace Module\Stream;

use \Exception;
use Module\Stream\StreamOrder;

class PurchaseOrder  extends StreamOrder {

	const R_TYPE = 15; //国庆活动类型ID
	
	protected $ctime;//订购时间
	protected $priId;//预约ID
	protected $msg='订购成功';//订购消息
	
    //初始化
    protected function init() {
		
    }
	
	 //订购流量
    public function orderStream($streamCode) {
        try {
			$this->getStreamApi()->setErrorNull();
			parent::orderStream($streamCode);
        } catch (Exception $e) {
           $this->msg =  $e->getMessage();
		   $this->sendMessageError();//失败发送短信
        }
		$this->getNationalPurchaseLogModel()->update(array('send_msg'=>$this->msg),array('id'=>$this->priId));//修改状态
    }
	
	//失败发送短信
	private function sendMessageError(){
		return false;
		//暂时不发短信了
		$str='【四川联通】预约失败通知:您预约申请的国庆节日包因产品冲突原因未能订购成功，给您造成的不便敬请谅解！10月1日起您可尝试再次自行订购！http://169ol.com/gq';
		\Module\PhoneCode::instance()->sendMessage($this->userPhone,$str);//发送短信
	}
	
	//设置主键
	public function setPriId($id){
		 $this->priId = $id;
		 return $this;
	}
	
	//设置用户电话
	public function setPhone($phone){
		 $this->userPhone = $phone;
		 return $this;
	}
	
	//设置订购时间
	public function setCtime($time){
		 $this->ctime = $time;
		 return $this;
	}
	
	//订购流量额外的参数
	protected function transactFlowParmas(){
		return array('ctime'=>$this->ctime);
	}

	//获取流量包名称
    protected function getPackageName() {
        $row = $this->getStreamRow(); //当前订购流量包
        return $row['name'];
    }
	
	//获取单行流量
	private function getStreamRow(){
		return $this->getNationalDayDataModule()->getStreamRow(array('stream_code'=>$this->streamCode));
	}
	
	
	//获取国庆数据层
	private function getNationalDayDataModule(){
		return \Module\NationalDay\NationalDayData::getIns();
	}
	
	
	//成功发送短信
	protected function orderSuccessOpt(){
		\Module\PhoneCode::instance()->sendMessage($this->userPhone,$this->getMessage());//发送短信
	}
	
	//获取预约model
	private function getNationalPurchaseLogModel(){
		return \Model\NationalDay\NationalPurchaseLog::instance();
	}

	//获取短信内容
	private function getMessage(){
		return "【四川联通】您预定的国庆大促15元15GB省内流量节日包已经订购成功，该产品将在10月1日00:00:00生效，10月7日23:59:59失效。订购后不可退订，剩余流量不参与结转。流量大，当然大不同！ http://169ol.com/gq";
	}
	
}

?>