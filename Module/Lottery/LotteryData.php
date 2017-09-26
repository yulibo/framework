<?php
namespace Module\Lottery;

use \Module\Lottery\PrizeType;

use \Exception as Exception;

class LotteryData
{
	
	private function __construct(){
		
	}
	
	//获取对象本身
	public static function getIns(){
		return new self();
	}
	
	//获取单个奖品
	public function getPrizeItemRow($prizeId){
		$row = $this->getLotteryItemModel()->getRow(['id'=>$prizeId]);//单个奖品
		if(empty($row)){
			throw new Exception('奖品不存在',LotteryCode::PRIZE_NOT);
		}
		return $row;
	}
	
	
	//获取奖品列表
	public function proList(){
		$list = $this->getLotteryItemModel()->proList();//奖品列表
		if(empty($list)){
			throw new Exception('没有奖品');
		}
		$proArr= [];
		$time = strtotime(date('Y-m-d',time())); //当天日期
		foreach($list as $key=>$val){
			if($val['o_date'] && $time!=strtotime($val['o_date'])){
				continue;
			}
			if($val['probability']=='0.0000'){
				continue;
			}
			if(!empty($val['probability']) && ($val['surplus']==-1 || $val['surplus']>=1)){
				$proArr[] = $val;
			}
		}
		//没有奖品 提示未中奖
		if(!empty($list) && empty($proArr)){
			throw new Exception('没有奖品');
		}
		return $proArr;
	}
	
	//获取奖品总数
	public function getPrizeCount($where){
		return $this->getPrizeLogModel()->getCount($where);
	}
	
	//获取用户抽奖次数
	public function getUserLottery($userId){
		$row = $this->getLotteryModel()->getRow(['user_id'=>$userId]);
		return intval($row['num']);
	}
	

	
	//添加中奖纪录
	public function addPrize($userId,$prizeId){
		return $this->getLotteryModel()->addPrize($userId,$prizeId);
	}
	
	
	//添加奖品领取日志
	public function addRecivePrizeLog($data){
		return $this->getLotteryModel()->addRecivePrizeLog($data);
	}
	
	
	//获取lottery model
	private function getLotteryModel(){
		return \Model\SinglesDay\Lottery::instance();
	}
	
	//获取lottery_item model
	private function getLotteryItemModel(){
		return \Model\SinglesDay\LotteryItem::instance();
	}
	
	//获取prize_log model
	private function getPrizeLogModel(){
		return \Model\SinglesDay\PrizeLog::instance();
	}
}
?>