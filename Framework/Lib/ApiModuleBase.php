<?php


namespace Core\Lib;
use \Module\Common as Common;

abstract class ApiModuleBase  extends ModuleBase
{
	protected $modelName = '';//model名称
	private static $modeObj = '';//model 对象
	private static $modeList;//对象列表
    public function __construct(){
		
	}
	final function __clone(){
		
	}
	
	//获取服务
    public function getService($className=''){
		if(empty($className)){
			$className = Common::getClassNameByNameSpace($this);
		}
		if(!isset(self::$modeList[$className]) || self::$modeList[$className]==null){
			$class = "\Model\Api\\$className";
			if(!class_exists($class) || ($class instanceof ApiModel)){
				throw new \Exception("$class找不到或者不是ApiModel");
			}
			self::$modeList[$className] = $class::instance();
		}
		self::$modeObj = self::$modeList[$className];
		return self::$modeObj;
	}
	
	//设置错误为空
	public function setErrorNull(){
		if(self::$modeObj){
			self::$modeObj->err = '';
		}
	}
	
	//获取错误信息
	public function getError(){
		return self::$modeObj->getError();
	}
	
	//获取错误号码
	public function getCode(){
		return self::$modeObj->getCode();
	}
	
	//获取结果
	public function getResult(){
		return self::$modeObj->getResult();
	}
	

	
	//web分页列表
	public function getPageFormatListResult($pageNow,$pageSize){
		return self::$modeObj->getPageFormatListResult($pageNow,$pageSize);
	}
	
	 /**
     * 格式化分页列表结果 for mobile
     * @param int $pageNow 当前页
     * @param int $pageSize 页面条数
     * @return array
     */
	public function getListPageMobile($pageNow,$pageSize){
		return self::$modeObj->getListPageMobile($pageNow,$pageSize);
	}
	
	/**
     * 获取分页的列表数据
     * @return array
     */
	public function getPageResult(){
		return self::$modeObj->getPageResult();
	}
}