<?php
namespace Module\NationalDay;

use \Exception;

class NationalDayStatus 
{
	private $streamCode;//流量包
	
	private static $activityTime = array('stime'=>'2016-09-27','etime'=>'2016-10-07 23:59:59'); //国庆活动时间

	const NOT_START=1;//未开始
	const PURCHASE=2;//立即预约
	const PURCHASED = 3;//已预约
	const ORDER = 4;//立即订购
	const ORDERED = 5;//已订购
	const STOP = 6;//已结束
	const FINISH_Y = 7;//已经抢完  预约
	const FINISH = 8;//已经抢完  订购
	
	public static $orderListMessage = [self::NOT_START=>'活动未开始',self::FINISH_Y=>'对不起,已抢完',self::FINISH=>'对不起,已抢完',self::PURCHASED=>'尊敬的用户，您已经成功预约国庆大促15元15GB省内流量节日包，请勿重复预约。',self::ORDERED=>'您好，您已订购',self::STOP=>'对不起，已结束'];//提示状态
	
	private static $obj; //当前对象
	
	private $phone;//电话号码
	
	private function __construct(){
		if(isset( $_SESSION['user_info']['user_phone'])){
			$this->phone = $_SESSION['user_info']['user_phone']; //用户电话
		}
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
		if($this->getNotStartStatus()){ 
			return self::NOT_START;//未开始
		}elseif($this->getEndStatus()){
			return self::STOP;//已经结束
		}elseif($this->getPurchaseFinish()){
			return self::FINISH_Y;//已经抢完  预约
		}elseif($this->getPurchaseStatus()){
			return self::PURCHASE;//立即预约
		}elseif($this->getPurchasedStatus()){
			return self::PURCHASED;//已预约
		}elseif($this->getOrderFinish()){
			return self::FINISH; //已经抢完  订购
		}elseif($this->getOrderStatus()){
			return self::ORDER; //立即订购
		}elseif($this->getOrderedStatus()){
			return self::ORDERED; //已经订购
		}
		return self::STOP;//已经结束
	}
	
	//获取到结束时间的天数
	public static function getEndDayNum(){
		$e = strtotime(self::$activityTime['etime'])-time();
		return floor($e/86400);
	}
	
	
	//未开始
	public  function getNotStartStatus(){
		$time = time();
		if($time<strtotime(self::$activityTime['stime'])){
			return true;
		}
		$row = $this->getStreamRow();
		$minTime = ($row['p_start']>$row['o_start'] ) ? $row['o_start']: !empty($row['p_start'])?$row['p_start']:$row['o_start'];
		//当前时间小于最小开始时间 返回未开始
		if($time<$minTime){
			return true;
		}
		//当前时间大约预约结束时间 同时小于订购开始时间
		if($time>$row['p_end'] && $time<$row['o_start']){
			return true;
		}
		//不限制订购小时 未开始 返回false
		if($row['limit_time']==-1){
			return false;
		}
		$maxTime = ($row['p_end']>$row['o_end']) ? $row['p_end']: $row['o_end'];
		//当前小时 小于 限制订购小时 返回 未开始
		if(date('H',time())<$row['limit_time'] && $time<$maxTime){
			return true;
		}
	}
	
	//结束活动状态
	public  function getEndStatus(){
		$time = time();
		if($time>strtotime(self::$activityTime['etime'])){
			return true;
		}
		$row = $this->getStreamRow();
		$maxTime = ($row['p_end']>$row['o_end']) ? $row['p_end']: $row['o_end'];
		//当前时间 大于 最大时间 返回活动已经结束
		if($time>$maxTime){
			return true;
		}
	}
	
	//获取可预约状态
	public  function getPurchaseStatus(){
		$time = time();
		$row = $this->getStreamRow();
		//判断当前时间不是预约范围内
		if(!($time>=$row['p_start'] && $time<=$row['p_end'])){
			return false;
		}
		//如果预约量等于-1 不限量预约
		if($row['pur_num']==-1){
			return true;
		}
		if($this->getOrderNum($this->phone)>$row['order_num']){
			return false;
		}
		if($this->getPurchaseCount()<$row['pur_num']){
			return true;
		}
	}
	
	
	//获取已经预约完状态
	public  function getPurchaseFinish(){
		$time = time();
		$row = $this->getStreamRow();
		if(!($time>=$row['p_start'] && $time<=$row['p_end'])){
			return false;
		}
		if($row['num']==-1){
			return false;
		}
		if($this->getPurchaseNum()>=$row['num']){
			return true;
		}
	}
	
	//获取已经预定
	public  function getPurchasedStatus(){
		$time = time();
		$row = $this->getStreamRow();
		if(!($time>=$row['p_start'] && $time<=$row['p_end'])){
			return false;
		}
		if($this->getOrderNum($this->phone)>=$row['order_num']){
			return true;
		}
		if($this->getPurchaseCount()>=$row['pur_num']){
			return true;
		}
	}
	
	//获取可立即订购状态
	public  function getOrderStatus(){
		$time = time();
		$row = $this->getStreamRow();
		if(!($time>=$row['o_start'] && $time<=$row['o_end'])){
			return false;
		}
		if($row['order_num']==-1){
			return true;
		}
		if(empty($this->phone)){
			return true;
		}
		if($this->getOrderNum($this->phone)<$row['order_num']){
			return true;
		}
	}
	
	//获取已经抢完状态
	public function getOrderFinish(){
		$time = time();
		$row = $this->getStreamRow();
		if(!($time>=$row['o_start'] && $time<=$row['o_end'])){
			return false;
		}
		if($row['num']==-1){
			return false;
		}
		if($this->getOrderNum()>=$row['num']){
			return true;
		}
	}
	
	//获取已经订购状态
	public  function getOrderedStatus(){
		$time = time();
		$row = $this->getStreamRow();
		if(!($time>=$row['o_start'] && $time<=$row['o_end'])){
			return false;
		}
		if($row['order_num']==-1){
			return false;
		}
		if(empty($this->phone)){
			return false;
		}
		if($this->getOrderNum($this->phone)>=$row['order_num']){
			return true;
		}
	}
	
	//获取总的预约数量
	private function getPurchaseNum(){
		return $this->getNationalDayDataModule()->getPurchaseCount(array('stream_code'=>$this->streamCode));
	}
	
	//获取自己预约的总数
	private function getPurchaseCount(){
		return $this->getNationalDayDataModule()->getPurchaseCount(array('stream_code'=>$this->streamCode,'phone'=>$this->phone));
	}
	
	//获取单行流量
	private function getStreamRow(){
		return $this->getNationalDayDataModule()->getStreamRow(array('stream_code'=>$this->streamCode));
	}
	
	//获取订购总量
	private function getOrderNum($phone=''){
		return $this->getNationalDayDataModule()->getOrderNum($this->streamCode,$phone);
	}
	
	//获取国庆数据层
	private function getNationalDayDataModule(){
		return NationalDayData::getIns();
	}
}

?>
