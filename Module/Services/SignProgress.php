<?php 
namespace Module\Services;
use \Module\Common as Common;
use \Module\Services\WobeiProgress;
use \Module\FreeLottery; 



class SignProgress extends \Core\Lib\ModuleBase{

	public $mob;     // 签到用户手机号
	public $user_id; // 签到用户id
	public $month;   // 当前签到年月
	public $today;
	public $signType; // 签到类型 1为正常签到，2为补签
	public $csn; //当前连续签到天数
	public $targetDay; // 目标时间

	const MAKE_UP_TIME = 5;

	public static $wobeiType = [
		1 => "签到-每日固定赠送沃贝",
		2 => "签到-翻倍赠送沃贝",
		3 => "签到-补签到扣减沃贝",
		4 => "签到-兑换流量扣减沃贝",
		5 => "流量包折价返还沃贝",
	];

	public static $felwareType = [
		1 => "流量包兑换机会",
		2 => "沃贝翻倍机会",
		3 => "电子券",
		4 => "免费抽奖机会",
	];

	public static $makeUpCost = [
		1 => 5,
		2 => 10,
		3 => 20,
		4 => 50,
		5 => 100
	];

	// public static $makeUpCost = [
	// 	1 => 1,
	// 	2 => 1,
	// 	3 => 1,
	// 	4 => 1,
	// 	5 => 1
	// ];
	
	public function __construct($user_id){
		$this->userInit($user_id);
	}

	public function userInit($user_id){
		$user_info = Common::Model("user")->getUserInfo(['id' => $user_id]);
		$this->user_id = $user_id;
		$this->month = date("Y-m");
		$this->mob = $user_info['phone'];
		$this->today = date("d");

	}

	public function setSignType($signType){
		$this->signType = $signType;
	}

	public function setTargetDay($day){
		$this->targetDay = $day;
	}

	public function calculateTureSignCnt(){

		$sign_m = Common::Model("sign");
		
		$where['month']     = $this->month;
		$where['member_id'] = $this->user_id;
		$where['make_up between'] = [1,$this->targetDay];

		$_m_sign_list = $sign_m->getSignList($where);

		if(!$_m_sign_list){
			return 0;
		}

		$l_len = count($_m_sign_list);

		if($_day - (int)$_m_sign_list[ $l_len - 1 ]['day'] > 1 ){
			return 0;
		}

		$csn = 0;
		$last_day = 0;
		
		for ($i = 0; $i < $l_len ; $i++) { 
			if($last_day +1 == (int)$_m_sign_list[$i]['day']){
				$csn++;	
			}else{
				$csn = 1;
			}
			$last_day = (int)$_m_sign_list[$i]['day'];
		}

		$this->csn = $csn;
		return $csn;

	}

	public function addSignLog($date){
		$ret['sta'] = -1;
		$ret["msg"] = "";

		if(empty($this->user_id)){
			$ret['msg'] = "用户id不能为空";
			return $ret;
		}				

		$where['member_id'] = $this->user_id;
		$signInfo = \Model\Sign::instance()->getUserSign($where);	

		if(!$signInfo){
			$ret['msg'] = "用户没有签到记录，不能补签，请先完成今日签到";
			return $ret;			
		}

		if(trim($date) == ""){
			$ret['msg'] = "补签时间不能为空";
			return $ret;				
		}
		if(!Common::regex_vali($date,"date")){
			$ret['msg'] = "补签时间格式有误";
			return $ret;			

		}	
		
		$_time = date("H:i:s");
		$data['created'] = strtotime($date." ".$_time);

		if($data['created'] > time()){
			$ret['msg'] = "补签时间不能超出当前时间";
			return $ret;			
		}

		$data['member_id'] = $this->user_id;
		$data["welfare"]   = "";
		$data['daily_wobei'] = 2;
		$data['month'] = $this->month;

		$where['member_id'] = $this->user_id;

		$begin = strtotime(date("Y-m-d",$data['created']));
		$end = strtotime(date("Y-m-d",$data['created'])."+1 day") - 1;

		$where['created between'] = [$begin,$end];

		$signInfo = \Model\Sign::instance()->getUserSignLog($where);

		if($signInfo){
			$month = date("m",$data['created']);
			$year=   date("Y",$data['created']);

			$ret['msg'] = "用户当天存在签到记录已存在";
			return $ret;	
		}

		$result            = \Model\Sign::instance()->add_sign_log($data);

		if($result){
			$ret['sta'] = 1;
			$ret['msg'] = "补签成功";
		}else{
			$ret['msg'] = "补签失败";	
		}		
		return $ret;
	}

