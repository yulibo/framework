<?php
namespace Module\Lottery\PrizeType;

use \Module\Lottery\LotteryCode;
use \Module\Lottery\LotteryData;
use \Exception;

//奖品
abstract class Prize
{
	
	protected $prizeId;//奖品ID
	
	protected $userId;//用户id
	protected $userPhone;//用户电话号码
	
	public function __construct(){
		$this->userId=$_SESSION['user_info']['user_id'];
	    $this->userPhone=$_SESSION['user_info']['user_phone'];
	}
	
	//检查是否还有奖品
	public function addPrize($id){
		$this->prizeId = $id;
		$this->grantPrize();//发放奖品
		$this->addRecivePrizeLog();//添加奖品领取日志
	}
	
	
	//添加发放奖品
	protected function grantPrize(){
		
	}
	
	//添加奖品领取日志
	private function addRecivePrizeLog(){
		//判断是否有领取信息
		if(!$this->getRecivePrizeInfo()){
			return false;
		}
		$result = [];
		$result['user_id'] = $this->userId;
		$result['user_phone'] = $this->userPhone;
		$result['prize_id'] = $this->prizeId;
		$result = array_merge($result,$this->getRecivePrizeInfo());
		return $this->getLotteryDataModule()->addRecivePrizeLog($result);
	}
	
	//获取奖品
	protected function getPrizeItemRow(){
		return $this->getLotteryDataModule()->getPrizeItemRow(['id'=>$this->prizeId]);
	}
	
	//获取奖品领取信息
	protected function getRecivePrizeInfo(){
		return [];
	}
	
	
	//获取lottery_item model
	protected function getLotteryDataModule(){
		return LotteryData::getIns();
	}
	
	
}
?>