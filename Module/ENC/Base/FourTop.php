<?php
//4强处理流程
namespace Module\ENC\Base;

class FourTop extends Base{

	const MAX_SHARE_NUM = 2;//最多分享次数
	private $shareGetSilver;//分享获取球的数量
	
	//分享---获取球,添加日志
	protected function shareBallBase(){
		try{
			if($this->shareGetSilver>=self::MAX_SHARE_NUM){
				throw new ENCException('已经达到最大分享次数');
			}
			$data = array(
				'userId' => $this->userId,
				'source' => \Model\ECup\EuropeanCup::SOURCE_2,
				'gameType' => \Model\ECup\EuropeanCup::GAME_2,
				'field' => 'silver_ball_num',
				'rmark' => '分享获取银球一枚'
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
				'gameType' => \Model\ECup\EuropeanCup::GAME_2,
				'field' => 'gold_ball_num',
				'rmark' => '购买流量包获取银球一枚'
			);
			return $this->operationBall($data);
		}catch(\Exception $e){
			$this->setException($e);
		}
	}
	
	
	//最后一次分享球的时间
	public function shareBallTime(){
		return \Model\ECup\EuropeanCupBallLog::instance()->shareBallTime($this->userId,\Model\ECup\EuropeanCup::GAME_2,\Model\ECup\EuropeanCup::SOURCE_2);
	}

	//获取小组列表
	public function getTeamList(){
		$res =  \Model\ECup\EuropeanCupList::instance()->getListByTrun(\Model\ECup\EuropeanCup::GAME_2,$this->userId,array('trun_2'=>1));
        foreach($res as &$v){
            $reslut[$v['trun_2_group'][0]][] = $v;
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
		$this->ballNowNum = $res['silver_ball_num'] - $res['use_silver_ball_num'];
		$this->shareGetSilver = $res['share_get_silver'];
		return $res['silver_ball_num'];
	}
	
	
	//下注 -- 扣减 剩余球数量 ,添加下注记录
	protected function betBallBase($ballTeamId){
		try{
			$data = array(
				'userId' => $this->userId,
				'ballTeamId' => $ballTeamId,
				'gameType' => \Model\ECup\EuropeanCup::GAME_2,
				'rmark' => '投注扣减银球一枚'
			);
			return  \Model\ECup\EuropeanCup::instance()->betBallBase($data);
		}catch(\Exception $e){
			$this->setException($e);
		}
	}
	
		
	//检查用户是否有下注
	protected function checkBetBall($ballTeamId){
		return \Model\ECup\EuropeanCupUserSupport::instance()->getAll(array('user_id'=>$this->userId,'type'=>\Model\ECup\EuropeanCup::GAME_2,'suppor_country'=>$ballTeamId));
	}
	
	//送球
	protected function giveBallBase(){
		try{
			$data = array(
				'userId' => $this->userId,
				'source' => 1,
				'gameType' => \Model\ECup\EuropeanCup::GAME_2,
				'field' => 'silver_ball_num',
				'login_field' => 'gift_silver',
				'rmark' => '登录用户送银球一枚'
			);
			return  \Model\ECup\EuropeanCup::instance()->loginGiveBall($data);
		}catch(\Exception $e){
			$this->setException($e);
		}
	}
	
	
	//获取最大得球的数量
	public function getlimitNum(){
		return 4;
	}
}