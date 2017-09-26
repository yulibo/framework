<?php
namespace Module\Lottery;

use \Module\Lottery\PrizeType\MaterialObject;
use \Module\Lottery\PrizeType\Bill;
use \Module\Lottery\PrizeType\Stream;
use \Module\Lottery\PrizeType\Wobei;


use \Exception;


abstract class Lottery
{
	public static $prizeClass = [
			1=>'MaterialObject',
			2=>'Bill',
			3=>'Stream',
			4=>'Wobei'];//奖品类型对应class
			
	public $err;//错误信息
		
	protected $userId;//用户ID
	
	private $prizeId;//奖品ID
	
	const PRIZE_OVER = -2;//奖品已经抽完
	
	private static $notPrize = [];//不能抽奖的奖品
	private $fUser = false;//默认用户不是4G用户
	
	const MIN_PRIZE_PP = 1;//最小概率
	const PRIZE_PP = 1000;//剩概率
	
	private function __construct(){
		 $this->userId=$_SESSION['user_info']['user_id'];
		 $this->getUserIs4g();//判断用户是否是4G用户
	}
	
	private static $ins;//
	
	//获取对象本身
	public static function getIns(){
		if(!empty(self::$ins)){
			return self::$ins;
		}
		return self::$ins =  new static();
	}
	
	//检查抽奖机会,并且扣除抽奖机会
	protected function checkLuckyDraw(){
		
	}
	
	private static $billPrize = [3,4,5];//话费奖品ID
	
	//过滤23G用户中话费的概率
	private function filterBill(){
		return !(!$this->fUser && in_array($this->prizeId,self::$billPrize));
	}
	
	
	//判断用户是否是4G用户
	private function getUserIs4g(){
		$phone = $_SESSION['user_info']['user_phone'];
		if(empty($phone)){
			return false;
		}
        $modelUser = \Module\Api\Users::instance();
        $userInfo = $modelUser->getBaseUserInfo($phone);
		if($userInfo['netType'] == '4G'){
			$this->fUser = true;
		}
	}
	
	
	//抽奖
	public function lotteryOpt(){
		try{
			$this->checkLuckyDraw();//抽奖机会检查
			$this->getPrizeIdByRand();//抽奖
			$this->addPrize();//添加奖品
			return $this->getPrizeItemRow();
		}catch(Exception $e){
			$this->err = $e->getMessage();
			return false;
		}
	}
	
	//抽奖随机算法
	private function getPrizeIdByRand() {
		$proArr = $this->proList();//获取奖品
		$result = ''; 
		//概率数组的总概率精度 
		$proSum = array_sum($proArr)*self::PRIZE_PP;
		
		//概率数组循环 
		foreach ($proArr as $key => $proCur) {
			$proCur = $proCur*self::PRIZE_PP;
			$randNum = mt_rand(self::MIN_PRIZE_PP, $proSum);
			if ($randNum <= $proCur) { 
				$result = $key; 
				break; 
			} else { 
				$proSum -= $proCur; 
			}
		}
		unset($proArr);
		if(empty($result)){
			throw new Exception('没有奖品');
		}
		return $this->prizeId = $result; 
	}
	
	
	
	//获得奖品添加到数据库
	protected function addPrizeDb(){
		if(empty($this->prizeId)){
			return false;
		}
		return $this->getLotteryDataModule()->addPrize($this->userId,$this->prizeId);//获取奖品
	}


	
	//获得奖品
	protected function addPrize(){
		$filter = $this->filterBill();//判断是否 是23G用户
		if(empty($filter)){
			$this->getPrizeIdByRand();//计算抽奖算法
			return $this->addPrize();
		}
		$result = $this->addPrizeDb();//插入数据库
		$code = isset($result['result'])?$result['result']:0;
		//-2 奖品已经抽完
		if($code==self::PRIZE_OVER){
			self::$notPrize[] = $this->prizeId;
			$this->getPrizeIdByRand();//计算抽奖算法
			return $this->addPrize();
		}
		if(1!=$code){
			throw new Exception('抽奖失败');
		}
		return $this->getPrizeModule()->addPrize($this->prizeId);//获取奖品
	}


	//获取module
	private function getPrizeModule(){
		$row = $this->getPrizeItemRow();//当前奖品
		$name = self::$prizeClass[$row['type']];
		switch ($name) {
            case 'MaterialObject':
                $gameAda = new MaterialObject();
				break;
            case 'Bill':
                $gameAda = new Bill();
				break;
			case 'Stream':
                $gameAda = new Stream();
				break;
			case 'Wobei':
                $gameAda = new Wobei();
				break;
            default:
                trigger_error('try get undefined property: '.$level.' of class '.__CLASS__, E_USER_NOTICE);
                break;
        }
		return $gameAda;
	}
	
	//获取单个奖品
	private function getPrizeItemRow(){
		return $this->getLotteryDataModule()->getPrizeItemRow($this->prizeId);
	}
	
	
	//获取奖品列表
	private function proList(){
		$list = $this->getLotteryDataModule()->proList();//获取奖品
		$proArr = [];
		foreach ($list as $key => $val) {
			//去除自己不能抽奖的奖品
			if(!in_array($val['id'],self::$notPrize) ){
				$proArr[$val['id']] = $val['probability']; 
			}
			if(!$this->fUser && in_array($val['id'],self::$billPrize) && isset($proArr[$val['id']])){
				unset($proArr[$val['id']]);
			}
		}
		//没有奖品 提示未中奖
		if(!empty($list) && empty($proArr)){
			throw new Exception('没有奖品');
		}
		return $proArr;
	}
	
	//获取lottery_item model
	public function getLotteryDataModule(){
		return LotteryData::getIns();
	}
}
?>