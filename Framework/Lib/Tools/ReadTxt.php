<?php

namespace Core\Lib\Tools;
use \Exception as Exception;

class ReadTxt
{
	public static $ins;//class self
	private $fileSource;//文件内容
	public $page;//第几页
	public $pageSize;//每页多少条
	public $step;//总共切分为几次
	public $startPos;//开始位置
	public $fileCount;//文件总行
	public $filterList = array('phone'=>'/^\d{11}$/');
	public $findList = array('用户号码'=>'phone','订购流量包时间'=>'login_time');//需要查找的列
	public $posList; //位置列表
	public $combinData=array();//要合并的数据
	
	
	//构造
	private function __construct(){
		set_time_limit(0);
		ini_set('memory_limit','1024M');
	}
	
	//初始化系统
	public static function getIns(){
		if(!empty(self::$ins)){
			return self::$ins;
		}
		return self::$ins = new self();
	}
	
	//读取txt文件
	private function readContent($file){
		if(empty($file)){
			throw new Exception('文件名称不能为空');
		}
		$file = ltrim($file,'/');
		$this->fileSource = fopen($file,"r");
		if(empty($this->fileSource)){
			throw new Exception('文件读取错误');
		}
		return $this->fileSource;
	}
	
	//获取文件总行数
	private function getTxtCount(){
		$line = 0 ; //初始化行数  
		if($this->fileSource){  
			//获取文件的一行内容，注意：需要php5才支持该函数；  
			while(stream_get_line($this->fileSource,8192,"\n")){  
				$line++;  
		    }
		}
		fseek($this->fileSource,0);
		stream_get_line($this->fileSource,8192,"\n");
		return $this->fileCount = $line;
	}
	
	//获取查找列的位置
	private function getPosList(){
		$data = fgets($this->fileSource);
		if(empty($data)){
			throw new Exception('文件列不存在');
		}
		$data = $this->strConv($data);
		$data = explode("\t",$data);
		foreach($this->findList as $key=>$val){
			$posList[$val] = array_search($key,$data);
		}
		return $this->posList =  $posList;
	}
	
	
	//编码转换
	protected function strConv($str){
		$encode = mb_detect_encoding($str, array("ASCII",'UTF-8',"GB2312",'GBK','BIG5')); 
		return mb_convert_encoding($str,"UTF-8",$encode);
	}
	
	//获取开始位置
	private function getStartPos(){
		$size = ceil($this->fileCount/$this->step);//总共切分为几次
		return $this->startPos = (($this->page-1)*$size); //开始位置
	}
	
	//每页分页大小
	private function setPageSize(){
		$this->pageSize = $this->startPos+ceil($this->fileCount/$this->step); //每页多少条
		if($this->pageSize>$this->fileCount){
			$this->pageSize = $this->fileCount;
		}
	}
	
	//获取行
	public function getRows($file){
		$this->readContent($file); //读取txt文件
		$posList = $this->getPosList();
		$this->getTxtCount(); //获取文件总行数
		$start=$this->getStartPos(); //获取开始位置
		$this->setPageSize();//设置每页多少条
		if(empty($posList)){
			throw new Exception('读取文件出错');
		}
		if($start==$this->pageSize){
			throw new Exception('读取文件出错');
		}
		$i=0;
		$end=$this->pageSize;
		$list = array();
		while(($file = fgets($this->fileSource))){
			$i++;
			if(!($i>$start && $i<=$end)){
				continue;
			}
		    if($i>$end){
				break;
		    }
			$data = $this->strConv($file);
			$data = explode("\t",$data);
			if($file){
				$idata = array();
				foreach($posList as $key=>$val){
					$bool = $this->filter($data[$val],$val);
					if(empty($bool)){
						continue 2;
					}
					$idata[$key] = $data[$val];
				}
				$idata = array_merge($idata,$this->combinData);
				if(!empty($idata)){
					$result[] = $idata;
				}
			}
			unset($file,$idata,$bool,$data);
		}
		return $result;
	}

	
	//过滤不合格的数据
	protected function filter($data,$field){
		if(empty($data[$val])){
			return false;
		}
		$field = array_search($field,$this->posList);
		foreach($this->filterList as $key=>$val){
			if(!preg_match($val,$data) && $key==$field){
				return false;
			}
		}
		return true;
	}
}