	public function addSignLog2(){

		$data = [
			"member_id" => $this->user_id,
			"created" => time(),
			"welfare" => "",
			"make_up" => $this->targetDay,
			"type"    => $this->signType,
			"month"   => $this->month,
		];

		$result            = \Model\Sign::instance()->add_sign_log($data);

		if($result){
			$ret['sta'] = 1;
			$ret['msg'] = "补签成功";
		}else{
			$ret['sta'] = 0;
			$ret['msg'] = "补签失败";	
		}		
		return $ret;


	}

	public function updateCST($cst){
		$sign_m = Common::Model("sign");

		$signInfo = $sign_m -> getUserSign(["member_id" => $this->user_id]);
		if(!$signInfo){
			return false;
		}
		$old_cst = $signInfo['contine_time'];
		if($old_cst == $cst){
			return true;
		}
		return $sign_m -> updateCST(["member_id" => $this->user_id],$cst);
	}

	public function makeUpWobei(){
		// $sign_opt
		

	}

	public function signInit(){
		$isUserSigned = $this->isUserSigned();

		if(!$isUserSigned){
            $data['member_id']      = $this->user_id;
            $data['last_sign_time'] = 0;
            $data['create_time']    = time();
            $data['contine_time']   = 0;
            Common::Model("sign")->addSign($data);  
		}
	}

	public function isUserSigned(){
		return Common::Model("sign")->signRowByID($this->user_id);
	}


	public function isSigned($month,$day){

		$where['member_id'] = $this->user_id;
		$_today_begin = strtotime(date("{$month}-{$day}"));

		$_today_end = $_today_begin + 24*60*60 - 1;
		$where['created >'] = $_today_begin;
		$where['created <'] = $_today_end;
		$where['make_up'] = $this->today;
		return Common::Model("sign")->getUserSignLog($where);
	}

	public function isMakeUped($month,$day){
		$where['member_id'] = $this->user_id;
		$where['make_up'] = $day;
		// var_dump($where);die();
		$_month_begin = strtotime(date("{$month}"));
		$_month_end = strtotime(date("{$month}")." +1 month") - 1;
		$where['created >'] = $_month_begin;
		$where['created <'] = $_month_end;

		return Common::Model("sign")->getUserSignLog($where);		
	}


	public function sign(){

		$ret['status'] = 0;
		$ret['gotourl'] = '';
		$ret['msg'] = '签到失败';
		// 获取今日签到记录

		$this->setTargetDay($this->today);


		$isSigned = $this->isSigned($this->month,$this->today);

		if($isSigned){
			$ret['status'] = 0;
			if($this->signType == 1){
				$ret['msg'] = '抱歉您今日已经签到过';	
			}
			return $ret;
		}

		$addSignLog = $this->addSignLog2();
		if(!$addSignLog){
			return $ret;
		}
		$this->calculateTureSignCnt();


		$getdailyWoebi = $this->getDailyWobei();

		if(!$getdailyWoebi){
			return $ret;
		}

		$getFelware = $this->getDayFelwares();

		if($getFelware['sta'] === -1 ){
			$ret['status'] = -1;
			return $ret;		
		}


		$ret['data'] = $getFelware['data'];

		// 更新签到记录
		
		$update = $this -> updateSign();
		if(!$update){
			$ret['status'] = -1;
			return $ret;
		}

		$ret['status'] = 1;
		$ret['msg'] = "签到成功";

		return $ret;
	}

