<?php
/**
 * name
 *
 * @param array
 * @param obj
 * @return array
 */

namespace Module\Stream\Extension\StreamLimit;

use Module\Stream\StreamOrder;
use Module\Stream\DragonBoatStatus;
use \Exception;
class Lottery extends StreamOrder
{

    const R_TYPE = 1; //流量办理
    private static $dragonboatrow; //行
    private static $packgename; //流量包名称
    //订购流量
    public function orderStream($streamCode) {

        try {
            parent::orderStream($streamCode);
        } catch (Exception $e) {
            if ($e->getCode() != -1) {
                throw new Exception($e->getMessage(), $e->getCode());
            }
            $streamCodeList = $this->getCombinStreamCode();
            $this->repetOrder($e->getCode(), $streamCodeList);
        }
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


    //获取流量包名称
    protected function getPackageName() {
        if(empty(self::$packgename)){
            $row = $this->getModel()->getRow(array('stream_code'=>$this->streamCode)); //当前订购流量包
            return self::$packgename = $row['stream_name'];
        }else{
            return self::$packgename;
        }

    }

    //获取行
    private function getRow() {
        if (!empty(self::$dragonboatrow[$this->streamCode])) {
            return self::$dragonboatrow[$this->streamCode];
        }
        self::$dragonboatrow[$this->streamCode] = $this->getModel()->getRow(array('stream_code' => $this->streamCode));
        if (empty(self::$dragonboatrow[$this->streamCode])) {
            return false;
        }
        return self::$dragonboatrow[$this->streamCode];
    }

    //获取抽奖model
    private function getModel() {
        return \Model\Stream\Lottery::instance();
    }

    //成功发送短信
    protected function orderSuccessOpt(){
        return \Module\PhoneCode::instance()->sendMessage($this->userPhone,$this->getMessage());
    }

    //获取短信内容
    private function getMessage(){
        $row = $this->getRow(); //当前订购流量包
        $str = \Stream\Config\Biz::$msg;
        return sprintf($str,$row['stream_name']);
    }
}