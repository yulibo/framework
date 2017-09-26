<?php

namespace Module\NationalDay;

use \Exception;

class NationalDay   {
	
	public $err;
	
	//订购流量
    public function orderStream($streamCode) {
	   try{
			return $this->getNationalDayOrderModule()->orderStream($streamCode);
	   }catch(Exception $e){
		   $this->err = $e->getMessage();
	   }
    }
	
	

	//流量列表
    public function getStreamList() {
       return $this->getNationalDayListModule()->getStreamList();
    }

	//获取流量 -代理
	public function getStreamRow($where = array()){
		return $this->getNationalDayDataModule()->getStreamRow($where);
	}
	
	//获取国庆数据层 module
	private function getNationalDayDataModule(){
		return  NationalDayData::getIns();
	}
   
	//获取流量包订购module
	private function getNationalDayOrderModule(){
		return  new NationalDayOrder();
	}

	//获取流量包list module
	private function getNationalDayListModule(){
		return  new NationalDayList();
	}
	
}

?>