	public function makeUpSign($day = 0){

		$ret['status'] = 0;
		$ret['gotourl'] = '';
		$ret['msg'] = '补签到失败';
		// 获取今日签到记录

		$this->setTargetDay($day);

		if($this->month == "2016-07"){
			$ret['status'] = -1;
			$ret['msg'] = '补签到功能8月份正式开始启用';	
			return $ret; 			
		}

		if($this->targetDay >= $this->today){
			$ret['status'] = -1;
			$ret['msg'] = '只能补签今天之前的签到';	
			return $ret; 			
		}

		$userInfo = $this->userSignInfo(); 
		if($userInfo['remainMakeUp'] <= 0){
			$ret['status'] = -1;
			$ret['msg'] = '抱歉本月补签次数已用完';	
			return $ret; 			
		}

		$makeUpCost = static::$makeUpCost;

		$isMakeUped = $this->isMakeUped($this->month,$this->targetDay);

		if($isMakeUped){
			$ret['status'] = 0;
				$ret['msg'] = "抱歉本月{$this->targetDay}号已经签到过";	
			return $ret;
		}
		
		/*
		$nextWobei = $makeUpCost[$userInfo['alreadyMakeUp'] + 1];
	
		if( $userInfo['wobei'] < $nextWobei ){
			$ret['status'] = 0;
			$ret['msg'] = '沃贝余额不足，无法补签';	
			return $ret;			
		} 

		// 扣除补签沃贝
		$deduct = $this->deductWobei($nextWobei);
		if(!$deduct){
			$ret['status'] = -1;
			return $ret;			
		}
		*/

		$addSignLog = $this->addSignLog2();
		if(!$addSignLog){
			$ret['status'] = -1;
			return $ret;
		}

		$this->calculateTureSignCnt();

		$getdailyWoebi = $this->getDailyWobei();

		if(!$getdailyWoebi){
			return $ret;
		}

		$getFelware = $this->getMultiDayFelwares();

		if($getFelware['sta'] === -1 ){
			$ret['status'] = -1;
			return $ret;		
		}


		$ret['data'] = $getFelware['data'];
		$ret['status'] = 1;
		$ret['msg'] = "补签到成功";

		return $ret;
	}

	public function makeUpSignByAdmin($day = 0){
		$this->setSignType(1);
		$ret['status'] = 0;
		$ret['gotourl'] = '';
		$ret['msg'] = '补签到失败';
		// 获取今日签到记录

		$this->setTargetDay($day);

		if($this->targetDay >= $this->today){
			$ret['status'] = -1;
			$ret['msg'] = '只能补签今天之前的签到';	
			return $ret; 			
		}


		$makeUpCost = static::$makeUpCost;

		$isMakeUped = $this->isMakeUped($this->month,$this->targetDay);

		if($isMakeUped){
			$ret['status'] = 0;
				$ret['msg'] = "抱歉本月{$this->targetDay}号已经签到过";	
			return $ret;
		}

		$nextWobei = $makeUpCost[$userInfo['alreadyMakeUp'] + 1];

		$addSignLog = $this->addSignLog2();
		if(!$addSignLog){
			$ret['status'] = -1;
			return $ret;
		}

		$this->calculateTureSignCnt();

		$getdailyWoebi = $this->getDailyWobei();

		if(!$getdailyWoebi){
			return $ret;
		}

		$getFelware = $this->getMultiDayFelwares();

		if($getFelware['sta'] === -1 ){
			$ret['status'] = -1;
			return $ret;		
		}

		// var_dump($getFelware['data']);
		$ret['data'] = $getFelware['data'];
		$ret['status'] = 1;
		$ret['msg'] = "补签到成功";

		return $ret;
	}


	private function updateSign(){
		$where['member_id'] = $this->user_id;
		$data['last_sign_time'] = time();
		$data['contine_time'] = $this->csn;
		// var_dump($where,$data);
		return Common::Model("sign")->updateSign($data,$where);
	}

	private function getDailyWobei(){
		$ret = Common::Model("sign")->getDailyWobei($this->month);
		
		if(!$ret){
//			$this->req_log(0,static::$wobeiType[$type],"{$this->month}规则未配置正确");
			return false;
		}

		$wobei = (int)$ret['wobei'];
		if($wobei > 0){
			$ret = $this->addSignWobei(1,$wobei);

		}

		if(!$ret){
			return false;
		}
		return true;
	}

