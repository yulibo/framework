<?php
namespace Core\Config;

class Config{
	
	private static $ins = NULL;
	private function __construct(){
		
	}
	public static function getIns(){
		if(empty(self::$ins)){
			self::$ins = new self();
		}
		return self::$ins;
	}
	
	
	public $mallPayCallBackUrl = 'http://169ol.com/Mall/Order/callbackurl'; //web支付回调的返回地址
	
	public $mobilePayCallBackUrl = 'http://169ol.com/Mobile/Order/callbackurl'; //手机端支付回调的返回地址
	
	public $debugPayWay = 0;//是否启用测试环境支付调试模式
	
}
