<?php
namespace Module\Stream;

use \Exception;

abstract class StreamList 
{
	protected $userPhone;//用户电话
	
	//获取流量列表
	public function getStreamList(){
		$list = $this->getDataList();//获取数据列表
		if(empty($list)){
			return false;
		}
		$this->formatStreamList($list); //返还格式化的流量列表
		return $list;
	}
	
	
	//格式化流量列表
	protected function formatStreamList(&$list){
		if(empty($list)){
			return false;
		}
		foreach($list as &$val){
			$val['status'] = $this->getStatus($val['streamCode']);
		}
	}
	
	//获取流量状态
	abstract protected function getStatus($id);
	
	
	//获取流量列表
	abstract protected function getDataList();
}

?>