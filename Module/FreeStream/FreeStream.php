<?php
namespace Module\FreeStream;

use \Module\FreeStream\FreeStreamLogStatus;
use \Exception as Exception;

abstract class FreeStream
{

    public $err; // 错误信息

    const BORAD_ID = null; // 板块ID

    const STREAM_1 = 1; // 可领取

    const STREAM_2 = 2; // 已结束

    const STREAM_3 = 3; // 已领取

    const STREAM_4 = 4; // 未开始
    protected $permissions; // 用户是否用后权限
    protected $fid; // 浏览包ID
    private $bid; // 板块ID
    protected $is4g = 0; // 是否是4G用户
    protected $pageSize = 2; // 流量列表每页显示的条数
	protected $userInfo;//用户基本信息
	protected $userBaseInfo;//用户基本信息
	protected $streamWhere;//流量列表where 条件
	
    public function __construct()
    {
        $this->init();
    }
    
    // 初始化
    protected function init()
    {
        $this->getUserInfo(); //用户信息
        $this->getIs4g(); //是否是4G用户
		$this->getPermiss(); //权限获取
    }
	
	//获取权限
	private function getPermiss(){
        return $this->permissions = $this->getPer(); // 获取用户权限
	}
    
    // 获取用户信息
    private function getUserInfo()
    {
		$this->userBaseInfo = $_SESSION['baseUserInfo'];
        return $this->userInfo = $_SESSION['user_info'];
    }
    
    // 判断是否 是4G 用户
    private function getIs4g()
    {
        $flag = '';
        if (isset($this->userBaseInfo['netType']) && $flag = $this->userBaseInfo['netType']) {
            $this->is4g = ($flag == '4G') ? 1 : 0; // 标示是否是4G
        }
    }
    
    // 检查用户权限
    abstract protected function getPer();
    
    // 检查流量包状态
    private function getStreamStatus()
    {
        $obj = $this->getFreeStreamLogStatus();
        if ($obj->getNotstart() || $this->getNotstart($obj->stream)) {
            return self::STREAM_4; // 未开始
        } elseif ($obj->getOver() || $this->getOver($obj->stream)) {
            return self::STREAM_2; // 已结束
        } elseif ($obj->getReceive()) {
            return self::STREAM_3; // 已领取
        } else {
            return self::STREAM_1; // 可领取
        }
    }
	
	//获取未开始
	protected function getNotstart($data){
		return false;
	}
	
	//获取已经结束
	protected function getOver($data){
		return false;
	}
    
    // 获取流量板块
    public function getStreamBoard()
    {
        // 权限验证
        if (! $this->permissions) {
            return false;
        }
        return $this->getBoradModel()->getRow(array(
            'id' => static::BORAD_ID
        ));
    }
    // 获取流量列表
    public function getStreamList($page=1)
    {
        //权限验证
        if(!$this->permissions){
            return false;
        }
        $where = array(
            'b.b_id'=>static::BORAD_ID,
            'f.is_4g'=>$this->is4g
        );
		if(!empty($this->streamWhere)){
			$where = array_merge($where,$this->streamWhere);
		}
        $stream = $this->getFreeStreamModel()->getList($where,$page,$this->pageSize);

        foreach($stream['list'] as &$val){
            $val['status'] = $this->checkStream($val['f_id'],static::BORAD_ID);
        }
        return array('list'=>$stream['list'],'count'=>$stream['count']);
    }
    
    // 检查流量是否[未开始,已领取,已结束,可领取]
    protected function checkStream($f_id, $borad_id)
    {
        $this->fid = $f_id;
        $this->bid = $borad_id;
        return $this->getStreamStatus();
    }
    
    // 领取流量时候判断
    protected function orderStreamCheck($data)
    {}
    
    // 获取错误
    public function getError()
    {
        return $this->err;
    }
    
    // 订购流量
    public function orderStream(array $data)
    {
        try {
            $this->orderStreamCheck($data); // 领取流量前的判断
            $this->getStreamModule()->doleStreamFreeFlow($data);
            $err = $this->getStreamModule()->getError();
            if (! empty($err)) {
                throw new Exception($err);
            }
            return $this->getStreamModule()->getResult();
        } catch (Exception $e) {
            $this->err = $e->getMessage();
        }
    }
    
    // 获取流量板块model
    private function getBoradModel()
    {
        return \Model\FreeStream\BoardStream::instance();
    }
    
    // 获取免费流量包model
    protected function getFreeStreamModel()
    {
        return \Model\FreeStream\FreeStream::instance();
    }
    
    // 获取免费流量包日子model
    private function getFreeStreamLogModel()
    {
        return \Model\FreeStream\FreeStreamLog::instance();
    }
    
    // 获取状态
    private function getFreeStreamLogStatus()
    {
        return new FreeStreamLogStatus($this->fid, $this->bid,$this->userInfo['user_id']);
    }
    
    // 获取流量module
    private function getStreamModule()
    {
        return \Module\Api\Stream::instance();
    }
}

?>