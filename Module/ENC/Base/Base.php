<?php
//欧洲杯
namespace Module\ENC\Base;

abstract class Base{
	
	protected $ballNum;//当前用户球的数量
	protected $phone;//用户电话号码
	protected $userId;//用户ID
	protected $limitNum;//限制用户最多球的数量
	public $ballNowNum;//当前用户剩余球的数量
	public $ballForWobeiNum;//当前用户沃贝兑换铜球的数量
	public $err;//错误信息
	public $code;//错误码
	protected $isOpen;//是否开启活动
	
	const LOGIN_BALLNUM = 1;//登录用户送球的数量
	const SHARE_BALLNUM = 1;//分享送球数量，每天
	const BETBALL_NUM = 1;//下注抵扣球数量
	
	
	
	public function __construct(){
		$this->init();//初始化
	}
	
	
	//分享
	abstract protected function shareBallBase();
	
	
	//最后一次分享球的时间
	abstract public function shareBallTime();
	

	//获取小组列表
	abstract public function getTeamList();
	
	
	//获取推荐流量包
	abstract public function getStreamList();
	
	
	//获取用户当前有多少球
	abstract public function getBallNum();
	
	
	//下注 -- 扣减 剩余球数量 ,添加下注记录
	abstract protected function betBallBase($ballTeamId);
	
		
	//检查用户是否有下注
	abstract protected function checkBetBall($ballTeamId);
	
	
	//送球
	abstract protected function giveBallBase();
	
	
	//获取最大得球的数量
	abstract public function getlimitNum();
	

	//初始化
	public function init(){
		$this->phone = $_SESSION['user_info']['user_phone'];//用户电话号码
		$this->userId = $_SESSION['user_info']['user_id'];//用户ID
		$this->ballNum = $this->getBallNum(); //当前球的数量
		$this->limitNum = $this->getlimitNum();  //获取最大得球的数
	}
	
	//设置异常
	public function setException($e){
		if($e instanceof ENCException){
			$this->err = $e->getMessage();
			$this->code = $e->getCode();
		}
	}
	
	//检查活动是否开启
	private function checkActivity(){
		$this->isOpen = \Module\ENC\EuropeanCup::instance()->getActivitIsOpen(); //检查活动是否开启
		if(empty($this->isOpen)){
			throw new ENCException('活动已经结束');
		}
		$b = \Module\Common::isChinaUnicomPhone($this->phone);
		if(empty($b)){
			throw new ENCException('不是四川联通用户');
		}
	}
	
	
	//分享球
	public function shareBall(){
		try{
			$this->checkActivity(); //检查活动状态
			if($this->ballNum>=$this->limitNum){
				throw new ENCException('球的数量超额');
			}
			$now = strtotime(date('Y-m-d',time()));//当前时间
			$shareTime = strtotime(date('Y-m-d',$this->shareBallTime())); //最后分享时间
			if($shareTime>=$now){
				throw new ENCException('今日已经分享');
			}
			return $this->shareBallBase();//分享球
		}catch(ENCException $e){
			$this->setException($e);
			return false;
		}
	}
	
	//下注内部实现
	public function betBall($ballTeamId){
		try{
			if(!is_array($ballTeamId)){
				throw new ENCException('参数不正确');
			}
			$count = count($ballTeamId) * self::BETBALL_NUM;
			$this->checkActivity(); //检查活动状态
			if($this->ballNowNum<$count){
				throw new ENCException('球的数量不够');
			}
			if($this->checkBetBall($ballTeamId)){
				throw new ENCException('不能对同一只球队重复下注');
			}
			$this->betBallBase($ballTeamId);
			return true;
		}catch(ENCException $e){
			$this->setException($e);
			return false;
		}
	}
	
	//送用户球基础方法
	public function loginGiveBall(){
		try{
			$this->checkActivity(); //检查活动状态
			if(empty($this->userId)){
				throw new ENCException('用户不存在');
			}
			if($this->ballNum>=$this->limitNum){
				throw new ENCException('球的数量超额');
			}
			return $this->giveBallBase(); //送球
		}catch(ENCException $e){
			$this->setException($e);
			return false;
		}
	}
	
	
}