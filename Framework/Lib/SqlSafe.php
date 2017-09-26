<?php

/**
 * sql 安全过滤
 */

namespace Core\Lib;

class SqlSafe {
	
    private $getfilter = "'|(and|or)\\b.+?(>|<|=|in|like)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)";
    private $postfilter = "\\b(and|or)\\b.{1,6}?(=|>|<|\\bin\\b|\\blike\\b)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)";
    private $cookiefilter = "\\b(and|or)\\b.{1,6}?(=|>|<|\\bin\\b|\\blike\\b)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)";
    
    protected static $instance;
	
	/**
     * 构造函数
     */
    private function __construct() {
        foreach($_GET as $key=>$value){$this->stopattack($key,$value,$this->getfilter);}
        foreach($_POST as $key=>$value){$this->stopattack($key,$value,$this->postfilter);}
        foreach($_COOKIE as $key=>$value){$this->stopattack($key,$value,$this->cookiefilter);}
    }
	
	 /**
     *
     * @return self
     */
    public static function instance() {
        if(!static::$instance) {
            static::$instance = new self();
        }
        return static::$instance;
    }
	
    /**
     * 参数检查并写日志
     */
    private function stopattack($StrFiltKey, $StrFiltValue, $ArrFiltReq){
        if(is_array($StrFiltValue))$StrFiltValue = implode($StrFiltValue);
        if (preg_match("/".$ArrFiltReq."/is",$StrFiltValue) == 1){  
            $this->writeslog($_SERVER["REMOTE_ADDR"]."    ".strftime("%Y-%m-%d %H:%M:%S")."    ".$_SERVER["PHP_SELF"]."    ".$_SERVER["REQUEST_METHOD"]."    ".$StrFiltKey."    ".$StrFiltValue);
            $this->showMsg('您提交的参数非法,系统已记录您的本次操作！');
        }
    }
	
	//提示信息展示
	private function showMsg($msg){
		exit($msg);
	}
	
    /**
     * SQL注入日志
     */
    private function writeslog($log){
        $log_path = SYS_LOG.DIRECTORY_SEPARATOR.'sql_log.txt';
        $ts = fopen($log_path,"a+");
        fputs($ts,$log."\r\n");
        fclose($ts);
    }
}