	private function deductWobei($wobei){
	
		if($wobei == 0){
			return false;
		}

		$ret = $this->deductSignWobei(3,$wobei);
		if(!$ret){
			return false;
		}

		return true;
	}

	private function deductSignWobei($type,$wobei){
		
		if($wobei == 0){
			return false;
		}

		$ret = WobeiProgress::deductWobei($this->user_id,$this->mob,$wobei,static::$wobeiType[$type]);
		
		if(!$ret){
			$this->req_log(0,static::$wobeiType[$type]."扣减沃贝失败",$type,-$wobei);
			return fasle;
		}

		$this->req_log(1,static::$wobeiType[$type]."扣减沃贝成功",$type,-$wobei);		
		return !!1;		
	}

	private function getTimedWobei($time){
		$ret = Common::Model("sign")->getDailyWobei($this->month);
		
		if(!$ret){
			$this->req_log(0,"{$this->month}规则未配置正确");
			return false;
		}

		$wobei = (int)$ret['wobei'] * $time;
		if($wobei > 0){

			$ret = $this->addSignWobei(2,$wobei);

			if(!$ret){
				return false;
			}
			$ret = [];
			$ret['type'] = 2;
			$ret['time'] = $time;
			$ret['data']['wobei'] = $wobei;
		}

		return $ret;	
	}


	private function addSignWobei($type,$wobei){
		$ret = WobeiProgress::addWobei($this->user_id,$this->mob,$wobei,static::$wobeiType[$type]);
		
		
		if(!$ret){
			$this->req_log(0,static::$wobeiType[$type]."获取失败",$type,$wobei);
			return fasle;
		}

		$this->req_log(1,static::$wobeiType[$type]."获取成功",$type,$wobei);		
		return !!1;
	}

	private function req_log($sta,$msg,$type,$wobei){
		
		$req_data = [
			"type" => $type,
			"wobei" => $wobei,
			"request_status" =>$sta,
			"member_id" => $this->user_id,
			"msg" => $msg,
			"created" =>time()
		];

		return 	Common::Model("sign")->sign_req($req_data);			
	}

	public function getMultiDayFelwares(){
		$sign_m = Common::Model("sign");
		
		$where['member_id'] = $this->user_id;
		$where['get_fel'] = 0;
		// var_dump($this->today);
		$where['make_up between'] = [(int)$this->targetDay,(int)$this->today];
		$where['month'] = $this->month;
		// var_dump($where);die();
		$_m_sign_list = $sign_m->getSignList($where);

		$ret['sta'] = 0;
		$ret['data'] = [];
		
		if(count($_m_sign_list) > 0){
			foreach ($_m_sign_list as $v) {
				$this->setTargetDay($v['day']);
				$this->calculateTureSignCnt();
				$ress = $this->getDayFelwares();

				if($ress['sta'] > 0 && $ress['data'][0] != false){
					$ret['data'] = array_merge($ret['data'] ,$ress['data']);
				}
				
			}		
			$ret['sta'] = 1;	
		}

		return $ret;
	}

	public function getDayFelwares(){
		$ret['sta'] = -1;

		$felwares = Common::Model("sign")->getDayFelwares($this->month,$this->targetDay);

		if(!$felwares){
			$ret['sta'] = 0;
			return $ret;			
		}
		$ret['data'] = [];
		foreach ($felwares as $k => $v) {
			$ret['data'][] = $this->felwareHandle($v);
		}
		
		$data['get_fel'] = 0;
		if($ret['data'][0]){
			$data['get_fel'] = 1;	
		}
		
		$where['make_up'] = $this->targetDay;
		$where['month'] = $this->month;
		$where['member_id'] = $this->user_id;
		
		$update = Common::Model("sign")->updateSignLog($data,$where);
		$ret['sta'] = 1;
		return $ret;
	}

	private function felwareHandle($felware){

		if($this->csn < $felware['csd']){
			return false;
		}
		switch ($felware['felware_type']) {
			case 1:
				return $this->getStream($felware['type_id']);
				break;
			case 2:
				return $this->getWobei($felware['type_id']);
				break;
			case 3:
				return $this->getCoupon($felware['type_id']);
				break;
			case 4:
				return $this->getLottery($felware['type_id']);
				break;							
			default:
				return false;
				break;
		}
	}


