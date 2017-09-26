<?php
/**
* 流量订购的接口调用层
*/
namespace Module\EStream;
class EStream extends \Core\Lib\ModuleBase{

	private $result;
    private $err = array();
    /**
     * 获取业务流水号
     * @param  array  $data [description]
     * @return [type]       [description]
     */
    public function makeTaskId($data=array()){
    	$data=$this->formatTaskData($data);
    	if(empty($data)){
    		return false;
    	}
    	$this->requestApi($data,'getTaskId');
    	$this->log('taskId',array('request'=>$data,'response'=>$this->getResult()));
		return $this->getResult();
    }

    /**
     * 对客户产品进行变更（订购和退订）
     * @param  array  $data [传递参数]
     * @return [type]       [description]
     */
    public function productOperate($data=array()){
    	$data=$this->formatOperateData($data);
    	if(empty($data)){
    		return false;
    	}
    	$this->requestApi($data,'productFlowOperate');
    	$this->log('productOperate',array('request'=>$data,'response'=>$this->getResult()));
		return $this->getResult();
    }

    /**
     * 客户产品进行变更（订购和退订），资源购买的办理结果查询
     * @param  array  $data [传递参数]
     * @return [type]       [description]
     */
    public function productResult($data=array()){
    	$data=$this->formatPResultData($data);
    	if(empty($data)){
    		return false;
    	}
    	$this->requestApi($data,'productFlowResult');
    	$this->log('productResult',array('request'=>$data,'response'=>$this->getResult()));
		return $this->getResult();
    }

    /**
     * 业务变更
     * 填充默认参数并验证格式化参数
     * @param  array  $data [传递参数]
     * @return [type]       [description]
     */
    public function formatOperateData($data=array()){
    	if(empty($data)){
    		$this->err[]='请求参数错误';
    		return false;
    	}
    	if(!isset($data['phone'])){
    		$this->err[]='手机号码为空';
    		return false;
    	}
    	if(!$this->validPhone($data['phone'])){//验证手机号码是否正确
    		return false;
    	}
    	if(!$this->validSiChuangUnicomPhone($data['phone'])){//验证是否是联通号码
    		return false;
    	}
    	if(!isset($data['packagecode'])||empty($data['packagecode'])){//验证业务包产品编码是否为空
    		$this->err[]='业务包产品编码为空';
    		return false;
    	}
    	if(!$this->validPackageCode($data['packagecode'])){//验证业务包产品编码是否正确
    		return false;
    	}
    	if(!isset($data['taskId'])||empty($data['taskId'])){//验证是否传递业务流水号
    		$this->err[]='业务流水号为空';
    	}
    	$data['modify']='ding';
    	return $data;
    }

    /**
     * 业务变更查询
     * 填充默认参数并验证格式化参数
     * @param  array  $data [传递参数]
     * @return [type]       [description]
     */
    public function formatPResultData($data=array()){
    	if(empty($data)){
    		$this->err[]='请求参数错误';
    		return false;
    	}
    	if(!isset($data['phone'])||empty($data['phone'])){
    		$this->err[]='手机号码为空';
    		return false;
    	}
    	if(!$this->validPhone($data['phone'])){//验证手机号码是否正确
    		return false;
    	}
    	if(!$this->validSiChuangUnicomPhone($data['phone'])){//验证是否是联通号码
    		return false;
    	}
    	if(!isset($data['packageCode'])||empty($data['packageCode'])){//验证业务包产品编码是否为空
    		$this->err[]='业务包产品编码为空';
    		return false;
    	}
    	if(!$this->validPackageCode($data['packageCode'])){//验证业务包产品编码是否正确
    		return false;
    	}
    	if(!isset($data['taskId'])||empty($data['taskId'])){//验证是否传递业务流水号
    		$this->err[]='业务流水号为空';
    	}
    	return $data;
    }

    /**
     * 获取业务流水号
     * 填充默认数据并验证
     * @param  [array] $data 	[接口请求参数]
     * @return [type]       	[description]
     */
    public function formatTaskData($data=array()){
    	if(!isset($data['phone'])){
    		$this->err[]='请求参数错误';
    		return false;
    	}
    	if(!$this->validPhone($data['phone'])){//验证手机号码是否正确
    		return false;
    	}
    	if(!$this->validSiChuangUnicomPhone($data['phone'])){//验证是否是联通号码
    		return false;
    	}
    	$data['action']='4g';
    	return $data;
    }

    /**
     * 验证是否是四川联通的号码
     * @param  string $phone [description]
     * @return [type]        [description]
     */
    public function validSiChuangUnicomPhone($phone=''){
        $phoneSection=mb_substr($phone, 0,7);
        $section=\Model\SectionNo::instance()->getRow(array('section_no'=>$phoneSection));
        if(empty($section)){
            $this->err[]='该号码不是四川联通号码';
            return false;
        }
    	return true;
    }

    /**
     * 验证流量包码是否是数据库中的包码
     * @param  [type] $packageCode [description]
     * @return [type]              [description]
     */
    public function validPackageCode($packageCode){
    	$package=\Model\EStream::instance()->getPackageByCode($packageCode);//通过业务编码获取业务信息
    	if(empty($package)){
    		$this->err[]='业务包产品不存在';
    		return false;
    	}
    	return true;
    }

    /**
     * 验证手机号码
     * @param  string $phone [手机号码]
     * @return [type]        [description]
     */
    public function validPhone($phone=''){
    	$str = '/^1(([385][0-9])|(47)|([7][012356789]))\d{8}$/';
		if (!preg_match($str, trim($phone)) || strlen(trim($phone)) != 11) {
			$this->err[]='手机号码格式不正确';
			return false;
		}
		return true;
    }

    /**
	 * 请求接口存储接口结果以及错误信息
	 * @param  array  $data        [组装好的查询参数]
	 * @param  string $serviceName [服务名称]
	 * @return [type]              [description]
	 */
	public function requestApi($data=array(),$serviceName=''){
		$api = \Core\Lib\WmApi::instance();
        if(empty(APITYPE)){
            $api->setConfig('test4G');
        }
        $result = $api->httpRequest($data,$serviceName);
        $this->result = $result;
        if (empty($result)){
            $this->err[] = '接口请求失败';
        }
	}

	/**
     * 获取结果集
     */
    public function getResult(){
        return $this->result;
    }

    /**
     * 获取错误集
     */
	public function getErr(){
		return $this->err;
	}
}