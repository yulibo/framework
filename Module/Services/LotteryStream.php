<?php
/**
 * 双11特惠流量包送流量
 * @author ZhangHao
 */
namespace Module\Services;
class LotteryStream extends \Core\Lib\ModuleBase{

	//调用接口
	public function checkPackage($user,$activite,$package,$message){
		$res=$this->foreachApi($user,$package);
		if($res['status']!==1){
			$this->addLog($user,$activite,\Model\LotteryStreamLog::LSL_MPACKAGE,$package,$res);//记录日志
			return $res;
		}
		$this->addLog($user,$activite,\Model\LotteryStreamLog::LSL_MPACKAGE,$package,$res);//记录日志
		$send_message=vsprintf($message[1],array($package['name'],$package['size'],$package['package_price']));
		\Model\Api\Api::instance()->sendSMS($user['phone'],$send_message,1);//发送订购成功短信
		$son_package=$this->randSonPackage($package);
		if(empty($son_package)){
			return $res;
		}
		$this->addLog($user,$activite,\Model\LotteryStreamLog::LSL_SPACKAGE,$son_package,'');//记录日志
		$res['sonPackage_id']=$son_package['id'];
		return $res;    			
	}

	//若接口调用返回结果失败再次执行，最多三次
	private function foreachApi($user,$package){
		$result=array();
		try{
			$result=\Module\Services\StreamProgress::instance()->customStream($user['phone'],$package['code']);
		}catch(\Exception $e){
			$data=array('user'=>$user['phone'],'package_code'=>$package['code']);
			$this->log('lotteryStream',array('data'=>array($data,'log'=>'user buy package faild : '.$e->getMessage())));
		}
		// return array('status'=>1,'msg'=>'订购成功');
		$this->addNormalLog($user,$package,$result);
		return array('status'=>$result['sta'],'msg'=>$result['msg']);
	}

	//赠送流量
	public function checkSonPackage($log,$user,$package,$message){
		$num=$package['num'];
	 	$num=intval($package['num'])-1;
	 	if($num<0){
	 		return array('status'=>-1,'msg'=>'很遗憾，流量包已赠送完！');
	 	}
	 	\Model\LotterySonPackage::instance()->multPackageNum(array('num'=>$num),array('id'=>$package['id']));//扣减流量包数据
		$res=$this->foreachApi($user,$package);
		$this->updateLog($user,$log,$res);//记录日志
		if($res['status']!==1){
			$num=$package['num'];
	 		\Model\LotterySonPackage::instance()->multPackageNum(array('num'=>$num),array('id'=>$package['id']));//扣减流量包数据
			return $res;
		}
	 	
		$send_message=vsprintf($message[3],array($package['name']));//组建发送消息内容
		\Model\Api\Api::instance()->sendSMS($user['phone'],$send_message,1);//发送订购成功短信 
		return $res;
	}

	//生成兑换记录日志与系统中的流量兑换记录进行统一
	private function addNormalLog($user,$package,$result){
		$log_para = [
            'user_id'=>$user['id'],
            'wl_id' => 0,//无该数据项，可给任意值
            'streamCode' => $package['code'],
            'mobile'=>$user['phone'],
            'wobei'=>0,//不扣沃贝
            'stream'=>$package['name'],
            'created'=>time(),
            'info'=>$result['msg'],
            'request_state' =>$result['request_state']
        ];
        \Model\Stream::instance()->add_log($log_para);

	}

	//记录日志
	private function addLog($user,$activite,$type,$package,$result){
		$log_para=array(
			'user_id'=>$user['id'],
            'stream_id'=>$activite['id'],
            'stream_name' => $activite['name'],
            'package_id'=>$package['id'],
            'code' => $package['code'],
            'mobile'=>$user['phone'],
            'type'=>$type,
            'create_time'=>time(),
            'result_str'=>serialize($result)
			);
		if(!empty($result)){
			$log_para['result']=$result['status'];
		}
        \Model\LotteryStreamLog::instance()->addLog($log_para);
	}

	//更新日志
	private function updateLog($user,$log,$result){
		$data=array('result'=>$result['status'],'result_str'=>serialize($result),'is_used'=>\Model\LotteryStreamLog::LSL_USED);
		$where=array('id'=>$log['id']);
		\Model\LotteryStreamLog::instance()->updateInfo($where,$data);
	}

	//获取赠送流量包
	public function getSonPackage($package_id){
		$sonPackages=\Model\LotterySonPackage::instance()->getLists(array('stream_package_id'=>$package_id,'is_delete'=>\Model\LotterySonPackage::LSP_UNDELETE,'num !='=>0));
		return $sonPackages;
	}

	//随机出赠送流量包的id
	private function randSonPackage($package){
		$sonPackages=$this->getSonPackage($package['id']);
		$randArr=array();
		//获取还有包可以赠送的id从0开始编号存入数组待随机选择
		if(empty($sonPackages)){
			return false;
		}
		$randArr=array_keys($sonPackages);
		$id=intval(mt_rand(0,count($randArr)-1));
		return $sonPackages[$randArr[$id]];
	}
}