	private function getStream($sf_id){
		$streamFelwareInfo = Common::Model("sign")->getStreamFelwareInfo(["a.id" => $sf_id]);

		$_data = [
			"user_id"=>$this->user_id,
			"bid"=>0,
			"price_id"=>0,
			"pid"=>0,
			"present_price"=>$streamFelwareInfo['cost_wobei'],
			"original_price"=>0,
			"initial_price"=>0,
			"target_price"=>0,
			"amount"=>1,
			"phone"=>$this->mob,
			"perferen_price"=>0,
			"promo_code"=>$streamFelwareInfo['sp_id'],
			"status"=>0,
			"type"=>11,
			"create_time"=>time(),
			"effective_time"=> strtotime("+3 month"),
			"start_time"=> time(),
			"outer_url"=> "",
			"image_id"=> $streamFelwareInfo['show_img'],
			"title"=> $streamFelwareInfo['name'],
			"info" => $streamFelwareInfo['info'],
		];
		
		$sendRs = Common::Model("coupon")->sendCoupon($_data);
	
		if(!$sendRs){

			return false;
		}
		$rs['type'] = 1;
		$rs['data']['streamInfo']['name'] = $streamFelwareInfo['name'];
		$rs['data']['streamInfo']['id'] = $sendRs; 
		return $rs;
	}

	private function getWobei($wf_id){
		// var_dump($wf_id)
		$wobeiFelwareInfo = Common::Model("sign")->getWobeiFelwareInfo(["id"=>$wf_id]);
		if(!$wobeiFelwareInfo){
			return false;
		}
		$opt = json_decode($wobeiFelwareInfo['opt'],true);

		if(count($opt) <=0){
			return false;
		}

		$data = $this->getRand($opt);
		$ret = $this->getTimedWobei($data['time']);

		return $ret;
	}

	private function getRand($opt){
		$proSum = 0;
		foreach ($opt as $v) {
			$proSum += $v['rate'];
		}

		foreach ($opt as $k => $v) {
			$randNum = mt_rand(1,$proSum);
	        if ($randNum <= $v['rate']) {
	            $data = $opt[$k];
	            break;
	        } else {
	            $proSum -= $v['rate'];
	        }			
		}

		return $data;
	}


	private function getCoupon($coupon_batch_id){
		$coupon_m = Common::Model("coupon");
		$coupon_m->db->write()->begintransaction();
		$last_coupon_info = $coupon_m-> get_last_coupon_info($coupon_batch_id);

		if(!$last_coupon_info){
			$coupon_m->db->write()->rollback();
			return false;
		}

		$locked = $coupon_m->lock_coupon_code($last_coupon_info['id'],$this->user_id);

		if(!$locked){
			$coupon_m->db->write()->rollback();
			return false;
		}

		// var_dump($li_info['coupon_batch']);die();

		if(!$last_coupon_info['outer_href']){
			$last_coupon_info['outer_href'] = "";
		}

		$_data = [
			"user_id"=>$this->user_id,
			"bid"=>0,
			"price_id"=>0,
			"pid"=>0,
			"present_price"=>0,
			"original_price"=>0,
			"initial_price"=>0,
			"target_price"=>0,
			"amount"=>1,
			"phone"=>$this->mob,
			"perferen_price"=>0,
			"promo_code"=>$last_coupon_info['coupon_code'],
			"status"=>0,
			"type"=>10,
			"create_time"=>time(),
			"effective_time"=> $last_coupon_info['end_time'],
			"start_time"=> $last_coupon_info['start_time'],
			"outer_url"=> $last_coupon_info['outer_href'],
			"image_id"=> $last_coupon_info['show_img'],
			"title"=> $last_coupon_info['batch_name'],
			"info" => $last_coupon_info['info']
		];
		
		$sendRs = $coupon_m->sendCoupon($_data);
		if(!$sendRs){
			$coupon_m->db->write()->rollback();
			return false;
		}
		$coupon_m->db->write()->commit();
		$rs['type'] = 3;
		$rs['data']['coupon_info'] = $last_coupon_info; 
	
		return $rs;		

	}

