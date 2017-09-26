<?php 
namespace Module\Stream\Extension\StreamLimit;
use \Exception;
use Module\Stream\StreamOrder;

//流量等级订购
class StreamLevel extends StreamOrder{

	public $err = '';//错误信息

	const R_TYPE = 10;


	//得到流量包4位码
	public function getFourCode($stream_id){
		$row = $this->getStreamModel()->getRow(array('id'=>$stream_id));

		return $row['stream_code'];
	}

	//订购流量
    public function orderStreamTrans($streamCode) {
        try {
            $this->transactFlow($streamCode);
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }
	
	//订购流量
    public function orderStream($stream_id,$level) {
        $streamCode = $this->getFourCode($stream_id);
		return parent::orderStream($streamCode);
       
    }

	
	//获取流量包名称
	protected function getPackageName(){
		$row = $this->getStreamModel()->getRow(array("stream_code"=>$this->streamCode));
		return $row['name'];
	}
	

    //成功发送短信
    protected function orderSuccessOpt(){
        return \Module\PhoneCode::instance()->sendMessage($_SESSION['user_info']['user_phone'],$this->getMessage());
    }

    //获取短信内容
    private function getMessage(){
        //得到stream_id
        $row = $this->getStreamModel()->getRow(array("stream_code"=>$this->streamCode));
        //当前订购流量包
        $rs = $this->getStreamLevel()->getOne(array("stream_id"=>$row['id']));
        $str = "温馨提示，您已通过“%s”活动成功订购%u元1.5GB省内流量，该流量有效期截止今日23:59:59。登录联通网上营业厅www.10010.com查询【优选在沃】";
        return sprintf($str,$rs['name'],intval($rs['price']));
    }

    private function getStreamLevel(){
    	return \Model\Stream\StreamLevelDayrent::instance();
    }

	
	//获取model stream 
	private function getStreamModel(){
		return \Model\StreamProduct::instance();
	}

	//获取订购流量service
    protected function getStreamApi() {
        return \Module\Api\Stream::instance();
    }
}
?>