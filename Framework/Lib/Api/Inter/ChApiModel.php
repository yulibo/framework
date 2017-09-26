<?php

/**
 * 接口父类 --- 恒尚接口实现 
 */

namespace Core\Lib\Api\Inter;

use \Core\Lib\ApiException as ApiException;
use \Exception as Exception;
use \Module\Common as Common;

abstract class ChApiModel extends \Core\Lib\Api\ApiBaseModel {

	protected static $successCode = 1; //成功的状态码
	
	protected function __construct(){
		$this->apiAdapter = \Core\Lib\Api\Inter\ChApi::instance();
	}
	
	
	//获取结果错误
	public function getResultError(){
		return isset($this->result['ERROR_MESSAGE']) ? $this->result['ERROR_MESSAGE'] : '';//获取结果错误 
	}

	
    //设置接口数据
	public function setDataResult(){
		$this->dataResult = isset($this->result) ? $this->result : array();
	}

	
    /**
     * 获取错误码
     * @return int
     */
    public function getCode() {
        $this->code = isset($this->result['ERROR_CODE']) ? $this->result['ERROR_CODE'] : 1;
        return $this->code;
    }


    /**
     * 解析接口返回数据
     * @param  string  $data  [数据]
     * @return [type]         [description]
     */
    protected function decodeData($data = '') {
        return json_decode($data, true);
    }
}