	private function getLottery($cnt){

		if($cnt <=0 ){
			return false;
		}
		for($i = 0;$i<$cnt;$i++){
			$rs = FreeLottery::addSignLottery($this->user_id,$this->month);

			if(!$rs){
				return false;
			}

		}
		$rs = [];
		$rs['type'] = 4;
		$rs['data']['lottery_cnt'] = $cnt; 
	
		return $rs;
	}

	static public function getSignOpt(){
		$sign_m = Common::Model("sign");
		return $sign_opt = $sign_m ->getOpt();
	}

    public function userSignInfo(){

		if(!$this->user_id){
			return false;
		}

		$info['user_id'] = $this->user_id;
		$info['mob'] = $this->mob;
		
		$_month_s = strtotime($this->month);
		$_month_e = strtotime($this->month." +1 month") -1;

		$where = [
			"member_id" => $this->user_id,
			"type"    => 2,
			"created BETWEEN"   =>  [$_month_s,$_month_e]
		];

		$makeUpTime = (int)Common::Model("sign")->getSignLogTime($where);
		// var_dump($makeUpTime);die();
		$info['remainMakeUp'] = self::MAKE_UP_TIME - $makeUpTime;
		$info['alreadyMakeUp'] = $makeUpTime;
		$info['maxMakeUpTime'] = self::MAKE_UP_TIME;
		$info['makeUpCost']  = static::$makeUpCost;
		if(isset($_SESSION['userInfo'])){
			$info['head_image'] = $_SESSION['userInfo']['head_image'];
		}else{
			$info['head_image'] = "";
		}
		

		$where = [
			"member_id" => $this->user_id,
			"created BETWEEN"   =>  [$_month_s,$_month_e]
		];


		$info['isUnicom'] = COmmon::is_ChinaUnicom_mob($this->mob);
		$info['signCnt'] = (int)Common::Model("sign")->getSignLogTime($where);

		$info['wobei'] = (int)WobeiProgress::getUserWobei($this->mob);
		$info['csd'] = $this->calculateTureSignCnt();
		$info['freeLotteryTime'] = (int)FreeLottery::get_free_times($this->user_id);
		$info['isLogin'] = 1;
		return $info;
    }

    public function getDefaultInfo(){


		$info['remainMakeUp'] = 5;
		$info['alreadyMakeUp'] = 0;
		$info['maxMakeUpTime'] = 5;
		$info['makeUpCost']  = static::$makeUpCost;
		$info['isUnicom'] = false;
		$info['signCnt'] = 0;
		$info['wobei'] = 0;
		$info['csd'] = 0;
		$info['freeLotteryTime'] = 0;
		$info['isLogin'] = 0;
		$info['head_image'] = "/Mobile/Template/asset/image/touxiang.png";

    	return $info;
    }

    public function getSignCalendar(){
		$_month_s = strtotime($this->month);
		$_month_e = strtotime($this->month." +1 month") -1;

		$where = [
			"member_id" => $this->user_id,
			"created BETWEEN"   =>  [$_month_s,$_month_e]
		];

		$signLogs = Common::Model("sign")->getSignLogs($where);

		$_currMonthDays = date("d",$_month_e);

		$unsigned = [
			"signed" => 0,
			"makeUp" => 0,
			"felwareType" => 0,
		];
		$signCalendar = [];

		for ($i = 1; $i <= $_currMonthDays ; $i++) { 
			$signCalendar[$i] = $unsigned; 			
		}

		if(count($signLogs)>0){
			foreach ($signLogs as $k => $v) {
				$signCalendar[$v['make_up']]['signed'] = 1;
				if($v['type'] == 2){
					$signCalendar[$v['make_up']]['makeUp'] = 1;
				}
			}			
		}


		$dailyRuleList = Common::Model("sign")->getMonthRulesDetail(['month' => $this->month]);
		// var_dump($dailyRuleList);die();
		if( count($dailyRuleList) > 0 ){
			foreach ($dailyRuleList as $k => $v) {
				$signCalendar[$v['day']]['felwareType'] = $v['felware_type'];
				$signCalendar[$v['day']]['felwareCSD'] = $v['csd'];
			}	
		}

		return $signCalendar;
    }

