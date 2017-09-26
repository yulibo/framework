<?php

namespace Module\Stream;

use \Exception;

abstract class StreamOrder implements StreamrNumInterFace {

    protected $streamCode; //流量码 4位
    private $userId; //用户ID
    protected $userPhone; //用户电话
    public $err; //错误信息

    const R_TYPE = 0; //默认类型
    const NOT_LIMIT = -1; //不限制次数

    final public function __construct() {
        $this->init();
    }

    //初始化
    protected function init() {
        $this->userPhone = $_SESSION['user_info']['user_phone']; //用户电话 
    }

    //检查流量订购次数判断
    public function checkStreamLimit($streamCode) {
        $this->setStreamCode($streamCode);
        $this->checkStreamAllNum(); //检查流量包库存是否充足
        $this->checkStreamAcDayNum(); //检查活动的订购次数
        $this->checkStreamDayNum(); //检查流量包当天的订购总次数
        $this->checkStreamMonthNum(); //检查当月的订购总次数
        return true;
    }

    //订购流量
    public function orderStream($streamCode) {
        if (empty($this->userPhone)) {
            throw new Exception('请登录', 99);
        }
        try {
            $this->checkStreamLimit($streamCode);
            $this->transactFlow($this->streamCode);
            return true;
        } catch (Exception $e) {
			 if ($e->getCode() != -1) {
                throw new Exception($e->getMessage(), $e->getCode());
            }
            $streamCodeList = $this->getCombinStreamCode();
            $this->repetOrder($e->getCode(), $streamCodeList);
        }
    }

	 //重复订购
    protected function repetOrder($code, &$streamCodeList) {
        if ($code != -1) {
            throw new Exception($this->getStreamApi()->getService()->getError());
        }
        try {
            array_shift($streamCodeList);
            if (empty($streamCodeList[0])) {
                throw new Exception($this->getStreamApi()->getService()->getError());
            }
            $this->getStreamApi()->setErrorNull();
            if (!empty($streamCodeList[0])) {
                $this->transactFlow($streamCodeList[0]);
            }
        } catch (Exception $e) {
            if ($e->getCode() == 1) {
                return true;
            }
            $this->repetOrder($e->getCode(), $streamCodeList);
        }
    }
	
	//获取副产品ID
	protected function getCombinStreamCode(){
		$row = $this->getStreamProductModel()->getRow(['stream_code'=>$this->streamCode]);
        if ($row['by_stream_code']) {
            $streamCodeList = array_merge([$this->streamCode], explode(',', $row['by_stream_code']));
        } else {
            $streamCodeList[] = $this->streamCode;
        }
        return $streamCodeList;
	}
	
	
    //流量办理
    protected function transactFlow($streamCode) {
        $this->streamCode = $streamCode;
        $data = array();
        $data['modify'] = 'ding';
        $data['phone'] = $this->userPhone;
        $data['packagecode'] = $streamCode;
        $data['package_name'] = $this->getPackageName();
        $data['rType'] = static::R_TYPE;
		$data = array_merge($data,$this->transactFlowParmas()); //参数组合
        $this->getStreamApi()->transactFlow($data);
        if ($this->getStreamApi()->getCode()!=1) {
            $err = $this->getStreamApi()->getError();
            throw new Exception($err, -1);
        }else{
			$this->orderSuccessOpt(); //成功发送短信
		}
    }
	
	//添加订购流量的参数
	protected function transactFlowParmas(){
		return array();
	}
	
	
	//订购成功要做的事
	protected function orderSuccessOpt(){
		
	}

    //获取流量包名称
    protected function getPackageName() {
        
    }

    //获取流量码
    public function setStreamCode($streamCode) {
        if (empty($streamCode)) {
            throw new Exception('流量码不能为空');
        }
		$where=['stream_code'=>$streamCode,'is_delete'=>0,'status'=>1];
		if(!$this->getStreamProductModel()->getRow($where)){
			throw new Exception('流量产品不存在');
		}
        return $this->streamCode = $streamCode;
    }

    //判断每月的订购次数
    protected function checkStreamMonthNum() {
        if ($this->getStreamMonthNum() == self::NOT_LIMIT) {
            return true;
        } 
        if ($this->getOrderStreamMonthNum() >= $this->getStreamMonthNum()) {
            throw new Exception('流量包当月订购总次数已经订满,不能继续订购');
        }
    }

    //判断每天的订购次数
    protected function checkStreamDayNum() {
        if ($this->getStreamDayNum() == self::NOT_LIMIT) {
            return true;
        }
        if ($this->getOrderStreamDayNum() >= $this->getStreamDayNum()) {
            throw new Exception('流量包当天订购总次数已经订满,不能继续订购');
        }
    }

    //判断流量包是否已经抢完
    protected function checkStreamAllNum() {
        
    }

    //判断指定流量包在某个活动每天的订购次数
    protected function checkStreamAcDayNum() {
        
    }

    //获取流量包当月的已订购次数
    public function getOrderStreamMonthNum() {
        return $this->getStreamModule()->getOrderStreamMonthNum($this->streamCode);
    }

    //获取流量包当天的已订购次数
    public function getOrderStreamDayNum() {
        return $this->getStreamModule()->getOrderStreamDayNum($this->streamCode);
    }

    //获取某个活动的流量包已经订购的总数
    protected function getOrderStreamAcDayNum() {
        
    }

    //获取流量包当月的可订购次数
    public function getStreamMonthNum() {
        return $this->getStreamModule()->getStreamMonthNum($this->streamCode);
    }

    //获取流量包当天的可订购次数
    public function getStreamDayNum() {
        return $this->getStreamModule()->getStreamDayNum($this->streamCode);
    }

    //获取流量包在指定活动可以订购的次数
    protected function getStreamAcDayNum() {
        
    }
	
	//获取streamproductmodel
	private function getStreamProductModel(){
		return \Model\StreamProduct::instance();
	}

    //获取流量module
    private function getStreamModule() {
        return new StreamNum($this->streamCode, $this->userPhone);
    }

    //获取订购流量service
    protected function getStreamApi() {
        return \Module\Api\Stream::instance();
    }

}

?>