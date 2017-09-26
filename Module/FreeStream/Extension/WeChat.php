<?php 
namespace Module\FreeStream\Extension;
use Module\FreeStream\FreeStream as FreeStream;
//微信沃贝卡,用户免费领
class WeChat extends FreeStream{
	
	const BORAD_ID = 4;//微信沃贝卡,用户免费领

	//检查用户权限
    protected function getPer(){
		if($this->userBaseInfo['isWeiXin'] == 1){
			return true;
		}else{
			return false;
		}
	}
	
	
}
?>