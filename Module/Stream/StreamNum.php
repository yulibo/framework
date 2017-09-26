<?php
namespace Module\Stream;

class StreamNum implements StreamrNumInterFace
{
	private $streamCode;//产品编码
	private $userPhone;//用户电话
	private $streamRow;//流量产品行
	
	public function __construct($streamCode,$phone=''){
		$this->streamCode = $streamCode;
		$this->userPhone = $phone;
	}
	
	//获取流量包当月的已订购次数
	public function getOrderStreamMonthNum(){
		$where = array();
		$where['ctime >='] = mktime(0,0,0,date('m'),1,date('Y'));
		return  $this->getCount($where);
	}
	
	//获取流量包当天的已订购次数
	public function getOrderStreamDayNum(){
		$where['ctime >'] = mktime(0,0,0,date('m'),date('d'),date('Y'));
		$where['ctime <='] = mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
		return $this->getCount($where);
	}
	
	
	//获取流量包当月的可订购次数
	public function getStreamMonthNum(){
		$where = array();
		$row = $this->getRow($where);
		return $row['month_num'];
	}
	
	//获取流量包当月的可订购次数
	public function getStreamDayNum(){
		$where = array();
		$row = $this->getRow($where);
		return $row['day_num'];
	}
	
	//获取单行 row
	private function getRow($where){
		if($this->streamRow){
			return $this->streamRow;
		}
		$where['stream_code'] = $this->streamCode;
		return $this->streamRow=$this->getStreamModel()->getRow($where);
	}
	
	//获取所有人当天的订购总量
	public function getAllOrderPacageCount($where=array()){
		$where['ctime >'] = mktime(0,0,0,date('m'),date('d'),date('Y'));
		$where['ctime <='] = mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
		$where['package_code'] = $this->streamCode;
		$where['status'] = 1;
		return $this->getStreamModel()->getOrderPacageCount($where);
	}
	
	//获取 count
	public function getCount($where=array()){
		$where['package_code'] = $this->streamCode;
		!empty($this->userPhone) && $where['phone'] = $this->userPhone;
		$where['status'] = 1;
		return $this->getStreamModel()->getOrderPacageCount($where);
	}
	
	//获取model stream 
	private function getStreamModel(){
		return \Model\StreamProduct::instance();
	}
}

?>