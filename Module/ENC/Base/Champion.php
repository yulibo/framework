<?php
//冠军赛程处理流程
namespace Module\ENC\Base;

class Champion extends Base{
	
	//分享---获取球,添加日志
	protected function shareBallBase(){
		try{
			$data = array(
				'userId' => $this->userId,
				'source' => \Model\ECup\EuropeanCup::SOURCE_2,
				'gameType' => \Model\ECup\EuropeanCup::GAME_3,
				'field' => 'gold_ball_num',
				'rmark' => '分享获取金球一枚'
			);
			return $this->operationBall($data);
		}catch(\Exception $e){
			$this->setException($e);
		}
	}
	
	//添加球
	protected function operationBall($data){
		if(empty($data)){
			throw new Exception('数据不能为空');
		}
		return  \Model\ECup\EuropeanCup::instance()->operationBall($data);
	}
	
	//购买流量添加球
	public function buyStreamGiveBall(){
		try{
			$data = array(
				'userId' => $this->userId,
				'source' => \Model\ECup\EuropeanCup::SOURCE_3,
				'gameType' => \Model\ECup\EuropeanCup::GAME_3,
				'field' => 'gold_ball_num',
				'rmark' => '购买流量包获取金球一枚'
			);
			return $this->operationBall($data);
		}catch(\Exception $e){
			$this->setException($e);
		}
	}
	
	//最后一次分享球的时间
	public function shareBallTime(){
		return \Model\ECup\EuropeanCupBallLog::instance()->shareBallTime($this->userId,\Model\ECup\EuropeanCup::GAME_3,\Model\ECup\EuropeanCup::SOURCE_2);
	}

	
	//获取小组列表
	public function getTeamList(){
		return  \Model\ECup\EuropeanCupList::instance()->getListByTrun(\Model\ECup\EuropeanCup::GAME_3,$this->userId,array('trun_3'=>1));
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
		$this->ballNowNum = $res['gold_ball_num'] - $res['use_gold_ball_num'];
		return $res['gold_ball_num'];
	}
	
	
	//下注 -- 扣减 剩余球数量 ,添加下注记录
	protected function betBallBase($ballTeamId){
		try{
			$data = array(
				'userId' => $this->userId,
				'ballTeamId' => $ballTeamId,
				'gameType' => \Model\ECup\EuropeanCup::GAME_3,
				'rmark' => '投注扣减金球一枚'
			);
			return  \Model\ECup\EuropeanCup::instance()->betBallBase($data);
		}catch(\Exception $e){
			$this->setException($e);
		}
	}
	
		
	//检查用户是否有下注
	protected function checkBetBall($ballTeamId){
		return \Model\ECup\EuropeanCupUserSupport::instance()->getRow(array('user_id'=>$this->userId,'type'=>\Model\ECup\EuropeanCup::GAME_3,'suppor_country'=>$ballTeamId));
	}
	
	//送球
	protected function giveBallBase(){
		try{
			$data = array(
				'userId' => $this->userId,
				'source' => 1,
				'gameType' => \Model\ECup\EuropeanCup::GAME_3,
				'field' => 'gold_ball_num',
				'login_field' => 'gift_gold',
				'rmark' => '登录用户送金球一枚'
			);
			return  \Model\ECup\EuropeanCup::instance()->loginGiveBall($data);
		}catch(\Exception $e){
			$this->setException($e);
		}
	}
	
	
	//获取最大得球的数量
	public function getlimitNum(){
		return 2;
	}
	
}