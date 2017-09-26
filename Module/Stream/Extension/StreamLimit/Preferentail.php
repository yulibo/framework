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
class Preferentail extends StreamOrder
{

    const R_TYPE = 2; //流量办理
    private static $dragonboatrow; //行
    private static $packgename; //流量包名称
    private static $packgecode = array(1170,1171,1172,1173,1174,1175);
   

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
            $row = $this->getModel()->getOne(array('code'=>$this->streamCode)); //当前订购流量包
            return self::$packgename = $row['title'];
        }else{
            return self::$packgename;
        }

    }

    //获取model
    private function getModel() {
        return \Model\Preferential::instance();
    }

    //获取行
    private function getRow() {
        if (!empty(self::$dragonboatrow[$this->streamCode])) {
            return self::$dragonboatrow[$this->streamCode];
        }
        self::$dragonboatrow[$this->streamCode] = $this->getDragonBoatModel()->getRow(array('g.stream_code' => $this->streamCode, 'g.is_delete' => 0, 'g.status' => 1));
        if (empty(self::$dragonboatrow[$this->streamCode])) {
            return false;
        }
        return self::$dragonboatrow[$this->streamCode];
    }

    //获取中秋model
    private function getDragonBoatModel() {
        return \Model\Stream\DragonBoat::instance();
    }

    //成功发送短信
    protected function orderSuccessOpt(){
        if(in_array($this->streamCode,self::$packgecode))
            return \Module\PhoneCode::instance()->sendMessage($this->userPhone,$this->getMessage());
    }

    //获取短信内容
    private function getMessage(){
        $row = $this->getRow(); //当前订购流量包
        $str = \Stream\Config\Biz::$msg;
        return sprintf($str,$row['title']);
    }
}