<?php

namespace Module\Stream\Extension\StreamLimit;

use Module\Stream\StreamOrder;
use Module\Stream\DragonBoatStatus;
use \Exception;

//中秋
class DragonBoat extends StreamOrder {

    const R_TYPE = 11; //中秋活动类型ID
    private static $dragonboatrow; //行

    //获取中秋活动的当天订购次数
    protected function getOrderStreamAcDayNum() {
        $where = array();
		if(empty($this->userPhone)){
			return false;
		}
        $where['phone'] = $this->userPhone;
        return $this->getOrderPacageCount($where);
    }

    //获取中秋活动可以订购流量的总数
    protected function getStreamAcDayNum() {
        $row = $this->getRow();
        if (empty($row)) {
            return false;
        }
        return $row['day_num'];
    }

    //订购流量
    public function orderStream($streamCode) {
        //未开始
        if (DragonBoatStatus::getNotStartStatus()) {
            throw new Exception('活动未开始');
        }
        //已结束
        if (DragonBoatStatus::getEndStatus()) {
            throw new Exception('活动已经结束');
        }
		return  parent::orderStream($streamCode);
    }



    //获取流量包名称
    protected function getPackageName() {
        $row = $this->getRow(); //当前订购流量包
        return $row['package_name'];
    }
	
	
	//成功发送短信
	protected function orderSuccessOpt(){
		return \Module\PhoneCode::instance()->sendMessage($this->userPhone,$this->getMessage());
	}

    //判断指定流量包在某个活动每天的订购次数
    public function checkStreamAcDayNum() {
        if ($this->getStreamAcDayNum() == self::NOT_LIMIT) {
            return true;
        }
        if ($this->getOrderStreamAcDayNum() >= $this->getStreamAcDayNum()) {
            throw new Exception('当天已经订购满了');
        }
        return true;
    }

    //检查流量包是否已经抢完
    public function checkStreamAllNum() {
        $row = $this->getRow(); //获取当前流量包
        if ($row['num'] == self::NOT_LIMIT) {
            return true;
        }
        $where = array();
        $count = $this->getOrderPacageCount($where);
        if ($count >= $row['num']) {
            throw new Exception('流量包已经抢完');
        }
        return true;
    }
	
	private static $countList;//指定条件查询的总数
	
	
    //获取流量包当天订购的总数--针对具体的活动
    private function getOrderPacageCount(array $where) {
        $where['ctime<='] = mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')) - 1;
        $where['ctime>='] = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
        $where['r_type'] = self::R_TYPE;
        $where['package_code'] = $this->getCombinStreamCode();
        $where['status'] = 1;
		$key = $this->getCacheKey($where);
		if(isset(self::$countList[$key])){
			return self::$countList[$key];
		}
        return self::$countList[$key] = $this->getStreamModel()->getOrderPacageCount($where);
    }
	
	private function getCacheKey($where){
		return md5(json_encode($where));
	}

    //获取组合流量包产品码
    protected function getCombinStreamCode() {
        $row = $this->getRow(); //当前订购流量包
        if ($row['repet_code']) {
            $streamCodeList = array_merge([$this->streamCode], explode(',', $row['repet_code']));
        } else {
            $streamCodeList[] = $this->streamCode;
        }
        return $streamCodeList;
    }

    //获取行
    private function getRow() {
        if (!empty(self::$dragonboatrow[$this->streamCode])) {
            return self::$dragonboatrow[$this->streamCode];
        }
        return self::$dragonboatrow[$this->streamCode] = $this->getDragonBoatModel()->getRow(array('g.stream_code' => $this->streamCode, 'g.is_delete' => 0, 'g.status' => 1));
    }

    //获取中秋model
    private function getDragonBoatModel() {
        return \Model\Stream\DragonBoat::instance();
    }

    //获取model stream 
    private function getStreamModel() {
        return \Model\StreamProduct::instance();
    }
	
	//获取短信内容
	private function getMessage(){
		$row = $this->getRow(); //当前订购流量包
		$str = \Stream\Config\Biz::$msg;
		return sprintf($str,$row['title']);
	}
}

?>