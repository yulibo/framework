<?php

/**
 * 接口父类
 */

namespace Core\Lib\Api;
use \Exception as Exception;
use \Core\Lib\ApiException as ApiException;
use \Module\Common as Common;

abstract class ApiBaseModel{

    public $err;  //错误信息
    protected $result; //接口返回的的所有数据
	protected $dataResult; //数据结果
    protected $showErrMsg; // 错误码对应列表
    protected $serviceName; //接口名称
    protected $requestData; //请求数据
    protected $code; //接口返回的错误码
	protected $listMap; //列表字段映射
	protected $defaultErrMsg;//默认错误暂时
	
    protected static $resultCountKey = 'pageCount'; //结果列表总数字段
    protected static $resultDataKey = 'pageDatas'; //结果列表字段
	
	protected $apiAdapter;//接口执行者
	
	const PAGECOUNT_KEY = '';//数据列表总数
	const PAGEDATA_KYE = '';//分页数据key
	const DEFAULT_EXCE = '9999'; //默认异常
	
	
    protected static $instances = array();

   
    public static function instance(){
        $className = get_called_class();
        if (!isset(self::$instances[$className])){
            self::$instances[$className] = new $className;
        }
        return self::$instances[$className];
    }

    /**
     * 格式化列表
     * @return array
     */
    public function getResult() {
        $this->setDataResult();
        $this->formatList($this->dataResult);
        return $this->dataResult;
    }
	
	
	
	
	
    //设置接口数据
	abstract public function setDataResult();


    /**
     * 设置异常
     * @param ApiException $e api异常
     * @return void
     */
    public function setException(Exception $e){
		if (empty($e) || !($e instanceof Exception)) {
            return false;
        }
        $this->err = $e->getMessage();
		$this->code = $e->getCode();
	}
	
	
	 /**
     * 检查错误
     * @return array
     */
    private function checkError() {
        $code = $this->getCode();
		//成功状态码
        if ($code == static::$successCode) {
            return true;
        }
		//错误码列表
        if (isset($this->showErrMsg[$code])) {
            throw new ApiException($this->showErrMsg[$code]);
        }elseif(!empty($this->defaultErrMsg)){ //默认错误提示
			throw new ApiException($this->defaultErrMsg,self::DEFAULT_EXCE);
		} else {
			//提示接口端错误
            throw new ApiException($this->getResultError());
        }
    }

	  /**
     * 获取错误码
     * @return int
     */
    abstract public function getResultError();
	
    /**
     * 设置映射数组
     * @param array $list 映射数组
     * @return void
     */
    protected function setMapList($list = array()){
		$this->listMap = $list;
	}

	 /**
     * 格式化列表
     * @return void
     */
    protected function formatList(&$list) {
        if (!isset($list[self::$resultDataKey])) {
            return false;
        }
        if (empty($list)) {
            return false;
        }
        foreach ($list[self::$resultDataKey] as $key => &$val) {
            $this->mapList($val);
        }
    }

 
	
    /**
     * 改变列表KEY
     * @param array $val 改变数组  引用传值
     * @return void
     */
    protected function mapList(&$val) {
        if (empty($val) || empty($this->listMap)) {
            return false;
        }
        foreach ($this->listMap as $ckey => $cval) {
            if (!isset($val[$ckey])) {
                continue;
            }
            $val[$cval] = $val[$ckey];
            unset($val[$ckey]);
        }
    }

	
    /**
     * 获取格式的结果
     * @return void
     */
    private function setDecodeData() {
        $this->result = $this->decodeData($this->result);
    }
 
    /**
     * 获取错误码
     * @return int
     */
    abstract public function getCode();

	
    /**
     * 获取错误
     * @return string
     */
    public function getError() {
        return $this->err;
    }

	
    /**
     * 解析接口返回数据
     * @param  string  $data  [数据]
     * @param  boolean $toArr [是否转换成数组]
     * @return [type]         [description]
     */
    abstract protected function decodeData($data = '');
	
	
	//添加接口驱动
    protected function addApiAdapter($obj){
		$this->apiAdapter = $obj;
	}
	
	//获取当前调用对象的名称
	private function getClassName(){
		return Common::getClassNameByNameSpace($this);
	}
	
	 /**
     * 自动验证
     * @return [type]         [description]
     */
	protected function autoValidate(){
		$className = Common::getClassNameByNameSpace($this);
		$class = "\Module\Mapper\\$className";
		if(!class_exists($class)){
			return false;
		}
		$obj = $class::instance();
		$field = $this->getMapperDate($className,'Field');

		if(empty($field)){
			$field = array_keys($this->requestData);
		}
		$obj->field = $field;
		$obj->valid = $this->getMapperDate($className,'Valid');
		$obj->auto = $this->getMapperDate($className,'Auto');
		$this->requestData = $obj->setData($this->requestData);
        $err = $obj->err;
        if ($err) {
            throw new Exception($err);
        }
	}
	
	//获取验证规则
	private function getMapperDate($className,$model=''){
		$class = "\Module\Mapper\\$model\\$className";
		if(!class_exists($class)){
			return false;
		}
		$obj = new $class();
		if(!property_exists($obj,$this->serviceName)){
			return false;
		}
		return $obj->{$this->serviceName};
	}
	
	
     /**
     * 接口请求
     * @return [type]              [description]
     */
    protected function requestApi(array $requestData=array()) {
		if(!empty($requestData)){
			$this->requestData = $requestData;
		}
		$this->result = null;
        try {
			if (!empty($this->err)) {
				//throw new ApiException($this->err);
			}
			$this->autoValidate();//自动验证
            if (empty($this->requestData)) {
                throw new ApiException('请求参数不能为空');
            }
            if (empty($this->serviceName)) {
                throw new ApiException('接口名称不能为空');
            }
            $result = $this->apiAdapter->httpRequest($this->requestData, $this->serviceName,$this->getClassName());
            if (empty($result)) {
                throw new ApiException('页面请求失败');
            }
            $this->result = $result; //设置结果
            $this->setDecodeData(); //设置代码解析
            $this->checkError();//定义错误
        } catch (Exception $e) {
            $this->setException($e);
        }
		return $this;
    }
}
