<?php

namespace Module\NationalDay;

use \Exception;
use Module\Stream\StreamOrder;

class NationalDayOrder  extends StreamOrder {

	const R_TYPE = 15; //国庆活动类型ID
	const LOTTERY_TYPE = 5; //国庆活动购流量送抽奖次数type
	const VIDEO_CODE = 1109;//腾讯视频CODE
	const NATION_CODE = 1190;//

	//订购流量
    public function orderStream($streamCode) {
		$this->streamCode = $streamCode;
		$status = $this->getStatus($streamCode);
		$row = $this->getStreamRow(); //当前订购流量包
		if($status==NationalDayStatus::PURCHASE){ //判断是否是预约
			$this->addNationalPurchase($streamCode);
		}
        try {
			$this->getOrderStreamEx($streamCode,$status);//流量包状态检查
            parent::orderStream($streamCode);
        } catch (Exception $e) {
            if ($e->getCode() != -1) {
                throw new Exception($e->getMessage(), $e->getCode());
            }
            $streamCodeList = $this->getCombinStreamCode();
            $this->repetOrder($e->getCode(), $streamCodeList);
        }
    }
	
	//添加预约
	private function addNationalPurchase($streamCode){
		$msg =  $this->getNationalPurchaseLogModel()->addNationalPurchase($this->userPhone,$streamCode);
		if($msg['msg']=='error'){
			throw new Exception('添加预约机会失败');
		}
		$row = $this->getStreamRow(); //当前订购流量包
		if(empty($row['pur_message'])){
			return true;
		}
		$msg = sprintf($row['pur_message'],$row['name']);
		\Module\PhoneCode::instance()->sendMessage($this->userPhone,$msg);//预约发送短信
		return true;
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
		return NationalDayData::getIns();
	}
	
	//成功发送短信
	protected function orderSuccessOpt(){
		\Module\PhoneCode::instance()->sendMessage($this->userPhone,$this->getMessage());//发送短信
		//如果当前code 是腾讯视频
		if($this->streamCode==self::VIDEO_CODE){
			$this->sendVideoMessage();//腾讯视频短信
		}
		$this->giveLottery();//送抽奖机会
	}
	
	
	//发送腾讯视频短信
	private function sendVideoMessage(){
		$msg = "【活动提示】尊敬的用户，参与视频风暴腾讯季活动用户在订购成功后，需下载最新版腾讯视频客户端进行激活，否则无法获得1G省内流量包月及腾讯视频会员。详细步骤请点击http://169ol.com/gq【沃4G+视频风暴】";
		\Module\PhoneCode::instance()->sendMessage($this->userPhone,$msg);//发送短信
	}
	
	//送抽奖机会
	private function giveLottery(){
		$row = $this->getStreamRow(); //当前订购流量包
		if($row['give_lottery']){
			$userId = $_SESSION['user_info']['user_id']; //用户电话
			\Module\FreeLottery::addFreeLottery($userId,self::LOTTERY_TYPE);
		}
	}
	
	
	//获取短信内容
	private function getMessage(){
		$row = $this->getStreamRow(); //当前订购流量包
		$time = "2016-09-30 12:00:00";
		if($this->streamCode==self::NATION_CODE && time()<=strtotime($time)){
			return "【四川联通】您订购的国庆大促15元15GB省内流量节日包已经订购成功，该产品将在10月1日00:00:00生效，10月7日23:59:59失效。订购后不可退订，剩余流量不参与结转。流量大，当然大不同！ http://169ol.com/gq";
		}else{
			return sprintf($row['order_message'],$row['name']);
		}
	}
	
	//获取流量包状态
	private function getStatus($streamCode){
		return $this->getNationalDayStatusModule()->getStatus($streamCode);
	}
	
	//获取订购流量包异常
	private function getOrderStreamEx($streamCode,$code){
		$statusModule =$this->getNationalDayStatusModule();
		if(isset($statusModule::$orderListMessage[$code])){
			throw new Exception($statusModule::$orderListMessage[$code]);
		}
	}
	
	//获取流量包状态
	private function getNationalDayStatusModule(){
		return NationalDayStatus::getIns();
	}

	//获取预约model
	private function getNationalPurchaseLogModel(){
		return \Model\NationalDay\NationalPurchaseLog::instance();
	}
	
}

?>