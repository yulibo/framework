<?php

namespace Module\Stream\Extension;

use \Exception;
use \Module\Stream\DragonBoatStatus;
use \Module\Stream\Extension\StreamLimit\DragonBoat as DragonBoatLimit;

//中秋
class DragonBoat {

    public $err; //错误信息
    public $code; //错误码
    private $userPhone; //用户电话
    private $userId; //用户ID
    private $limitDragonBoat; //限制对象

    const COUPON_TYPE = 14; //中秋活动类型
    const COUPON_BATCH_ID = 29; //优惠码批次ID
	
    public function __construct() {
        $this->userPhone = $_SESSION['user_info']['user_phone']; //用户电话 
        $this->userId = $_SESSION['user_info']['user_id']; //初始化用户ID 
    }
	

    //获取到结束时间的天数
    public function getEndDayNum() {
        return DragonBoatStatus::getEndDayNum();
    }

    //获取流量列表
    public function getStreamList() {
        return $this->getDragonBoatList()->getStreamList();
    }

    //订购流量
    public function orderStream($streamCode) {
        try {
            $this->getDragonBoatLimit()->orderStream($streamCode);
            return true;
        } catch (Exception $e) {
            $this->err = $e->getMessage();
            $this->code = $e->getCode();
            return false;
        }
    }

    //获取流量订购列表
    public function getOrderStreamList() {
        if (empty($this->userPhone)) {
            return false;
        }
        $where = array();
        $where['a.status'] = 1;
        $where['a.r_type'] = DragonBoatLimit::R_TYPE;
        $where['a.phone'] = $this->userPhone;
        return $this->getOrderStreamModel()->getOrderPacageList($where);
    }

    //获取优惠劵
    public function addCoupon() {
        if (empty($this->userId))
            return false;
        $data = array(
            'user_id' => $this->userId,
            'type' => self::COUPON_TYPE,
            'phone' => $this->userPhone
        );
        $is_get = $this->getCouponModoule()->getPrefrem($data); //批次是否存在
        if (!empty($is_get))
            return false;
        return $this->getCouponModoule()->getCoupon(self::COUPON_BATCH_ID, $this->userId, self::COUPON_TYPE, $this->userPhone);
    }

    //获取订购记录model
    private function getOrderStreamModel() {
        return \Model\StreamProduct::instance();
    }

    //获取优惠券module
    private function getCouponModoule() {
        return new \Module\Coupon\Coupon();
    }

    //获取model list
    private function getDragonBoatList() {
        return new \Module\Stream\Extension\StreamList\DragonBoat();
    }

    //获取model limit
    private function getDragonBoatLimit() {
        if (!empty($this->limitDragonBoat)) {
            return $this->limitDragonBoat;
        }
        return $this->limitDragonBoat = new \Module\Stream\Extension\StreamLimit\DragonBoat();
    }

}

?>
