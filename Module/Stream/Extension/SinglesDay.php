<?php

namespace Module\Stream\Extension;

use \Exception;
use \Module\Stream\Extension\Status\SinglesDayStatus;
use \Module\Stream\Extension\StreamLimit\SinglesDay as SinglesDayLimit;
use \Module\Services\WobeiProgress as WobeiProgress;

//双十一
class SinglesDay {

    public $err; //错误信息
    public $code; //错误码
    private $userPhone; //用户电话
    private $userId; //用户ID
    private $limitDragonBoat; //限制对象

    private function __construct() {
        $this->userPhone = $_SESSION['user_info']['user_phone']; //用户电话 
        $this->userId = $_SESSION['user_info']['user_id']; //初始化用户ID 
    }
	
	private static $ins;//对象
	
	public static function getIns(){
		if(!empty(self::$ins)){
			return self::$ins;
		}
		return self::$ins = new self();
	}
	

    //获取流量列表
    public function getStreamList() {
        return $this->getSinglesDayList()->getStreamList();
    }
	
	
	//获取抽奖次数
    public function getLotteryNum() {
		$where = ['user_id'=>$this->userId];
        $row =  $this->getLotteryModel()->getRow($where);
		return $row['num'];
    }


    //订购流量
    public function orderStream($streamCode) {
        try {
			$this->checkActivityStatus();//检查活动状态
			$this->streamCode = $streamCode;
            $this->getSinglesDayLimit()->orderStream($streamCode);
			$this->orderSuc();//订购成功送抽奖机......
            return true;
        } catch (Exception $e) {
            $this->err = $e->getMessage();
            $this->code = $e->getCode();
            return false;
        }
    }
	
	const NOTSTART = -1;//活动未开始状态
	const OVER = -2;//活动已经结束状态
	
	//获取活动状态
	public function getActivityStatus(){
		if(SinglesDayStatus::getNotStartStatus()){
			return self::NOTSTART;
		}
		if(SinglesDayStatus::getEndStatus()){
			return self::OVER;
		}
		return 0;
	}
	
	//检查活动状态
	private function checkActivityStatus(){
		$status = $this->getActivityStatus();//获取活动状态
		if($status==self::NOTSTART){
			throw new Exception('活动未开始');
		}
		if($status==self::OVER){
			throw new Exception('活动已结束');
		}
	}
	
	
	//获取沃贝module处理
	private function getWobeiModule(){
		return \Module\Api\Wobei::instance();
	}
	
	
	 //订购优惠
    public function orderPre($id) {
		$where = ['id'=>$id,'user_id'=>$this->userId];
		$row = $this->getPreModel()->getPreRow($where);
        try {
			if(empty($row)){
				throw new Exception('优惠记录不存在');
			}
			$row['present_price'] = intval($row['present_price']);
			//检查沃贝
			if($this->checkWobei($row['present_price'])){
				//冻结沃贝
				$code = $this->getWobeiModule()->freezeWobei(array('phone'=>$this->userPhone,'wobeiNum'=>$row['present_price']));
				if(1 != $code){
					throw new Exception('沃贝冻结失败');
				}
			}
			$this->streamCode = $row['promo_code'];
            $this->getPreLimit()->orderStream($this->streamCode);
			$this->updatePreStatus($id);//修改用户优惠状态
			if(!empty($row['present_price']) && $row['present_price']!='0.00'){
				WobeiProgress::deductWobei($this->userId,$this->userPhone,$row['present_price'],'兑换流量包扣减沃贝','','y');
			}
            return $row;
        } catch (Exception $e) {
			//检查沃贝
			if($this->checkWobei($row['present_price'])){
				//解冻用户
				$this->getWobeiModule()->optThawWobei(array('phone'=>$this->userPhone,'wobeiNum'=>$row['present_price']));
			}
            $this->err = $e->getMessage();
            $this->code = $e->getCode();
            return false;
        }
    }
	
	//检查我的优惠，沃贝是否存在
	private function checkWobei($wobei){
		return (!empty($wobei) );
	}
	
	//修改优惠状态
	private function updatePreStatus($id){
		return $this->getPreModel()->updateUserPre(['status'=>1],['id'=>$id,'user_id'=>$this->userId]);
	}
	
	
	//抽奖
    public function lottery() {
        try {
			$this->checkActivityStatus();//检查活动状态
			$id = $this->getLotteryModule()->lotteryOpt();
			$err = $this->getLotteryModule()->err;
			if($err){
				throw new Exception($err);
			}
            return $id;
        } catch (Exception $e) {
            $this->err = $e->getMessage();
            return false;
        }
    }
	
	
	//获取用户抽奖机会
	public function getUserLottery(){
		return  $this->getLotteryModule()->getUserLottery();
	}
	
	
	private $streamCode;//当前订购的流量码
	
	//订购成功送抽奖机会,日租包订购机会,90沃贝兑换1G流量包
	public function orderSuc(){
		$this->sendCoupon();
	}

	const SDAY_TYPE = 16;//购买流量包赠送优惠券类型
	//添加优惠券
	private function sendCoupon(){
		$rows  =  $this->getStreamPreRows();//获取流量产品
		foreach($rows as $row){
			$data = [
				"user_id"=>$this->userId,
				"bid"=>0,
				"price_id"=>0,
				"pid"=>0,
				"present_price"=>intval($row['wobei']),
				"original_price"=>$row['price'],
				"initial_price"=>0,
				"target_price"=>0,
				"amount"=>1,
				"phone"=>$this->userPhone,
				"perferen_price"=>0,
				"promo_code"=>$row['promo_code'],
				"status"=>0,
				"type"=>self::SDAY_TYPE,
				"create_time"=>time(),
				"effective_time" => $row['effective_time'],
				"start_time"=>time(),
				"outer_url"=>'',
				"download_url"=>trim($row['image']), //图片地址
				"image_id"=>0,
				"title"=>$row['name'],
				"info" =>$row['info']
			];
			 $this->getPreModel()->sendCoupon($data);
		}
		return true;
	}
	
	//获取中奖日志
	public function getPrizeLog($where){
		return $this->getLotteryModel()->getPrizeLog($where);
	}
	
	//获取要赠送的流量包
	private function getStreamPreRows(){
		return $this->getLotteryModel()->getStreamPreAll(['stream_code'=>$this->streamCode]);
	}
	
	//获取添加优惠model
	private function getPreModel(){
		return \Model\Lottery::instance();
	}
	
    //获取订购记录model
    private function getOrderStreamModel() {
        return \Model\StreamProduct::instance();
    }

	 //获取抽奖model
    private function getLotteryModel() {
        return  \Model\SinglesDay\Lottery::instance();
    }

    //获取model list
    private function getSinglesDayList() {
        return new \Module\Stream\Extension\StreamList\SinglesDay();
    }
	
	//获取抽奖module
	private function getLotteryModule(){
		return \Module\Lottery\Opportunity::getIns();
	}
	
	//获取model limit
    private function getPreLimit() {
        return new \Module\Stream\Extension\StreamLimit\Preferen();
    }
	
	

    //获取model limit
    private function getSinglesDayLimit() {
        if (!empty($this->limitDragonBoat)) {
            return $this->limitDragonBoat;
        }
        return $this->limitDragonBoat = new \Module\Stream\Extension\StreamLimit\SinglesDay();
    }

}

?>
