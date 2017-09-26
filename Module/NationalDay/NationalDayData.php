<?php

namespace Module\NationalDay;

use \Exception;

class NationalDayData  {
	
	private static $ins;//对象本身
	
	const ON_CACHE = true;//是否开启缓存

	final private function __construct(){
		
	}
	
	//获取对象
	public static function getIns(){
		if(!empty(self::$ins)){
			return self::$ins;
		}else{
			return self::$ins = new self();
		}
	}
	
	//获取流量列表
	public function getStreamList(){
		$key = 'streamlist';
		if(self::ON_CACHE && $result = $this->redis()->get($key)){
			return json_decode($result,true);
		}
		$result =  $this->getNationalDayModel()->getAll();
		$this->redis()->set($key,json_encode($result));
		return $result;
	}
	
	private $streamRow;//当行数据
	
	
	//获取流量列表
	public function getStreamRow($where = array()){
		$key = $this->getCacheKey($where); //缓存 key
		if(isset($this->streamRow[$key])){
			return $this->streamRow[$key]; 
		}
		if(self::ON_CACHE && $result = $this->redis()->get($key)){
			return json_decode($result,true);
		}
		$result = $this->streamRow[$key] = $this->getNationalDayModel()->getOne($where);
		$this->redis()->set($key,json_encode($result));
		return $result;
	}
	
	private $purchaseCountRow;//自己是否预约数据
	
	//自己预约的数量
	public function getPurchaseCount($where = array()){
		$key = $this->getCacheKey($where); //缓存 key
		if(isset($this->purchaseCountRow[$key])){
			return $this->purchaseCountRow[$key]; 
		}
		return $this->purchaseCountRow[$key] = $this->getNationalPurchaseLogModel()->getCount($where);
	}
	
	
	private $orderPackModule;//自己是否预约数据
	//获取订购日志
	
	private function getOrderPackModule($where=array()){
		$key = $this->getCacheKey($where); //缓存 key
		if(isset($this->orderPackModule[$key])){
			return $this->orderPackModule[$key]; 
		}
		return $this->orderPackModule[$key] = new \Module\Stream\StreamNum($where['stream_code'],$where['phone']);
	}
	
	
	//获取流量包当天订购的总量
	public function getOrderStreamDayNum($cwhere){
		$where['ctime >'] = mktime(0,0,0,date('m'),date('d'),date('Y'));
                $where['ctime <='] = mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
		$where['r_type'] = NationalDayOrder::R_TYPE;
		return $this->getOrderPackModule($cwhere)->getCount($where);
	}

	//获取流量包总订购量
	public function getCount($where){
		return $this->getOrderPackModule($where)->getCount(array('r_type'=>NationalDayOrder::R_TYPE));
	}
	
	private $orderNumList;//每天订购量
	
	//获取订购总量
	public function getOrderNum($streamCode,$phone=''){
		$key = $this->getCacheKey(array('stream_code'=>$streamCode,'phone'=>$phone)); //缓存 key
		if(isset($this->orderNumList[$key])){
			return $this->orderNumList[$key]; 
		}
		$row = $this->getStreamRow(array('stream_code'=>$streamCode));
		if($row['repet_code']){
			$streamCodeList = array_merge([$streamCode], explode(',', $row['repet_code']));
		}else{
			$streamCodeList = $streamCode;
		}
		$where['stream_code'] = $streamCodeList;
		$where['phone'] = $phone;
		if($row['is_limit']){
			$count = $this->getOrderStreamDayNum($where); //当天的订购总量
		}else{ 
			$count = $this->getCount($where); //总共订购总量
		}
		return $this->orderNumList[$key]=$count; 
	}
	
	//redis 
	public function redis($endpoint = 'default', $as='storage') {
        return \Core\Lib\RedisDistributed::instance($endpoint, $as);
    }
	
	//获取缓存key
	private function getCacheKey($where){
		return md5(json_encode($where));
	}
	
	//获取国庆流量model
	private function getNationalDayModel(){
		return \Model\NationalDay\NationalDay::instance();
	}
	
	//获取国庆预约log model
	private function getNationalPurchaseLogModel(){
		return \Model\NationalDay\NationalPurchaseLog::instance();
	}
	
}

?>
