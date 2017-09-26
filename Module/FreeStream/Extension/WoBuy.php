<?php 
namespace Module\FreeStream\Extension;
use Module\FreeStream\FreeStream as FreeStream;
//你上网,沃买单
class WoBuy extends FreeStream{
	
	const BORAD_ID = 3;//你上网,沃买单
	
	// 初始化
    protected function init()
    {
        parent::init();
		$this->streamWhere = array(
			'range'=>$this->getUserType()
		);
    }

	//获取用户类型
	private function getUserType(){
		return strtoupper($this->userBaseInfo['fosterUserType']);
	}
	
	//检查用户权限
    protected function getPer(){
		if(empty($this->getUserType())){
			return false;
		}
    	$row = $this->getFreeStreamModel()->getWoBuyCount(array('range'=>$this->getUserType()));
    	if(empty($row)){
			return false;
		}else{
			return true;
		}
	}
}
?>