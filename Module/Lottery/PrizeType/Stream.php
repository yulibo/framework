<?php
namespace Module\Lottery\PrizeType;

use \Exception;
//流量
class Stream extends Prize 
{
	const TYPE_ID=3;//流量奖品类型
	
	const SDAY_TYPE=15; //双十一抽奖赠送流量包
	
	//获取奖品
	public function grantPrize(){
		$this->sendCoupon();//添加优惠券
	}
	
	//添加优惠券
	private function sendCoupon(){
		$row  =  $this->getPrizeItemRow();//获取流量产品
		$data = [
			"user_id"=>$this->userId,
			"bid"=>0,
			"price_id"=>0,
			"pid"=>0,
			"present_price"=>0,
			"original_price"=>0,
			"initial_price"=>0,
			"target_price"=>0,
			"amount"=>1,
			"phone"=>$this->userPhone,
			"perferen_price"=>0,
			"promo_code"=>$row['data_value'],
			"status"=>0,
			"type"=>self::SDAY_TYPE,
			"create_time"=>time(),
			"effective_time" => $row['effective_time'],
			"start_time"=>time(),
			"outer_url"=>'',
			"image_id"=>0,
			"download_url"=>$row['image'],
			"title"=>$row['name'],
			"info" =>$row['info']
		];
		return $this->getLotteryModel()->sendCoupon($data);
	}
	
	

	//获取添加优惠model
	private function getLotteryModel(){
		return \Model\Lottery::instance();
	}
	
	//获取流量model
	private function getStreamModel(){
		return \Model\StreamProduct::instance();
	}
	
}
?>