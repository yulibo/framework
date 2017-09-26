<?php 
namespace Module\Stream\Extension\StreamLimit;
use Module\Stream\StreamOrder;
use Module\Stream\PanicBuyStatus;
use \Exception;

//沃贝专区抢购管理
class Wobei extends StreamOrder{

	const CATERGORY = 7;//日租包
	const R_TYPE=12;//沃贝专区抢购类型ID

	public $err = '';//错误信息

	private $streamId;

	//获取沃贝专区抢购流量商品的订购次数
	protected function getOrderStreamAcDayNum(){
		$where = array();
		$where['r_type'] = self::R_TYPE;
		$where['package_code'] = $this->streamCode;
		$where['phone'] = $this->userPhone;
		$where['ctime<=']=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
		$where['ctime>=']=mktime(0,0,0,date('m'),date('d'),date('Y'));

		return $this->getStreamModel()->getOrderPacageCount($where);
	}

	//检查流量订购次数判断
	public function checkStreamLimit($stream_id){
		// $streamCode = $this->getFourCode($stream_id);
		$streamCode = $this->getCombinStreamCode($stream_id);
		$this->setStreamCode($streamCode);
		$this->streamId = $stream_id;
		//检查活动的订购次数
		$this->getPanicStatus()->getPackagesOrder();
		//检查流量包库存是否充足
		if($this->checkStreamAllNum() == 0){
			throw new Exception("流量包今天已抢完");
		}
		$this->checkStreamDayNum(); //检查流量包当天的订购总次数
		$this->checkStreamMonthNum();//检查当月的订购总次数
		return true;
	}

	//获取流量包当天订购的总数--针对具体的活动
    private function getOrderPacageCount() {
        $where['ctime<='] = mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')) - 1;
        $where['ctime>='] = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
        $where['phone'] = $this->userPhone;
        $where['package_code'] = $this->getCombinStreamCode($this->streamId);
        $where['status'] = 1;

        return $this->getStreamModel()->getOrderPacageCount($where);
    }
	
	//获取沃贝专区抢购活动每天可以订购流量的总数
	protected function getStreamAcDayNum(){
		$row = $this->getPanicBuyModel()->getOne(array('id'=>$this->getStreamId()));
		if(empty($row)){
			return false;
		}
		return $row['d_amount'];
	}

	//得到stream_id
	private function getStreamId(){
		$where['stream_code'] = $this->streamCode;
		$row = $this->getStreamModel()->getRow($where);
		if(empty($row)){
			return false;
		}
		return $row['id'];
	}
	
	//判断指定流量包在某个活动每天的订购次数
	protected function checkStreamAcDayNum(){
		if($this->getOrderStreamAcDayNum()>=$this->getStreamAcDayNum()){
			throw new Exception('当天抢购次数已用完');
		}
	}

	//检查流量包是否已经抢完
	public function checkStreamAllNum(){
		$row = $this->getPanicBuyModel()->getOne(array('stream_id'=>$this->getStreamId())); //获取当前流量包

		$where = array();
		$where['package_code'] = $this->streamCode;
		$where['r_type'] = self::R_TYPE;
		$where['status'] = 1;
		$where['ctime<=']=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
		$where['ctime>=']=mktime(0,0,0,date('m'),date('d'),date('Y'));
		$count = $this->getStreamModel()->getOrderPacageCount($where);

		$res = intval($row['p_amount']) - intval($count);
		if($res){
			return $res;
		}else{
			return 0;
		}
	}

	//得到流量包4位码
	public function getFourCode($stream_id){
		$row = $this->getStreamModel()->getRow(array('id'=>$stream_id));

		return $row['stream_code'];
	}

	//订购流量
    public function orderStreamTrans($streamCode) {
        if (empty($this->userPhone)) {
            throw new Exception('请登录', 99);
        }
        try {
            parent::transactFlow($streamCode);
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

	//订购流量
    public function orderStream($stream_id) {
        //未开始
        if (PanicBuyStatus::getNotStartStatus()) {
            throw new Exception('活动未开始');
        }
        //已结束
        if (PanicBuyStatus::getEndStatus()) {
            throw new Exception('活动已经结束');
        }
        $streamCode = $this->getFourCode($this->streamId);
        try {
            $this->orderStreamTrans($streamCode);
        } catch (Exception $e) {
            if ($e->getCode() != -1) {
                throw new Exception($e->getMessage(), $e->getCode());
            }
            $streamCodeList = $this->getCombinStreamCode($stream_id);
            $this->repetOrder($e->getCode(), $streamCodeList);
        }
    }



    //成功发送短信
	protected function orderSuccessOpt(){
		return \Module\PhoneCode::instance()->sendMessage($this->userPhone,$this->getMessage());
	}

	//获取短信内容
	private function getMessage(){
		//得到stream_id
		$row = $this->getStreamModel()->getRow(array("stream_code"=>$this->streamCode));
		//当前订购流量包
		$rs = $this->getPanicBuyModel()->getOne(array("stream_id"=>$row['id']));
		$str = "温馨提示，您已通过“%s”活动成功订购%u元1.5GB省内流量，该流量有效期截止今日23:59:59。登录联通网上营业厅www.10010.com查询【优选在沃】";

		return sprintf($str,$rs['name'],intval($rs['true_price']));
	}

 
    //获取组合流量包产品码
    protected function getCombinStreamCode($stream_id) {
    	$streamCodeList = array();
    	$row = $this->getPanicBuyModel()->getOne(array('stream_id'=>$stream_id)); //获取当前流量包
        $rows = $this->getPanicBuyModel()->getSameLevel(array('level'=>$row['level']));//获取相同的流量包
        foreach ($rows as $key=>$val) {
        	$streamCode = $this->getFourCode($val['stream_id']);
        	array_push($streamCodeList, $streamCode);
        }

        return $streamCodeList;
    }
	
	//获取沃贝抢购 model
	private function getPanicBuyModel(){
		return \Model\PanicBuy::instance();
	}
	
	
	//获取model stream 
	private function getStreamModel(){
		return \Model\StreamProduct::instance();
	}

	//获取状态 Model
	public function getPanicStatus(){
		return new \Module\Stream\PanicBuyStatus();
	}
}
?>