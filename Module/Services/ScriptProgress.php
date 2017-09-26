<?php 
namespace Module\Services;
use \Module\Common as Common;



class ScriptProgress extends \Core\Lib\ModuleBase{


	public function __construct(){
		$this->w1_root = "D:/www/womall";
		$rs = is_dir($this->w1_root);
		$this->w1_user = $this->w1_root."/upload/member/";
		$UploadsDir = $_SERVER['DOCUMENT_ROOT']."/Uploads/Mall/user_image";
		Common::mkDirs($UploadsDir);
		$this->w2_user = $UploadsDir;
		$this->w2_reuser = "/Uploads/Mall/user_image";

	}

	public function transUserImage($image,$user_id){
		$w1_image = $this->w1_user.$image;

		$_id = 0;
		$re_m = Common::Model("Resource");
		$userDir = $this->w2_user."/{$user_id}";
		if(is_file($w1_image)){
			$new_file = $userDir."/".$image;
			Common::mkDirs($userDir);
			$rs = copy($w1_image,$new_file);			
		}
		
		if($rs){
			$name = $rename = basename($new_file);
			$text = $this->w2_reuser."/{$user_id}";
			$size = filesize($new_file);

			$tmp_arr = explode(".",$name);

			$ext  = array_pop($tmp_arr);
			$module = 1;
			$type = 0; 
			$image_id = $re_m->addResource($name, $rename, $text, $size, $ext, $module, $type);
		}

		if($image_id){
			$_id = $image_id;
		}

		return $_id;

	}
}