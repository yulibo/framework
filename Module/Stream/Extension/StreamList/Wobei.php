<?php
namespace Module\Stream\Extension\StreamList;
use Module\Stream\StreamList;
use Module\Stream\PanicBuyStatus;
use Module\Stream\StreamNum ;

use \Exception;

class Wobei{
	
	const RTYPE = 12;

	private static $status=[PanicBuyStatus::NOT_START=>'close',
			PanicBuyStatus::END_AC=>'close',
			PanicBuyStatus::ON=>'open'];//状态txt	

	private static $statusTxt=[PanicBuyStatus::ORDER=>'立即兑换',
			PanicBuyStatus::NOGOODS=>'已抢光',
			PanicBuyStatus::ORDERED=>'机会已用完'];//状态txt
			
	private static $notAvailable=['已抢光','机会已用完','已过期','未开始'];//不可点击的状态

	private static $flag = array(
		"1" => "未开始",
		"2" => "立即兑换",
		"3" => "已过期"
		);
	//获取流量状态
	protected function getStatus($streamCode,$p_count){
		return $this->getPanicBuyStatus()->getStatus($streamCode,$p_count);
	}

	//获取单条信息
	public function getPanicInfo($id){
		return $this->getPanicBuyModel()->getOne(array('id'=>$id));
	}

	//获取开启
	public function getOpenStatus(){
		return self::$status[$this->getPanicBuyStatus()->getOpenStatus()];
	}

	//获取格式化开启时间
	public function getFormatStart(){
		$time = $this->getPanicBuyStatus()->getStartTime();
		return $time;
	}

	//获取格式化结束时间
	public function getFormatEnd(){
		return $this->getPanicBuyStatus()->getEndTime();
	}
	
	//获取流量列表
	public function getDataList(){
		$list = array();
		$list = $this->getPanicBuyModel()->getAllList();
		$this->formatStreamList($list);
		return  $list;
	}

	//流量包有效期判断
	private function isEffec($start,$end){
		if(time() < $start){
			return 1;
		}
		if(time() > $end){
			return 3;
		}
		
		return 2;		
	}

	private function getCacheKey($where){
		return md5(json_encode($where));
	}

	private static $codeList;

	//得到相关联产品码
	private function getCodeList($rows){

		$key = $this->getCacheKey($rows);
		if(isset(self::$codeList[$key])){
			return self::$codeList[$key];
		}

		$streamIdList = array();
		$streamCodeList = array();
		foreach ($rows as $value) {
			array_push($streamIdList, $value['stream_id']);
		}
		$code = $this->getPackageCode($streamIdList);
		foreach ($code as $key => $value) {
			array_push($streamCodeList, $value['stream_code']);
		}

		return self::$codeList = $streamCodeList;
	}

	//格式列表
	protected function formatStreamList(&$list){
		foreach($list as &$val){
			$rows = $this->getPanicBuyModel()->getSameLevel(array('level'=>$val['level']));//获取相同的流量包
			$streamCodeList = $this->getCodeList($rows);

			$status = self::$flag[$this->isEffec($val['p_begintime'],$val['p_endtime'])];
			if($status==self::$statusTxt[PanicBuyStatus::ORDER]){
				$val['status'] = self::$statusTxt[$this->getStatus($streamCodeList,$val['p_amount'])];
			}else{
				$val['status'] = $status;
			}

            $val['notav'] = in_array($val['status'],self::$notAvailable) ? 1:0;//不可点击的状态
            // $val['show_price'] = intval($val['show_price']);
            $val['true_price'] = intval($val['true_price']);

            //得到等级相同的流量包
            $count = 0;
			$count = $this->getAllOrderStreamDayNum($streamCodeList);
			$p = $val['p_amount'] - intval($count);
            if($p > 0){
            	$val['p_amount'] = $p;//剩余抢购数
            }else{
            	$val['p_amount'] = 0;
            }
		}
	}
	
	//获取当天订购的总量
	private function getAllOrderStreamDayNum($streamCode){
		return $this->getStreamNum($streamCode)->getAllOrderPacageCount(array('r_type'=>self::RTYPE));
	}

	//得到表的流量编码
    public function getPackageCode($stream_id){
        $row = \Model\StreamProduct::instance()->getStreamCodeList(array('id'=>$stream_id));
        if(empty($row)){
            return false;
        }

        return $row;
    }
	
	//获取流量model
	private function getStreamNum($streamCode){
		return new StreamNum($streamCode);
	}
	
	//获取流量model
	private function getPanicBuyModel(){
		return \Model\PanicBuy::instance();
	}
	
	//获取状态
	private function getPanicBuyStatus(){
		return new PanicBuyStatus();
	}
}

?>