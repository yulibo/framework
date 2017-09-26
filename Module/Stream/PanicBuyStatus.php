<?php
namespace Module\Stream;

use \Exception;

class PanicBuyStatus 
{
	private $streamCode;//流量包
	private $phone;//手机号
	
	private static $activityTime = array('stime'=>'2016-09-18','etime'=>'2016-09-30 23:59:59'); //沃贝抢购范围

	const RTYPE = 12;//沃贝抢购
	const SUMDAY = 2;//所有流量包每天总共可以订购的次数
	const SUMMONTH = 10;//所有流量包每月总共可以订购的次数

	const NOT_START=-1;//未开始
	const END_AC=1;//已经结束
	const ON = 2;//开始

	const ORDER=0;//可订购
	const NOGOODS = 2;//已抢光
	const ORDERED = 3;//机会已用完

	public function __construct(){
		$this->phone = $_SESSION['user_info']['user_phone'];
	}
	
	//获取状态
	public function getStatus($streamCode,$p_count){
		if(empty($streamCode)){
			throw new Exception('流量编号不能为空');
		}
		$this->streamCode = $streamCode;
		if($this->getNoGoodsStatus($p_count)){//已抢光
			return self::NOGOODS;
		}elseif($this->getPackagesCount()){
			return self::ORDERED;
		}
		return self::ORDER;
	}

	public function getOpenStatus(){
		if(self::getNotStartStatus()){
			return self::NOT_START;
		}elseif(self::getEndStatus()){
			return self::END_AC;
		}

		return self::ON;
	}

	//格式化开始时间
	public function getStartTime(){
		$result = array();
		$time = self::$activityTime['stime'];
		$result['month'] = date('m',strtotime($time));
		$result['day'] = date('d',strtotime($time));
		$result['hour'] = date('H',strtotime($time));
		$result['minute'] = date('i',strtotime($time));

		return $result;
	}

	//格式化开始时间
	public function getEndTime(){
		$result = array();
		$time = self::$activityTime['etime'];
		$result['month'] = date('m',strtotime($time));
		$result['day'] = date('d',strtotime($time));
		$result['hour'] = date('H',strtotime($time));
		$result['minute'] = date('i',strtotime($time));
		
		return $result;
	}
	
	//未开始
	public static function getNotStartStatus(){
		if(time()<strtotime(self::$activityTime['stime'])){
			return true;
		}
	}
	
	//结束活动状态
	public static function getEndStatus(){
		if(time()>strtotime(self::$activityTime['etime'])){
			return true;
		}
	}
	
	//无货-已抢光
	private function getNoGoodsStatus($p_count){
		$num = $this->checkStreamAllNum();
		if($p_count - $num > 0){
			return false;
		}else{
			return true;
		}

	}

	//检查流量包是否已经抢完
	public function checkStreamAllNum(){
		$where = array();
		$where['package_code'] = $this->streamCode;
		$where['r_type'] = self::RTYPE;
		$where['status'] = 1;
		$where['ctime<=']=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
		$where['ctime>=']=mktime(0,0,0,date('m'),date('d'),date('Y'));
		$count = $this->getStreamModel()->getOrderPacageCount($where);

		return $count;
	}
        
	private static $getAll;
	//得到所有流量包
	private function getPackageAll(){
		if(!empty(self::$getAll)){
            return self::$getAll;
        }

		$list = $this->getPanicBuy()->getAll();
		$where = array();
		foreach ($list as $key => $value) {
			array_push($where, $value['stream_id']);
		}
		$res = $this->getStreamProduct()->getStreamCodeList(array("id" => $where));
		$rs  = array();
		foreach ($res as $key => $val) {
			array_push($rs,$val['stream_code']);
		}

		return self::$getAll = $rs;
	}

	private static $day_countList;//天
	private static $month_countList;//天

	//机会已用完
    public function getPackagesCount(){
        $countDay = 0;//天
        $countMOnth = 0;//月

        $key = $this->getCacheKey($this->getDayRange());
		if(isset(self::$day_countList[$key])){
			$countDay = self::$day_countList[$key];
		}else{
			$countDay = self::$day_countList[$key] = $this->getStreamProduct()->getOrderPacageCount($this->getDayRange());
		}

		$key_mon = $this->getCacheKey($this->getMonhtRange());
		if(isset(self::$month_countList[$key_mon])){
			$countMOnth = self::$month_countList[$key_mon];
		}else{
			$countMOnth = self::$month_countList[$key_mon] = $this->getStreamProduct()->getOrderPacageCount($this->getMonhtRange());
		}

        if($countDay >= self::SUMDAY || $countMOnth >= self::SUMMONTH){
            return true;
        }
        return false;
        
    }

    //机会已用完---order
    public function getPackagesOrder(){
        $countDay = 0;//天
        $countMOnth = 0;//月
        $key = $this->getCacheKey($this->getDayRange());
		if(isset(self::$day_countList[$key])){
			return self::$day_countList[$key];
		}else{
			$countDay = self::$day_countList[$key] = $this->getStreamProduct()->getOrderPacageCount($this->getDayRange());
		}

		$key_mon = $this->getCacheKey($this->getMonhtRange());
		if(isset(self::$month_countList[$key_mon])){
			return self::$month_countList[$key_mon];
		}else{
			$countMOnth = self::$month_countList[$key_mon] = $this->getStreamProduct()->getOrderPacageCount($this->getMonhtRange());
		}

        if($countDay >= self::SUMDAY || $countMOnth >= self::SUMMONTH){
            throw new Exception("机会已用完");
        }
        
    }

    private function getCacheKey($where){
		return md5(json_encode($where));
	}

    //得到当天时间范围
    private function getDayRange(){
    	$where = array();
    	$where['package_code'] = $this->getPackageAll();

    	$where['phone'] = $this->phone;
        $where['r_type'] = self::RTYPE;
        $where['status'] = '1';
        $where['ctime >'] = mktime(0,0,0,date('m'),date('d'),date('Y'));
		$where['ctime <='] = mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;

		return $where;
    }

    //得到当月时间范围
    private function getMonhtRange(){
    	$where = array();
    	$where['package_code'] = $this->getPackageAll();
    	$where['phone'] = $this->phone;
        $where['r_type'] = self::RTYPE;
        $where['status'] = '1';
       	$where['ctime >='] = mktime(0,0,0,date('m'),1,date('Y'));

		return $where;
    }

    //得到表的流量编码
    private function getPackageCode($stream_id){
        $row = $this->getStreamProduct()->getRow(array('id'=>$stream_id));
        if(empty($row)){
            return false;
        }

        return $row['stream_code'];
    }

	//得到PanicBuy
	private function getPanicBuy(){
		return \Model\PanicBuy::instance();
	}

	//得到StreamProduct
	private function getStreamProduct(){
		return \Model\StreamProduct::instance();
	}
	
	//获取model stream 
	private function getStreamModel(){
		return \Model\StreamProduct::instance();
	}
}

?>
