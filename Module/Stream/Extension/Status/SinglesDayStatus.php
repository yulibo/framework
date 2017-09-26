<?php
namespace Module\Stream\Extension\Status;

use \Exception;

class SinglesDayStatus 
{	
	private static $activityTime = array('stime'=>'2016-10-1','etime'=>'2016-11-14 23:59:59'); //双十一时间范围

	const ORDER=0;//可订购
	const NOT_START=-1;//未开始
	
	const END_AC=1;//已经结束
	const ORDERED=2;//已订购
	private static $obj; //当前对象
	private function __construct(){
		
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
		}elseif($this->getOrderedStatus()){
			return self::ORDERED;
		}
		return self::ORDER;
	}
	
	//获取当前对象
	public static function getIns(){
		if(!empty(self::$obj)){
			return self::$obj;
		}
		return self::$obj = new self();
	}
	
	
	//获取到结束时间的天数
	public static function getEndDayNum(){
		$e = strtotime(self::$activityTime['etime'])-time();
		return floor($e/86400);
	}
	
	//已订购
	private function getOrderedStatus(){
		try{
			$this->getSinglesDayLimit()->checkStreamAcDayNum();//获取当天是否订购
			return false;
		}catch(Exception $e){
			return true;
		}
	}
	
	//获取双十一
	private function getSinglesDayLimit(){
		$obj = new \Module\Stream\Extension\StreamLimit\SinglesDay();
		$obj->setStreamCode($this->streamCode);
		return  $obj;
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
}

?>
