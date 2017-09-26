<?php 
namespace Module\Services;
use \Module\Common as Common;
use \Module\Services\WobeiProgress;
use \Module\FreeLottery; 



class CommonActObj extends \Core\Lib\ModuleBase{


	private static $default_zip = "default.zip"; 
	private static $default_dir = "Uploads/act/"; 

	public static function gen(){
		$ca_m = Common::Model("CommonAct");

		$rt = $ca_m->add();

		if(!$rt){
			throw new \Exception("Insert faild");
		}

		self::initTpl($rt['tpl_dir'],self::$default_zip);

	}

	public static function initTpl($dir,$file){
		
		$srcFile = self::$default_dir.$file;
		$disDir = self::$default_dir.$dir;

		if(!is_dir($disDir)){			
			if(!mkdir($disDir)){
				throw new Exception("failed to mkdir");
			}
		}

		$disFile =$disDir."/".$file;
		$cp = copy($srcFile,$disFile);
		
		if(!$cp){
			throw new Exception("failed to copy zip");
		}


		
		return true;
	}
}