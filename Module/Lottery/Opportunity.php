<?php
namespace Module\Lottery;

use \Exception ;
//用赠送的抽奖机会抽奖
class Opportunity extends Lottery
{
	
	//检查抽奖机会
	protected function checkLuckyDraw(){
		if($this->getUserLottery()<=0){
			throw new Exception('没有抽奖机会',LotteryCode::NOT_LOTTERY_OP);
		}
	}
	

	
	//获取用户的抽奖机会
	public function getUserLottery(){
		return $this->getLotteryDataModule()->getUserLottery($this->userId);
	}

	
}
?>