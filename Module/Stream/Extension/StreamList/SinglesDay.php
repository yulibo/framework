<?php

namespace Module\Stream\Extension\StreamList;

use Module\Stream\StreamList;
use Module\Stream\Extension\Status\SinglesDayStatus;
use Module\Stream\StreamNum;
use Module\Stream\Extension\StreamLimit\DragonBoat as DragonBoatLimit;
use \Exception;

class SinglesDay extends StreamList {

	private static $obj;//当前对象
	
    private static $statusTxt = [SinglesDayStatus::ORDER => '立即订购',
        SinglesDayStatus::END_AC => '已结束',
        SinglesDayStatus::ORDERED => '已订购',
        SinglesDayStatus::NOT_START => '未开始']; //状态txt
		
    private static $notAvailable = [
        SinglesDayStatus::END_AC,
        SinglesDayStatus::ORDERED,
        SinglesDayStatus::NOT_START]; //不可点击的状态	
	
    //获取流量状态
    protected function getStatus($streamCode) {
        return $this->getSinglesDayStatus()->getStatus($streamCode);
    }

    //获取流量列表
    protected function getDataList() {
        return $this->getDragonBoatModel()->getAll();
    }

    //格式列表
    protected function formatStreamList(&$list) {
        foreach ($list as &$val) {
			$val['status'] = $this->getStatus($val['stream_code']);
            $val['statustxt'] = self::$statusTxt[$val['status']];
            $val['notav'] = in_array($val['status'], self::$notAvailable) ? 1 : 0; //不可点击的状态
			$val['price'] = explode('.',$val['price']);
        }
    }


    //获取流量model
    private function getDragonBoatModel() {
        return \Model\SinglesDay\Stream::instance();
    }

    //获取状态
    private function getSinglesDayStatus() {
        return  SinglesDayStatus::getIns();
    }

}

?>