<?php
//欧洲杯处理流程
namespace Module\ENC;

use Module\ENC\Base\SixteenTop;
use Module\ENC\Base\FourTop;
use Module\ENC\Base\Champion;

class EuropeanCup{
	
	const SIXTEENTOP = 'SixteenTop'; //16强
	const FOURTOP = 'FourTop'; //4强
	const CHAMPION = 'Champion'; //冠军赛
	
	public $gameTime = array(
		'SixteenTop'=>array('begin_time'=>'2016-6-10','end_time'=>'2016-6-22'),
		'FourTop'   =>array('begin_time'=>'2016-6-23','end_time'=>'2016-7-4'),
		'Champion'  =>array('begin_time'=>'2016-7-4','end_time'=>'2016-7-10')
	);//比赛时间

	public static $ins;//对象本身
	
	private $gameAda;//赛事
	
	private function __construct(){
		$this->gameAda = self::getGameAda($this->getCurrentLevel());
	}
	
	public static function instance(){
		if(!empty(self::$ins)){
			return self::$ins;
		}
		self::$ins = new self();
		return self::$ins;
	}

	//获取比赛阶段
	public static function getGameAda($level){
		switch ($level) {
            case 'SixteenTop':
                $gameAda = new SixteenTop();
				break;
            case 'FourTop':
                $gameAda = new FourTop();
				break;
			case 'Champion':
                $gameAda = new Champion();
				break;
            default:
                trigger_error('try get undefined property: '.$level.' of class '.__CLASS__, E_USER_NOTICE);
                break;
        }
		return $gameAda;
	}
	
	//获取当前赛事
	public function getCurrentLevel(){
		$time = time();
		foreach($this->gameTime as $key=>$val){
			if($time>=strtotime($val['begin_time']) &&
			$time<strtotime($val['end_time'])){
				return $key;
			}
		}
		return self::SIXTEENTOP;
	}
	
	//获取当前活动是否开启
	public function getActivitIsOpen(){
		$time = time();
		$beginTime = $this->gameTime[self::SIXTEENTOP]['begin_time']; //开始时间
		$endTime = $this->gameTime[self::CHAMPION]['end_time']; //结束时间
		if($time>=strtotime($beginTime) && $time<strtotime($endTime)){
			return true;
		}else{
			return false;
		}
	}

	//分享球
	public function shareBall(){
		return $this->gameAda->shareBall();
	}
	
	//获取流量
	public function getStreamList(){
		return $this->gameAda->getStreamList();
	}

	//球队下注
	public function betBall($ballTeamId,$act=''){
		try{
			if(!$this->getActivitIsOpen()){
				throw new \Exception('当前活动未开始');
			}
			//判断当前是否在有效赛事期间
			if($act!=$this->getCurrentLevel()){
				throw new \Exception($this->getGameStartEnd($act));
			}
			return $this->gameAda->betBall($ballTeamId);
		}catch(\Exception $e){
			$this->gameAda->err = $e->getMessage();
		}
	}
	
	//获取当前赛事是开始还是结束
	private function getGameStartEnd($act){
		return ($act==self::CHAMPION)?'当前赛事未开始':'当前赛事已结束';
	}

	//送用户球基础方法
	public function giveBall(){
		return $this->gameAda->loginGiveBall();
	}
	
	//获取用户当前有多少个球
	public function getBallNum(){
		return $this->gameAda->ballNowNum;
	}
	
	//购买流量送球
	public function buyStreamGiveBall(){
		return $this->gameAda->buyStreamGiveBall();
	}
	
	
	//获取错误信息
	public function getError(){
		return $this->gameAda->err;
	}
	
	//获取错误编码
	public function getCode(){
		return $this->gameAda->code;
	}
}
