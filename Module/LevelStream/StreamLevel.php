<?php
namespace Module\LevelStream;

use \Exception;
/**
 * 流量等级
 *
 */
class StreamLevel extends \Core\Lib\ModuleBase{
	public $err = '';//错误

    const LEVEL_1 = 1;//一星
    const LEVEL_2 = 2;//二星
    const LEVEL_3 = 3;//三星
    const LEVEL_4 = 4;//四星
    const LEVEL_5 = 5;//五星

    private static $stream_level_name = array(
    	self::LEVEL_1 => "一星会员特权奖励",
    	self::LEVEL_2 => "二星会员特权奖励",
    	self::LEVEL_3 => "三星会员特权奖励",
    	self::LEVEL_4 => "四星会员特权奖励",
    	self::LEVEL_5 => "五星会员特权奖励"
    	);

    const RTYPE = 10;//来源
    const COUNT = 1;//次数

    /**
     * 得到会员列表
     */
    public function getUserLevelStreamList(){
        $var = array();
        //得到会员等级
        $phone = $_SESSION['user_info']['user_phone'];
        //获取用户等级
        $module = \Module\Api\Users::instance();
        $user_info = $module->getBaseUserInfo($phone);
       
        $result = \Model\Stream\StreamLevelDayrent::instance()->getList(array("level"=>$user_info['lv'],"is_delete"=>0));
        if(empty($result)){
            return $var;
        }
        $var = $result[0];//取第一条
        $var['supr_count'] = $this->checkSurplus($result);

        return $var;
    }

    private function getStreamModel(){
        return \Model\StreamProduct::instance();
    }

    //得到相关联的流量包
    public function getComPackage($level){
        $streamCodeList = array();
        $result = \Model\Stream\StreamLevelDayrent::instance()->getList(array("level"=>$level,"is_delete"=>0));
        foreach ($result as $key => $value) {
            $rws = $this->getStreamModel()->getRow(array('id'=>$value['stream_id']));
            array_push($streamCodeList, $rws['stream_code']);
        }

        return $streamCodeList;
    }

    //包日订购次数
    public function getDayNum($stream_code){
        $rw = $this->getStreamModel()->getRow(array('stream_code'=>$stream_code));

        return $rw['day_num'];
    }

    //包月订购次数
    public function getMonthNum($stream_code){
        $rw = $this->getStreamModel()->getRow(array('stream_code'=>$stream_code));

        return $rw['month_num'];
    }

    //检查流量包剩余次数
    public function checkSurplus($data){
        $count = 0;
        $phone = $_SESSION['user_info']['user_phone'];
        //得到相同的流量包
        $rws = $this->getStreamModel()->getRow(array('id'=>$data[0]['stream_id']));
		
        $where = array();
		$package_code = [$rws['stream_code']];
		!empty($rws['by_stream_code']) && $package_code = array_merge($package_code,explode(',',$rws['by_stream_code']));
        $where['package_code'] = $package_code;
        $where['r_type'] = self::RTYPE;
        $where['phone'] = $phone;
        $where['status'] = 1;
        $where['ctime >='] = mktime(0,0,0,date('m'),1,date('Y'));
        $count = $this->getStreamModel()->getOrderPacageCount($where);
       
        $res = self::COUNT - intval($count);
        if($res){
            return $res;
        }else{
            return 0;
        }
    }

    //领取次数判断
    public function haveTotalNum($phone,$level){
        $stream_code = $this->getComPackage($level);

    	$where = array();
    	$where['phone'] = $phone;
    	$where['package_code'] = $stream_code;
        $where['status'] = 1;
    	try{
    		$this->isOrderDay($where,$this->getDayNum($stream_code[0]));
    		$this->isOrderMonth($where,$this->getMonthNum($stream_code[0]));
    	}catch (Exception $e) {
    		$this->err = $e->getMessage();
    	}
    	
    	return true;
    }

    //判断日租包日订购次数
    private function isOrderDay($where,$num){
        $where['ctime<=']=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
        $where['ctime>=']=mktime(0,0,0,date('m'),date('d'),date('Y'));
    	$count = $this->getStreamModel()->getOrderPacageCount($where);
		//不限制订购次数
		if($num==-1){
			return true;
		}
    	if($count >= $num){
    		throw new Exception("流量包日订购次数已用完!");
    	}
    }

    //判断日租包日订购次数
    private function isOrderMonth($where,$num){
        $where['ctime >='] = mktime(0,0,0,date('m'),1,date('Y'));
    	$count = $count = $this->getStreamModel()->getOrderPacageCount($where);
		//不限制订购次数
		if($num==-1){
			return true;
		}
    	if($count >= $num){
    		throw new Exception("流量包月订购次数已用完!");
    	}
    }
    
    
}