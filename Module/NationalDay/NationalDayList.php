<?php

namespace Module\NationalDay;

use Module\Stream\StreamList;
use Module\Stream\StreamNum;
use \Exception;

class NationalDayList extends StreamList {

	private static $obj;//当前对象
	
    private static $statusTxt = [NationalDayStatus::NOT_START => '未开始',
        NationalDayStatus::PURCHASE => '立即预定',
        NationalDayStatus::PURCHASED => '已预定',
        NationalDayStatus::ORDER => '立即订购',
        NationalDayStatus::ORDERED => '已订购',
        NationalDayStatus::STOP => '已结束',
		NationalDayStatus::FINISH_Y => '已抢完',
		NationalDayStatus::FINISH => '已抢完']; //状态txt
		
    private static $notAvailable = [
        NationalDayStatus::NOT_START,
        NationalDayStatus::PURCHASED,
        NationalDayStatus::ORDERED,
        NationalDayStatus::STOP,
		NationalDayStatus::FINISH_Y,
		NationalDayStatus::FINISH]; //不可点击的状态
	
	private static $fakeOrderStreamNum = [
		1190=>0,
		1109=>0,
		1170=>0,
		1027=>0,
		1096=>0,
		1174=>0];//流量假数据预定--订购

	private static $fakePurStreamNum = [1190=>0];//流量假数据预约--预约
	
    //获取流量状态
    protected function getStatus($streamCode) {
        return $this->getNationalDayStatus()->getStatus($streamCode);
    }

    //获取流量列表
    protected function getDataList() {
        return $this->getNationalDayModule()->getStreamList();
    }

    //格式列表
    protected function formatStreamList(&$list) {
        foreach ($list as &$val) {
			$val['status']  = $this->getStatus($val['stream_code']);//流量包状态值
            if (!empty($val['is_limit']) && ($num = $this->getNationalDayModule()->getOrderNum($val['stream_code'])) && !empty($num)) {
				$val['per'] = (($num / $val['num']) > 1 ? 1 : (($num / $val['num']))) * 100;
				//日租包 每天订购的百分比
            } else {
                $val['per'] = 0;
				//其他包百分默认为0
            }
			$val['ordered_num'] =  $this->getNationalDayModule()->getOrderNum($val['stream_code']); //流量包订购总量
			//订购量造假
			if(isset(self::$fakeOrderStreamNum[$val['stream_code']])){
				$val['ordered_num'] += self::$fakeOrderStreamNum[$val['stream_code']];//订购量造假
			}
			//预约总量
			$val['pured_num'] = $this->getNationalDayModule()->getPurchaseCount(array('stream_code'=>$val['stream_code']));
			//预约量造假
			if(isset(self::$fakePurStreamNum[$val['stream_code']])){
				$val['pured_num'] += self::$fakePurStreamNum[$val['stream_code']];//订购量造假
			}
			//状态描述
            $val['statustxt'] = self::$statusTxt[$val['status']];
            $val['notav'] = in_array($val['status'], self::$notAvailable) ? 1 : 0; //不可点击的状态
        }
    }

	//获取单行流量
	private function getStreamRow($streamCode){
		return $this->getNationalDayModule()->getStreamRow(array('stream_code'=>$streamCode));
	}
	

    //获取流量model
    private function getStreamNum($streamCode) {
        return new StreamNum($streamCode);
    }

    //获取流量model
    private function getNationalDayModule() {
        return NationalDayData::getIns();
    }

    //获取状态
    private function getNationalDayStatus() {
        return  NationalDayStatus::getIns();
    }

}

?>