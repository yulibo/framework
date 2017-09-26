<?php 
namespace Module\Services;
use \Module\Common as Common;


class WobeiProgress extends \Core\Lib\ModuleBase{

/**
 * 沃贝操作服务相关接口
 *
 * @author Bean 
 *
 */

    //查询用户沃贝相关信息 
    static public function getWobeiInfo($mob){
        $api_m = Common::Model("Api\\Api");
        return $api_m -> getWobeiInfo($mob);      
    }

    //仅获取用户沃贝数量
    static public function getUserWobei($mob){
        $api_m = Common::Model("Api\\Api");
        
        $wobeiInfo = $api_m->getWobeiInfo($mob);

        // 处理接口错误单词
        if(!isset($wobeiInfo['balance'])){
            $wobeiInfo['balance'] = $wobeiInfo['balence'];
        }

        return (int)$wobeiInfo['balance'];
    }

    //沃贝扣减接口 $realDec 
    static public function deductWobei($user_id,$mob,$wobei,$info="扣减沃贝",$remark="",$isFrozen = 'n'){
        $api_m   = Common::Model("Api\\Api");
        $wobei_m = Common::Model("wobei");

        $dectype = 1;

        //沃贝冻结状态    
        if($realDec == 'y'){
            $dectype = 2;
        }
        $log_data = [
            'user_id' =>$user_id,
            'type'    =>$dectype, 
            'wobei'   =>$wobei,
            'info'    =>$info,
            'created' =>time()
        ];

        $req_dec = $api_m -> decWobei($user_id,$mob,$wobei,$info,'',$isFrozen);

        if($req_dec){
            $log_data['request_state'] = 1; 
        }else{
            $log_data['request_state'] = 0;
        }

        // $wobei_m->wobei_log($log_data);
        
        return $req_dec;
    }

    //沃贝冻结接口 $realDec 
    static public function freezeWobei($phone,$wobei,$info=""){
        $api_m   = Common::Model("Api\\Api");
        $wobei_m = Common::Model("wobei");

        $dectype = 1;

        $log_data = [
            'user_id' =>$_SESSION['user_info']['user_id'],
            'type'    =>$dectype, 
            'wobei'   =>$wobei,
            'info'    =>$info,
            'created' =>time()
        ];

        $req_dec = $api_m -> freezeWobei($phone,$wobei,$info);

        if($req_dec){
            $log_data['request_state'] = 1; 
        }else{
            $log_data['request_state'] = 0;
        }

        // $wobei_m->wobei_log($log_data);
        
        return $req_dec;
    }

    //沃贝解冻接口 $realDec 
    static public function deFreezeWobei($phone,$wobei,$info=""){
        $api_m   = Common::Model("Api\\Api");
        $wobei_m = Common::Model("wobei");

        $dectype = 1;

        $log_data = [
            'user_id' =>$_SESSION['user_info']['user_id'],
            'type'    =>$dectype, 
            'wobei'   =>$wobei,
            'info'    =>$info,
            'created' =>time()
        ];

        $req_dec = $api_m -> deFreezeWobei($phone,$wobei,$info);

        if($req_dec){
            $log_data['request_state'] = 1; 
        }else{
            $log_data['request_state'] = 0;
        }

        // $wobei_m->wobei_log($log_data);
        
        return $req_dec;
    }	

    static public function addWobei($user_id,$mob,$wobei,$info="赠送沃贝"){
        $api_m   = Common::Model("Api\\Api");
        $wobei_m = Common::Model("wobei");

        $log_data = [
            'user_id' =>$user_id,
            'type'    =>0, 
            'wobei'   =>$wobei,
            'info'    =>$info,
            'created' =>time()
        ];

        $req_dec = $api_m -> addWobei($user_id,$mob,$wobei,$info,'');

        if($req_dec){
            $log_data['request_state'] = 1; 
        }else{
            $log_data['request_state'] = 0;
        }

        // $wobei_m->wobei_log($log_data);
        
        return $req_dec;        
    }

    static public function getSignWobei($user_id,$mob,$signCode,$info = ""){

        $api_m   = Common::Model("Api\\Api");
        $wobei_m = Common::Model("wobei");
        $sign_m  = Common::Model("Sign");

        $req_dec = $api_m -> OperateWobei($mob,$signCode);
        $wobei  = $sign_m::getSCWobei($signCode);

        $log_data = [
            'user_id' =>$user_id,
            'type'    =>0, 
            'wobei'   =>$wobei,
            'info'    =>$info,
            'requestCode' => $signCode,
            'created' =>time()
        ];

        $info = $api_m->getInfo();
        $info = json_decode($info,true);
        if(is_array($info)){
            $info = $info['error'];
        }else{
            $info = (int)$info;
        }


        $req_data = [
            'member_id' => $user_id,
            "signCode" => $signCode,
            "request_status" => +$req_dec,
            "msg" => $info,
            "created" =>time()
        ];

        
        $req = \Model\Sign::instance()->sign_req($req_data);


        // $info = json_decode($info,true);
        // if(is_array($info)){
        //     $info = $info['error'];
        // }else{
        //     $info = (int)$info;
        // }


        // $req_data = [
        //     'member_id' => $user_id,
        //     "signCode" => $signCode,
        //     "request_status" => +$req_dec,
        //     "msg" => $info,
        //     "created" =>time()
        // ];

        
        // $req = \Model\Sign::instance()->sign_req($req_data);

        if($req_dec){
            $log_data['request_state'] = 1; 
        }else{
            $log_data['request_state'] = 0;
            $log_data['info'] = "今天已签到，或签到失败";
        }

        $wobei_m->wobei_log($log_data);
            
        return $req_dec;      
    }    

    static public function register($mob){
        $api_m = Common::Model("api\api");
        return $api_m->register($mob);
    }

}