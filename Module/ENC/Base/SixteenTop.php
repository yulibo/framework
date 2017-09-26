<?php
//16强处理流程
namespace Module\ENC\Base;

class SixteenTop  extends Base{
	const STREAM_TYPE = 1 ;//16强流量包type
	const SOURCE_1 = 1 ;//登录得球标识
	const SOURCE_2 = 2 ;//分享得球标识
	const TYPE = 1; //16强
	//分享
	public function shareBallBase(){

		$data = array(
			'userId' => $this->userId,
			'source' => self::SOURCE_2,
			'gameType' => self::TYPE,
			'field' => 'copper_ball_num',
			'rmark' => '分享获取铜球一枚'
		);

		return  \Model\ECup\SixteenTop::instance()->operationBall($data);
	}

	//添加球
	protected function operationBall($data){
		if(empty($data)){
			throw new Exception('数据不能为空');
		}
		return  \Model\ECup\SixteenTop::instance()->operationBall($data);
	}

	public function buyBallForWobei($data){
		if(empty($data)){
			throw new Exception('数据不能为空');
		}
		return  \Model\ECup\SixteenTop::instance()->operationBall($data);
	}

	//购买流量添加球
	public function buyStreamGiveBall(){
		try{
			$data = array(
				'userId' => $this->userId,
				'source' => \Model\ECup\EuropeanCup::SOURCE_3,
				'gameType' => \Model\ECup\EuropeanCup::GAME_1,
				'field' => 'copper_ball_num',
				'rmark' => '购买流量包获取铜球一枚'
			);
			return $this->operationBall($data);
		}catch(\Exception $e){
			$this->setException($e);
		}
	}
	//最后一次分享球的时间
	public function shareBallTime(){
		return \Model\ECup\EuropeanCupBallLog::instance()->shareBallTime($this->userId,self::TYPE,\Model\ECup\EuropeanCup::SOURCE_2);
	}

	//获取小组列表
	public function getTeamList(){
		$res =  \Model\ECup\EuropeanCupList::instance()->getListByTrun(\Model\ECup\EuropeanCup::GAME_1,$this->userId);
		foreach($res as &$v){
			$reslut[$v['trun_1_group']][] = $v;
		}
		return $reslut;
	}
	
	//获取推荐流量包
	public function getStreamList(){
		return \Model\ECup\EuropeanCupStream::instance()->getAllStreamList();
	}
	
	
	//获取用户当前有多少球
	public function getBallNum(){
		if(empty($this->userId)){
			return 0;
		}
		$res = \Model\ECup\EuropeanCupBall::instance()->getBallNum($this->userId);
		$this->ballNowNum = $res['copper_ball_num'] - $res['use_copper_ball_num'];
		$this->ballForWobeiNum = $res['buy_copper_use_wobei'];
		return $res['copper_ball_num'];
	}
	
	
	//下注 -- 扣减 剩余球数量 ,添加下注记录
	public function betBallBase($ballTeamId){
		$data = array(
			'userId' => $this->userId,
			'gameType' => self::TYPE,
			'ballTeamId' => $ballTeamId,
			'rmark' => '投注扣减铜球一枚'
		);
		return  \Model\ECup\EuropeanCup::instance()->betBallBase($data);
	}
	
		
	//检查用户是否有下注
	public function checkBetBall($ballTeamId){
		$userId = $this->userId;
		$data = array(
			'user_id' => $userId,
			'suppor_country' => $ballTeamId,
			'type' => self::TYPE,
		);
		return \Model\ECup\EuropeanCupUserSupport::instance()->getRow($data);
	}

	//送球
	protected function giveBallBase(){
		try{
			$data = array(
				'userId' => $this->userId,
				'source' => 1,
				'gameType' => \Model\ECup\EuropeanCup::GAME_1,
				'field' => 'copper_ball_num',
				'login_field' => 'gift_copper',
				'rmark' => '登录用户送铜球一枚'
			);
			return  \Model\ECup\EuropeanCup::instance()->loginGiveBall($data);
		}catch(\Exception $e){
			$this->setException($e);
		}
	}
	
	
	//获取最大得球的数量
	public function getlimitNum(){
		return 5;
	}
}