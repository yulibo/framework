<?php 
namespace Module\FreeStream\Extension;
use Module\FreeStream\FreeStream;
//4G流量免费领
class FreeFourG extends FreeStream{
	
	const BORAD_ID = 1;//4G流量免费领

	//检查用户权限
    protected function getPer(){
		//if($this->userBaseInfo['is4gVip'] == 1 && $this->userBaseInfo['isOCS'] == 0){
		if($this->userBaseInfo['is4gVip'] == 1 ){
			return true;
		}else{
			return false;
		}
	}
	
	
}
?>