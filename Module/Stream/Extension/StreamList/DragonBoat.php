<?php

namespace Module\Stream\Extension\StreamList;

use Module\Stream\StreamList;
use Module\Stream\DragonBoatStatus;
use Module\Stream\StreamNum;
use Module\Stream\Extension\StreamLimit\DragonBoat as DragonBoatLimit;
use \Exception;

class DragonBoat extends StreamList {

	private static $obj;//当前对象
	
    private static $statusTxt = [DragonBoatStatus::ORDER => '立即订购',
        DragonBoatStatus::END_AC => '已结束',
        DragonBoatStatus::ORDERED => '已订购',
        DragonBoatStatus::NOGOODS => '立即订购',
        DragonBoatStatus::NOT_START => '未开始',
        DragonBoatStatus::ORDERED_NOGOODS => '已订购']; //状态txt
		
    private static $notAvailable = [
        DragonBoatStatus::END_AC,
        DragonBoatStatus::NOGOODS,
        DragonBoatStatus::ORDERED_NOGOODS,
        DragonBoatStatus::NOT_START]; //不可点击的状态
		
	private static $getAllOrderStreamDayNum=[];//获取当天订购的总量
	
	
    //获取流量状态
    protected function getStatus($streamCode) {
        return $this->getDragonBoatStatus()->getStatus($streamCode);
    }

    //获取流量列表
    protected function getDataList() {
        return $this->getDragonBoatModel()->getList();
    }

    //格式列表
    protected function formatStreamList(&$list) {
        parent::formatStreamList($list);
        foreach ($list as &$val) {
            if (($num = $this->getAllOrderStreamDayNum($val['streamCode'])) && !empty($num)) {
                $val['per'] = (($num / $val['num']) > 1 ? 1 : (($num / $val['num']))) * 100;
            } else {
                $val['per'] = 0;
            }
            $val['statustxt'] = self::$statusTxt[$val['status']];
            $val['notav'] = in_array($val['status'], self::$notAvailable) ? 1 : 0; //不可点击的状态
        }
    }

    //获取当天订购的总量
    private function getAllOrderStreamDayNum($streamCode) {
		if(isset(self::$getAllOrderStreamDayNum[$streamCode])){
			return self::$getAllOrderStreamDayNum[$streamCode];
		}
        return self::$getAllOrderStreamDayNum[$streamCode] = $this->getStreamNum($streamCode)->getAllOrderPacageCount(array('r_type' => DragonBoatLimit::R_TYPE));
    }

    //获取流量model
    private function getStreamNum($streamCode) {
        return new StreamNum($streamCode);
    }

    //获取流量model
    private function getDragonBoatModel() {
        return \Model\Stream\DragonBoat::instance();
    }

    //获取状态
    private function getDragonBoatStatus() {
        return  DragonBoatStatus::getIns();
    }

}

?>