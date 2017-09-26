<?php 

namespace Module\Services;
use \Module\Common as Common;



class XuanbeiProgress extends \Core\Lib\ModuleBase{

	public function __construct(){
		$msg = \Mall\Config\Biz::$xuanbei_exch_handle;
		$this->ServiceMsg = new \Core\Lib\ServiceMsg($msg,TRUE);
	}

	public function ecoupon_exchange($mob='',$ecoupon_code = '',$xuanbei = 0){
		$res = [
			"sta" => "-999",
			"msg" => "Waiting.."
		];

		$xuanbei_m = Common::Model("xuanbei");

		
		$ecoupon_info = self::ecoupon_check($ecoupon_code);

		
		$rs = $xuanbei_m->ecoupon_exchange($mob,$ecoupon_code,$xuanbei);
		
		// ecoupon_api_transfer();

		// return 
	}

	public function ecoupon_exch($user_id,$ecoupon_code){
		// ecoupon_log init
		$res = $this->ServiceMsg->getRes(1000);


		$ecoupon_m = Common::Model("Ecoupon");

		// Redis server check;

		if(!$ecoupon_m->redis){
			$res = $this->ServiceMsg->getRes(-1001);
			return $res;
		}

		// exchange times check
		// $rs = $ecoupon_m->del_frozen_counter($user_id); 
		$frozen_counter = $ecoupon_m->get_frozen_counter($user_id);

		
		$_time = time();
		
		// lock check
		// var_dump($frozen_counter);

		if($frozen_counter && ($frozen_counter['locked'] === 1) ){

			if($frozen_counter['unlock_time'] > $_time){
				$res = $this->ServiceMsg->getRes(-1007);
				return $res;
			}else{
				$rs = $ecoupon_m->del_frozen_counter($user_id); 
				if(!$rs){
					$res = $this->ServiceMsg->getRes(-1001);
					return $res;
				}				
			}
		}		

		$ecoupon_code_info = $ecoupon_m->get_ecoupon_info($ecoupon_code);

		if(!$ecoupon_code_info){
			// add frozen counter of ecoupon 
			$ecoupon_m->set_frozen_counter($user_id);

			$res = $this->ServiceMsg->getRes(-1006);

			
			return $res;
		}

		$batch_code = $ecoupon_code_info['batch_code'];

		$batch_info = $ecoupon_m->get_ecoupon_full_info($batch_code);

		if(!$batch_info){
			$res = $this->ServiceMsg->getRes(-1011);
			return $res;			
		}

		$batch_end_time = $batch_info['end_time'];
		$xuanbei = $batch_info['face_value'];

		if($batch_end_time < $_time){
			$res = $this->ServiceMsg->getRes(-1012);
			return $res;						
		}

		switch ((int)$ecoupon_code_info["status"]) {
			case 1:
				//兑换成功清空错误次数
				$ecoupon_m->del_frozen_counter($user_id);
				break;
			case 2:
				$res = $this->ServiceMsg->getRes(-1008);
				return $res;
				break;
			case 15:
				$res = $this->ServiceMsg->getRes(-1009);
				return $res;
				break;				
			case 0:
			default:
				$res = $this->ServiceMsg->getRes(-1010);
				return $res;
				break;
		}

		$user_info = Common::Model("user")->getUserById($user_id);
		$mob = $user_info["phone"];

		$xuanbei_m = Common::Model("xuanbei");		
		$api_rs = $xuanbei_m->ecoupon_exchange($mob,$ecoupon_code,$xuanbei);
		if(!$api_rs){
			$res = $this->ServiceMsg->getRes(-1000);
			return $res;			
		}

		$api_rs = json_decode($api_rs,true);

		switch ($api_rs['result']) {
			
			//请求成功
			case 1:	
				$res = $this->ServiceMsg->getRes(1001);

				// 后补特殊逻辑
				
				$res["msg"]  = "恭喜您，成功兑换{$xuanbei}炫贝！";

				$rspData = json_decode($api_rs["rspData"],true);

				$res["data"]["wobei"] = (int)$rspData[0]['wobeiNum'];

				if($res["data"]["wobei"]<=0){
					$res["data"]["wobei"] = 0;	
				}else{

			        $log_data = [
			            'user_id' =>$user_id,
			            'type'    => 0, 
			            'wobei'   =>$res["data"]["wobei"],
			            'info'    =>"积分电子券兑换赠送沃贝",
			            'created' =>time(),
			            'request_state' =>1
			        ];
			        $wobei_m = Common::Model("wobei");
			        //$wobei_m->wobei_log($log_data);  接口那边已经添加日志

				}

				$use_rs = $ecoupon_m->ecoupon_use($ecoupon_code);
				if(!$use_rs){
					$res = $this->ServiceMsg->getRes(-1000);
				}

				$this->makeAccountLog($xuanbei,$user_id);//记录账户日志
				return $res;	
				break;
			//CPS端电子券已使用
			case 10:
				$res = $this->ServiceMsg->getRes(-1013);
				return $res;	
				break;							
			//CPS端请求超时
			case 11:
				$res = $this->ServiceMsg->getRes(-1000);
				return $res;	
				break;
			//其他失败原因
			case 0:
				$res = $this->ServiceMsg->getRes(-1000);
				return $res;	
				break;
			default:	
				$cps_code = substr($api_rs['result'],0,4); 
				$res = $this->ServiceMsg->getRes(-1000);
				return $res;
				break;
		}
	}

	public function makeAccountLog($xuanbei=0,$user_id){
		$log=array();
		$log['user_id']=$user_id;
		$log['store_id']=0;
		$log['order_id']=0;
		$log['number_type']=\Model\OrderPay::XB_PAY;
		$log['number']=$xuanbei;
		$log['change_type']=\Model\AccountExchangeLog::ACCOUNT_ADD;
		$log['title']="电子券兑换";
		$log['product_id']=0;
		$log['img']='';
		$log['mark']='';
		$log['create_time']=time();
        $result=\Model\AccountExchangeLog::instance()->insertRecord($log);//调用model方法插入
    }

}	

?>