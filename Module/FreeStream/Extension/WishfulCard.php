<?php 
namespace Module\FreeStream\Extension;
use Module\FreeStream\FreeStream as FreeStream;
//沃派如意卡,用户免费领
class WishfulCard extends FreeStream{
	
	const BORAD_ID = 5;//沃派如意卡,用户免费领

	
	//检查用户权限
    protected function getPer(){
		if($this->userBaseInfo['isWoPai'] == 1){
			return true;
		}else{
			return false;
		}
	}

}
?>