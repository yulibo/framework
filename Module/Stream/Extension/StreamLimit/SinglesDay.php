<?php

namespace Module\Stream\Extension\StreamLimit;

use Module\Stream\StreamOrder;
use Module\Stream\Extension\Status\SinglesDayStatus;
use \Exception;

//双十一
class SinglesDay extends StreamOrder {

    const R_TYPE = 19; //双十一活动类型ID
    
	const NOT_LIMIT = -1;//不限制当天订购量
	
	const VIDEO_CODE = 1109;//腾讯视频CODE

	private static $countList;//
   
    //订购流量
    public function orderStream($streamCode) {
        //未开始
        if (SinglesDayStatus::getNotStartStatus()) {
            throw new Exception('活动未开始');
        }
        //已结束
        if (SinglesDayStatus::getEndStatus()) {
            throw new Exception('活动已经结束');
        }
		return parent::orderStream($streamCode);
    }


	
	//获取中秋活动可以订购流量的总数
    protected function getStreamAcDayNum() {
        $row = $this->getRow();
        if (empty($row)) {
            return false;
        }
        return $row['day_num'];
    }
	
	 //获取流量包当天订购的总数--针对具体的活动
    private function getOrderPacageCount(array $where) {
        $where['r_type'] = self::R_TYPE;
        $where['package_code'] = $this->getCombinStreamCode();
        $where['status'] = 1;
		$key = $this->getCacheKey($where);
		if(isset(self::$countList[$key])){
			return self::$countList[$key];
		}
        return self::$countList[$key] = $this->getOrderPacageModule()->getOrderPacageCount($where);
    }
	
	private function getCacheKey($where){
		return md5(json_encode($where));
	}
	
	 //获取中秋活动的当天订购次数
    protected function getOrderStreamAcDayNum() {
        $where = array();
		if(empty($this->userPhone)){
			return false;
		}
        $where['phone'] = $this->userPhone;
		$where['ctime<='] = mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')) - 1;
        $where['ctime>='] = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
        return $this->getOrderPacageCount($where);
    }
	
	//获取当月的订购次数
    protected function getOrderStreamAcMonthNum() {
        $where = array();
		if(empty($this->userPhone)){
			return false;
		}
        $where['phone'] = $this->userPhone;
		$beginTime =date('Y-m-01', strtotime(date("Y-m-d")));
		$where['ctime>='] = strtotime($beginTime);
		
        $where['ctime<='] = strtotime( date('Y-m-d', strtotime("$beginTime +1 month -1 day")));
        return $this->getOrderPacageCount($where);
    }
	
	
	
	 //判断指定流量包在某个活动每天的订购次数
    public function checkStreamAcDayNum() {
        if ($this->getStreamAcDayNum() == self::NOT_LIMIT) {
            return true;
        }
        if($this->getOrderStreamAcDayNum() >= $this->getStreamAcDayNum()) {
            throw new Exception('当天已经订购满了');
        }
		$row = $this->getRow();
		if ($row['month_num'] == self::NOT_LIMIT) {
            return true;
        }
		if($this->getOrderStreamAcMonthNum() >= $row['month_num']) {
            throw new Exception('当月已经订购满了');
        }
        return true;
    }
	
	

    //获取流量包名称
    protected function getPackageName() {
        $row = $this->getRow(); //当前订购流量包
        return $row['name'];
    }
	
	
	//成功发送短信
	protected function orderSuccessOpt(){
		//腾讯视频CODE
		if($this->streamCode==self::VIDEO_CODE){
			return $this->sendVideoMsg($this->userPhone);
		}
		if($msg = $this->getMessage()){
			return \Module\PhoneCode::instance()->sendMessage($this->userPhone,$msg);
		}
	}

	//发送腾讯视频短信
	private function sendVideoMsg($phone){
        $msg = "尊敬的用户，您已成功订购并开通15元WO+视频腾讯定向流量包月产品。流量包时效按自然月计算，跨月清零，自动续订，所含流量计入流量封顶。【视频风暴】";
        \Module\PhoneCode::instance()->sendMessage($phone,$msg);
        $msg = "【活动提示】尊敬的用户，参与视频风暴腾讯季活动用户在订购成功后，需下载最新版腾讯视频客户端进行激活，否则无法获得1G省内流量包月及腾讯视频会员。详细步骤请点击http://www.169ol.com/qqactivate【沃4G+视频风暴】";
        \Module\PhoneCode::instance()->sendMessage($phone,$msg);
    }
	
	//获取行
    private function getRow() {
        return  $this->getStreamModel()->getRow(array('stream_code'=>$this->streamCode));
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

    //获取双十一model
    private function getStreamModel() {
        return \Model\SinglesDay\Stream::instance();
    }
	
	//获取
    private function getOrderPacageModule() {
        return \Model\StreamProduct::instance();
    }



	//获取短信内容
	private function getMessage(){
		$row = $this->getRow(); //当前订购流量包
		return sprintf($row['phone_msg'],$row['name']);
	}
}

?>