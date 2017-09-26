<?php 
namespace Module\BarterPhone\Extension;
use Module\BarterPhone\BarterPhone;
use Model\BarterPhone\BarterPhoneDate;
 
//4G 网络体验流量包
class ExperienceStream extends BarterPhone{
	
	private $minTime;//最小时间
	private $hasData;//是否有数据
	private $monthDiff=0;//统计月差
	private $addNextMonth=0;//是否添加最小时间到下一个月了
	private $startMonthNum=0;//开始月差
	private $countList;//总的列表
	private $recevieList;//已领取的列表
	
	const G_START = 1;//已开始
	const G_OVER = 2;//已结束
	const G_NOSTART = 3;//未开始
	const G_RECEVED = 4;//已领取
	
	
	
	//检查用户权限
    protected function getPer(){
    	if(empty($this->getMinTime())){
			return false;
		}
	}
	
	//获取数据列表
	protected function getDataList(){
		
	}
	
	//获取流量列表
	public function getStreamList(){
		$data = $this->barterPhoneDateModel()->getAll();//获取所有的流量包
		if(empty($data)){
			return false;
		}
		$result = array();
		$countList= $this->getMonthBarterLog();//每月总数
		$recevieList = $this->getBarterLog(); //已经订购的记录
		foreach($data as $val){
			//显示已开始的和未开始的
			if(($status = $this->getBarterStatus($val['month_count']))!=self::G_OVER){
				$val['status'] = $status;
				$this->repeatData($val,$this->monthDiff,$result);
				$this->hasData = true;
			}
		}
		if($recevieList){
			$result = array_merge($result,array_values($recevieList));
		}
		return $result;
	}
	
	//重复组装数据
	private function repeatData($data,$num,&$result){
		$countList = $this->countList;
		$list = array();
		if($this->recevieList){
			$list = array_keys($this->recevieList);//已经订购的记录 ID
		}
		for($i=$this->startMonthNum;$i>=0 && $i<$num+$this->startMonthNum;$i++){
			$month = date('Ym',strtotime("+$i months",$this->currentTime()));
			if(in_array($data['id'].'_'.$month,$list)){
				continue;
			}
			if($month!=$data['month']){
				$data['month'] = $month;
			}
			$count = isset($countList[$data['id'].'_'.$month]['count']) ? $countList[$data['id'].'_'.$month]['count']:0;
			$data['count']= $count;
			$result[] =$data;
		}
	}
	
	//获取月差
	private function getMonthDiff($count){
		$diff= $this->getMonthNum( $this->getAddTime($count),$this->currentTime());
		if($diff<0){
			return 0;
		}else{
			$this->monthDiff=$diff;
			return $this->monthDiff;
		}
	}
	
	// 统计月差
    private function getMonthNum($date1_stamp, $date2_stamp){
        list ($date_1['y'], $date_1['m']) = explode("-", date('Y-m', $date1_stamp));
        list ($date_2['y'], $date_2['m']) = explode("-", date('Y-m', $date2_stamp));
        $month = ($date_1['y'] - $date_2['y']) * 12 + $date_1['m'] - $date_2['m'];
		if($month==0 && empty($this->addNextMonth)){
			$month+=1;
		}
        return $month;
    }
	
	
	//获取当前时间
	private function currentTime(){
		return time();
	}
	
	//获取添加过后的时间
	private function getAddTime($month){
		if(empty($this->getMinTime($month))){
			return false;
		}
		if(empty($month)){
			return false;
		}
		$month = $month;
		return  strtotime("+$month months",$this->getMinTime($month));//领取结束时间
	}
	
	
	//获取最小时间
	private function getMinTime($month){
		$this->monthDiff = $month;
		if(!empty($this->minTime)){
			return $this->minTime;
		}
		$time = $this->getBarterTime(); //换机时间
		if(empty($time)){
			return false;
		}
		$this->minTime =  $this->addDayBarterTime($time);
		if($this->timeMothFormat($time)!=$this->timeMothFormat($this->minTime)){
			$this->addNextMonth = 1;
		}
		if(date('Ym',$this->minTime)>date('Ym',$this->currentTime()))
			$this->startMonthNum = date('Ym',$this->minTime)-date('Ym',$this->currentTime());
		return $this->minTime;
	}
	
	//获取换机时间
	private function getBarterTime(){
		return '1466870400';
	}
	
	//换机时间添加天数
	private function addDayBarterTime($time){
		return $time+7*86400;
	}
	
	//时间格式月
	private function timeMothFormat($time){
		return date('Y-m',$time);
	}
	
	//获取状态
	public function getBarterStatus($month_num){
		if($this->timeMothFormat($this->getMinTime($month_num))>$this->timeMothFormat($this->currentTime())){
			return self::G_NOSTART;
		}elseif($this->getMonthDiff($month_num)>0){
			return self::G_START;
		}else{
			return self::G_OVER;
		}
	}
	
	//获取总数
	private function getMonthBarterLog(){
		return $this->countList = $this->barterPhoneDateModel()->getMonthBarterLog();
	}
	
	
	//获取用户领取记录
	private function getBarterLog(){
		$countList = $this->countList;
		$list =  $this->barterPhoneDateModel()->getBarterLog($this->userInfo['user_id']);
		if(empty($list)){
			return false;
		}
		foreach($list as &$val){
			$val['count'] = isset($countList[$val['id'].'_'.$val['month']]['count']) ? $countList[$val['id'].'_'.$val['month']]['count']:0;
			$val['status'] = self::G_RECEVED;
		}
		return $this->recevieList = $list;
	}
	
	
	//免费领取数据库
	private function barterPhoneDateModel(){
		return BarterPhoneDate::instance();
	}
}
?>