	public function exchStream($id){

		$ret['sta'] = -1;
		$ret['msg'] = "很抱歉，兑换失败！";	
		$where = [
			'user_id' => $this->user_id,
			'id' => $id,
			'status' => 0,
			'effective_time >= ' =>time() 
		];

		$streamInfo = Common::Model("Sign")->getUserStreamInfo($where);

		if(!$streamInfo){
			$ret['msg'] = "非法参数";
			return $ret;
		}

		$cost_wobei = (int)$streamInfo['present_price'];

		$userInfo = $this->userSignInfo();
		$userWoebi = $userInfo['wobei'];

		if( $userWoebi < $cost_wobei ){
			$ret['sta'] = -1;
			$ret['msg'] = "沃贝余额不足，无法完成兑换";			
		}

		$code = Common::Model("StreamProduct")->getOne(['id' => $streamInfo['promo_code']]);
		if(!$code){
			$ret['msg'] = "未找到对应流量产品";
			return $ret;
		}
		// var_dump($code);die();
		$code = $code['stream_code'];
		$package_name = isset($_REQUEST['packageName'])? trim($_REQUEST['packageName']) : '';
		$data = [
			"phone" => $this->mob,
			"packagecode" => $code,
			"package_name" => $package_name,
			"rType" => 18,
			"modify" => "ding"
		];
		$stream_m = \Module\Api\Stream::instance();

		$res = $stream_m ->transactFlow($data);
		
		if((int)$res !== 1){
			$ret['sta'] = -1;
			$ret['msg'] = $stream_m ->getError();
			return $ret;
		}

		$res = $this->deductSignWobei(4,$cost_wobei);

		if(!$res){
			$ret['sta'] = -1;
			$ret['msg'] = "系统忙请稍后再试";
			return $ret;					
		}

		$data = [
			'status' => 1
		];
		$where = [
			'user_id' => $this->user_id,
			'id' => $id
		];

		$res = Common::Model("sign")->completeStream($data,$where);

		if(!$res){
			$ret['sta'] = -1;
			$ret['msg'] = "系统忙请稍后再试";
			return $ret;					
		}

		$smsInfo = "尊敬的用户您好，您签到获得{$streamInfo['title']}，已经兑换成功。本次消费{$cost_wobei}沃贝。更多签到好礼请持续关注优选在沃平台www.169ol.com. ";

		$re1 = \Module\PhoneCode::instance()->sendMessage($this->mob,$smsInfo);

		$ret['sta'] = 1;
		$ret['msg'] = "恭喜您，兑换成功!";			
		return $ret;
	}    

	public function felware2wobei($id){

		$ret['sta'] = -1;
		$ret['msg'] = "很抱歉，折价返还失败！";	
		$where = [
			'user_id' => $this->user_id,
			'id' => $id,
			'status' => 0,
			'type in'=>[5,11], 
			'effective_time >= ' =>time() 
		];

		$streamInfo = Common::Model("Sign")->getUserStreamInfo($where);

		if(!$streamInfo){
			$ret['msg'] = "非法参数";
			return $ret;
		}

		$cost_wobei = ceil((int)$streamInfo['present_price']/10);
	
		$res = $this->addSignWobei(5,$cost_wobei);

		if(!$res){
			$ret['sta'] = -1;
			$ret['msg'] = "系统忙请稍后再试";
			return $ret;					
		}

		$data = [
			'status' => 2
		];
		$where = [
			'user_id' => $this->user_id,
			'id' => $id
		];

		$res = Common::Model("sign")->completeStream($data,$where);
		if(!$res){
			$ret['sta'] = -1;
			$ret['msg'] = "系统忙请稍后再试";
			return $ret;					
		}

		\Module\PhoneCode::instance()->sendMessage($this->mob,$smsInfo);

		$ret['sta'] = 1;
		$ret['msg'] = "恭喜您，折价返还成功！";		
		// var_dump($ret);die();	
		return $ret;
	}    


}
