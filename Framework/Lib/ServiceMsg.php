<?php 
namespace Core\Lib;


use \Module\Ajax;

class ServiceMsg{

	public $msg_arr;
	public $isAjax;
	public $default;

	public function __construct(Array $msg_arr = [],$isAjax = false ,$default="",$template = ""){
		if(count($msg_arr) <= 0 ){
			return false;
		}
		$this->msg_arr = $msg_arr;
		$this->isAjax  = $isAjax;
		if(trim($default) == ""){
			$this->default = "系统忙，请稍后再试";	
		}else{
			$this->default = $default;
		}
	}

	public function send($sta,$compact = ""){
		
		if(isset($this->msg_arr[$sta])){
			$msg = $this->msg_arr[$sta];
		}else{
			$msg = $this->default;
		}
		$res = compact("sta","msg");

		if( is_array( $this->template ) && array_key_exists( "sta", $this->template ) ){
			$res[$template["sta"]] = $res["sta"];
			unset($res["sta"]);
		}

		if( is_array( $this->template ) && array_key_exists( "msg", $this->template ) ){
			$res[$template["msg"]] = $res["msg"];
			unset($res["msg"]);
		}
		if(is_array($compact)){
			foreach ($compact as $k => $v) {
				$res[$k] = $v;
			}
			
		}

		if($this->isAjax){
			echo json_encode($res);die();
		}

		return $res;
	}

	public function toJson($sta,$compact = ""){
		if(isset($this->msg_arr[$sta])){
			$msg = $this->msg_arr[$sta];
		}else{
			$msg = $this->default;
		}
		$res = compact("sta","msg");

		if( is_array( $this->template ) && array_key_exists( "sta", $this->template ) ){
			$res[$template["sta"]] = $res["sta"];
			unset($res["sta"]);
		}

		if( is_array( $this->template ) && array_key_exists( "msg", $this->template ) ){
			$res[$template["msg"]] = $res["msg"];
			unset($res["msg"]);
		}
		if(is_array($compact)){
			foreach ($compact as $k => $v) {
				$res[$k] = $v;
			}
			
		}

		echo json_encode($res);die();
	
	}

	public function getRes($sta){
		if(isset($this->msg_arr[$sta])){
			$msg = $this->msg_arr[$sta];
		}else{
			$msg = $this->default;
		}
		$res = compact("sta","msg");
		
		return $res;		
	}

	public function getMsg($sta){

		$msg = $this->default;

		if(is_numeric($sta)){
			$msg = isset($this->msg_arr[$sta]) ? $this->msg_arr[$sta] : $msg ;
		}

		if(is_string($sta)){
			$msg = $sta;
		}		

		return $msg;		
	}

}

?>