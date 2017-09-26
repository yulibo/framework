<?php
namespace Module\Lottery\PrizeType;

use \Module\Services\WobeiProgress as WobeiProgress;
use \Exception;
//沃贝
class Wobei extends Prize 
{
	const TYPE_ID=4;//沃贝奖品类型
	
	protected $status=1;//奖品领取状态
	protected $desc = '赠送沃贝成功';//描述信息
	
	//发放奖品
	public function grantPrize(){
		$row = $this->getPrizeItemRow();//获取奖品
		$rs = WobeiProgress::addWobei($this->userId,$this->userPhone,$row['data_value'],"双十一大抽奖送沃贝");
		if($rs){
			return true;
		}
		$this->status = 0;
		$this->desc = '赠送沃贝失败';
		return false;
	}
	
	protected function getRecivePrizeInfo(){
		return ['status'=>$this->status,'desc'=>$this->desc];
	}
	
	//获取赠送沃贝model
	private function getLotteryModel(){
		return \Model\Lottery::instance();
	}
	
	

}
?>