<?php

/**
 * 接口父类 --- java
 */

namespace Core\Lib;

use \Core\Lib\ApiException as ApiException;
use \Exception as Exception;
use \Module\Common as Common;
use \Core\Lib\Api\ApiBaseModel as ApiBaseModel;
use \Core\Lib\Api\ApiPage as ApiPage;

abstract class ApiModel extends ApiBaseModel implements ApiPage {

	const PAGECOUNT_KEY = 'pageCount';//数据列表总数
	const PAGEDATA_KYE = 'pageDatas';//分页数据key
	
	protected static $successCode = 1; //成功的状态号码
	
	protected function __construct(){
		$this->apiAdapter = \Core\Lib\WmApi::instance();
		
	}
	

	//获取结果错误
	public function getResultError(){
		return isset($this->result['error']) ? $this->result['error'] : '';//获取结果错误 
	}

	
	
    //设置接口数据
	public function setDataResult(){
		$this->dataResult = isset($this->result['rspData']) ? $this->result['rspData'] : array();
        $count = count($this->dataResult);
		$this->dataResult = ($count==1) ? current($this->dataResult) :$this->dataResult;
	}

	
    /**
     * 获取错误码
     * @return int
     */
    public function getCode() {
        $this->code = isset($this->result['result']) ? $this->result['result'] : 0;
        return $this->code;
    }


    /**
     * 解析接口返回数据
     * @param  string  $data  [数据]
     * @param  boolean $toArr [是否转换成数组]
     * @return [type]         [description]
     */
    protected function decodeData($data = '') {
        $data = json_decode($data, true);
        if (isset($data['rspData'])) {
            $data['rspData'] = json_decode($data['rspData'],true);
        }
        return $data;
    }

	
	 /**
     * 格式化列表结果
     * @return array
     */
    public function getFormatListResult() {
        $list = $this->getResult();
		if(!isset($list[static::PAGECOUNT_KEY])){
			$list[self::$resultCountKey] = 0;
		}else{
			$list[self::$resultCountKey] = $list[static::PAGECOUNT_KEY];
		}
		if(!isset($list[static::PAGEDATA_KYE])){
			$list[self::$resultDataKey] = array();
		}else{
			$list[self::$resultDataKey] = $list[static::PAGEDATA_KYE];
		}
        return $list;
    }
	
	
	/**
     * 获取分页的列表数据
     * @return array
     */
	public function getPageResult(){
		$list = $this->getResult();
		return   isset($list[self::$resultDataKey])?$list[self::$resultDataKey]:array();
	}
	
	

    /**
     * 格式化分页列表结果 for web
     * @param int $pageNow 当前页
     * @param int $pageSize 页面条数
     * @param int $pageType 分页风格
     * @return array
     */
    public function getPageFormatListResult($pageNow = 1, $pageSize = 10, $pageType = 2) {
        $list = $this->getFormatListResult();
		$pageCount = Common::getPageCount($list[self::$resultCountKey],$pageSize); 
        $pageNow = ($pageCount >= $pageNow) ? $pageNow : 1;
		$pageStr = $this->getPageObj($list[self::$resultCountKey], $pageNow, $pageSize,$pageType);
		empty($list[self::$resultDataKey]) && $pageStr = '';
        return array('page' => $pageStr, 'list' => $list[self::$resultDataKey]);
    }
	
	
	
	//获取分页对象
	private function getPageObj($count,$pageNow,$pageSize,$pageType=2){
		$pager = new \Core\Lib\Page($count, $pageNow, $pageSize);
		return $pager->show($pageType);
	}
		
	 /**
     * 格式化分页列表结果 for mobile
     * @param int $pageNow 当前页
     * @param int $pageSize 页面条数
     * @return array
     */
    public function getListPageMobile($pageNow = 1,$pageSize = 10) {
        $list = $this->getFormatListResult();
		$pageCount = Common::getPageCount($list[self::$resultCountKey],$pageSize);
		($pageCount<$pageNow) && $pageNow = $pageCount;
        return array('pageCount' => $pageCount,'pageNow'=>$pageNow,'list' => $list[self::$resultDataKey]);
    }
	
	
}
