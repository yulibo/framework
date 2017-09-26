<?php
namespace Module\Stream;

use \Exception;

class DragonBoatStatus 
{
	private $streamCode;//流量包
	
	private static $activityTime = array('stime'=>'2016-09-11','etime'=>'2016-09-17 23:59:59'); //中秋时节范围

	const ORDER=0;//可订购
	const NOT_START=-1;//未开始
	const END_AC=1;//已经结束
	const NOGOODS = 2;//已抢光 自己没有订购
	const ORDERED = 3;//已订购 未抢光
	const ORDERED_NOGOODS = 4;//已抢光 自己有订购
	
	private static $obj; //当前对象
	private function __construct(){
		
	}
	
	//获取当前对象
	public static function getIns(){
		if(!empty(self::$obj)){
			return self::$obj;
		}
		return self::$obj = new self();
	}
	
	//获取状态

	public function getStatus($streamCode){
		if(empty($streamCode)){
			throw new Exception('流量编号不能为空');
		}
		$this->streamCode = $streamCode;
		if(self::getNotStartStatus()){
			return self::NOT_START;
		}elseif(self::getEndStatus()){
			return self::END_AC;
		}elseif(!$this->getOrderedStatus() && !empty($this->getNoGoodsStatus())){
			return self::NOGOODS;
		}elseif($this->getOrderedStatus() && empty($this->getNoGoodsStatus())){
			return self::ORDERED;
		}elseif($this->getOrderedStatus() && $this->getNoGoodsStatus()){
			return self::ORDERED_NOGOODS;
		}
		return self::ORDER;
	}
	
	//获取到结束时间的天数
	public static function getEndDayNum(){
		$e = strtotime(self::$activityTime['etime'])-time();
		return floor($e/86400);
	}
	
	
	
	//未开始
	public static function getNotStartStatus(){
		if(time()<strtotime(self::$activityTime['stime'])){
			return true;
		}
	}
	
	//结束活动状态
	public static function getEndStatus(){
		if(time()>strtotime(self::$activityTime['etime'])){
			return true;
		}
	}
	
	//无货-已抢光
	private function getNoGoodsStatus(){
		try{
			$this->getDragonBoat()->checkStreamAllNum();//获取是否已经抢完
			return false;
		}catch(Exception $e){
			return true;
		}
	}
	
	//已订购
	private function getOrderedStatus(){
		try{
			$this->getDragonBoat()->checkStreamAcDayNum();//获取当天是否订购
			return false;
		}catch(Exception $e){
			return true;
		}
	}
	
	private static $dragonBoatLimit;//
	
	//获取中秋
	private function getDragonBoat(){
		if(isset(self::$dragonBoatLimit[$this->streamCode])){
			return self::$dragonBoatLimit[$this->streamCode];
		}
		$obj = new \Module\Stream\Extension\StreamLimit\DragonBoat();
		$obj->setStreamCode($this->streamCode);
		return self::$dragonBoatLimit[$this->streamCode] = $obj;
	}
}

?>
