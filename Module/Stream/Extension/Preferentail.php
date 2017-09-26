<?php
/**
 * name
 *
 * @param array
 * @param obj
 * @return array
 */

namespace Module\Stream\Extension;

use \Exception;
use \Module\Stream\DragonBoatStatus;
use \Module\Stream\Extension\StreamLimit\Preferentail as PreferentailLimit;
class Preferentail
{

    public $err; //错误信息
    public $code; //错误码
    private $userPhone; //用户电话
    private $userId; //用户ID
    private $limitDragonBoat; //限制对象

    public function __construct() {
        $this->userPhone = $_SESSION['user_info']['user_phone']; //用户电话
        $this->userId = $_SESSION['user_info']['user_id']; //初始化用户ID
    }

    //订购流量
    public function orderStream($streamCode) {
        try {
            $this->PreferentailLimit()->orderStream($streamCode);
            return true;
        } catch (Exception $e) {
            $this->err = $e->getMessage();
            $this->code = $e->getCode();
            return false;
        }
    }

    //获取model limit
    private function PreferentailLimit() {
        if (!empty($this->limitDragonBoat)) {
            return $this->limitDragonBoat;
        }
        return $this->limitDragonBoat = new \Module\Stream\Extension\StreamLimit\Preferentail();
    }
}