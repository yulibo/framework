<?php 
namespace Module\Services;
use \Module\Common as Common;


class StreamProgress extends \Core\Lib\ModuleBase{

/**
 * 流量兑换服务相关接口
 *
 * @author Bean 
 *
 */

    //规则码流量对照
    static function getStreamCodeArr(){
        return \Model\Stream::getStreamCodeArr();
    }

    //验证兑换码在系统内是否存在
    static public function isValidStreamCode($streamCode){

    	$stream_code_array = self::getStreamCodeArr();

    	return array_key_exists($streamCode, $stream_code_array);
    }


	static public function checkCustom($userInfo,$streamInfo,$type=0){

		$failed_msg = [];
        $failed_msg[1] = "很遗憾，兑换失败，您可能是4G用户，目前暂不支持兑换流量包";
        $failed_msg[2] = "很遗憾，兑换失败，您输入的号码可能是4G用户，目前暂不支持兑换流量包";
        $failed_msg[3] = "恭喜您，兑换成功。";
        $failed_msg[4] = "流量包套餐号码不存在";
        $failed_msg[5] = "很遗憾，兑换失败，你要兑换的号码不是联通号码，不能进行兑换";
        $failed_msg[6] = "很遗憾，兑换失败，你要兑换的号码格式有误";
        $failed_msg[7] = "很遗憾，兑换失败，你的沃贝余额不足";
        $failed_msg[8] = "很遗憾，兑换失败，账号对应电话号码格式有误";
        $failed_msg[9] = "很遗憾，兑换失败，该商品为抢购商品，您并未抢购，请先抢购";
        $failed_msg[10] = "很遗憾，兑换失败，已超过抢购商品30分钟有效期";
        
        $msg = [];
        $msg[1] = "很遗憾，订购失败，您可能是4G用户，目前暂不支持订购流量包";
        $msg[2] = "很遗憾，订购失败，您输入的号码可能是4G用户，目前暂不支持订购流量包";
        $msg[3] = "恭喜您，订购成功。";
        $msg[4] = "流量包套餐号码不存在";
        $msg[5] = "很遗憾，订购失败，你要订购的号码不是联通号码，不能进行订购";
        $msg[6] = "很遗憾，订购失败，你要订购的号码格式有误";
        $msg[7] = "很遗憾，订购失败，你的沃贝余额不足";
        $msg[8] = "很遗憾，订购失败，账号对应电话号码格式有误";
        $msg[9] = "很遗憾，订购失败，该商品为抢购商品，您并未抢购，请先抢购";
        $msg[10] = "很遗憾，订购失败，已超过抢购商品30分钟有效期";
        if ($type == 1){
            //重写错误码
            $failed_msg =$msg;
        }
        $res['sta'] = 1;
        $res['mes'] = $failed_msg[3];

		//验证号码合法性

        if(!Common::is_mob($userInfo['targ'])||"" == trim($userInfo['targ'])){
            $res['sta'] = -6;
            $res['mes'] = $failed_msg[6];
            $err_arr[] = $res['mes'];   
        }


		if(!Common::is_ChinaUnicom_mob($userInfo['targ'])){
            $res['sta'] = -5;
            $res['mes'] = $failed_msg[5];
            $err_arr[] = $res['mes'];  
		}
		
	
		//验证用户沃贝数量是否足够
		if( (int)$userInfo['wobei'] -(int)$streamInfo['wobei'] < 0 && (int)$streamInfo['wobei'] != 0){
            $res['sta'] = -7;
            $res['mes'] = $failed_msg[7];
            $err_arr[] = $res['mes'];
		}

		return $res;
	}

	static public function customStream($mob,$streamCode,$vgId='',$type = 0){
	    
	    $msg_arr = [
	        0=>[
	          1=>"恭喜您，兑换成功。",
	          2=>"很遗憾，兑换失败，{$mob}本月已经订购过该产品",
	          3=>"很遗憾，兑换失败，{$mob}可能是非四川联通或者4G用户，目前暂不支持兑换流量包",
	          4=>"系统忙，请稍后再试",
	        ],
	        1=>[
	          1=>"恭喜您，订购成功。",
	          2=>"很遗憾，订购失败，{$mob}本月已经订购过该产品",
	          3=>"很遗憾，订购失败，{$mob}可能是非四川联通或者4G用户，目前暂不支持订购流量包",
	          4=>"系统忙，请稍后再试",
	        ],
	    ];


		$api_m = Common::Model("Api\\Api");
		$packageName = isset($_REQUEST['packageName']) ? trim($_REQUEST['packageName']) :'';
		$result =  $api_m->customStream($mob,$streamCode,$vgId,$packageName);
		

		$result = json_decode($result,true);
        
		
	
		
        if(1 === (int)$result['result']){
            $res['sta'] = 1;
            $res['msg'] = $msg_arr[$type][1];
            $res['request_state'] = 1;
            return $res;
        }

		
		
    	$result = $result['rspData'];

    	$result = json_decode($result,true); 
   		$result = $result[0];

		
		$res['sta'] = 4;
		$res['msg'] = $result['msg'];
		$res['request_state'] = 1; 
		
    	if($result['status'] != "00000"){

    		$res['request_state'] = 0; 
    		$res['sta'] = -1;

            $temp_arr = explode(",",$result['msg']);
            $code = array_shift($temp_arr);
    		$temp_arr = explode(":",$code);
    		$eCode = array_pop($temp_arr);
            
            switch ($eCode) {
                case '08':
                    $res['msg'] = $msg_arr[$type][2];
                    break;
                case '99':
                    $res['msg'] = $msg_arr[$type][3];
                    break;                    
                default:
                    $res['msg'] = $msg_arr[$type][4];
                    break;
            }   
    	    
        }else{
            $res['sta'] = 1;
            $res['msg'] = $msg_arr[$type][1];
        }
        isset($result['ERROR_MESSAGE']) && $res['msg'] = $result['ERROR_MESSAGE'];
		return $res;    			
	}
	
    public static function getFestSteamInfo($fest_id){
        
        $FestiVal_arr = [
            1=>["name"=>"3G流量包-10元","code"=>"1016","fee"=>"10元","wobei"=>0,"stream"=>"100M"],
            2=>["name"=>"3G流量包-20元","code"=>"1017","fee"=>"20元","wobei"=>0,"stream"=>"300M"],
            3=>["name"=>"3G流量包-30元","code"=>"1018","fee"=>"30元","wobei"=>0,"stream"=>"500M"],
        ];

        if(!isset($FestiVal_arr[$fest_id])){
            $streamInfo = false;
        }else{
        
            $streamInfo  = $FestiVal_arr[$fest_id];
           
        }

        return $streamInfo;
    }


    public static function getExtraFestSteamInfo($fest_id){
 
        $FestiVal_arr = [
            1=>["name"=>"流量节活动10元流量包赠送100M","code"=>"1061","fee"=>"10","wobei"=>0,"stream"=>"100M"],
            2=>["name"=>"流量节活动20元流量包赠送300M","code"=>"1067","fee"=>"20","wobei"=>0,"stream"=>"300M"],
            3=>["name"=>"流量节活动30元流量包赠送500M","code"=>"1070","fee"=>"30","wobei"=>0,"stream"=>"500M"],
        ];

        if(!isset($FestiVal_arr[$fest_id])){
            $streamInfo = false;
        }else{

            $streamInfo  = $FestiVal_arr[$fest_id];          
        }

        return $streamInfo;
    }


}
