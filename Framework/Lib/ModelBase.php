<?php
/**
 * ModelBase file.
 *
 * @author WangChengjin
 */

namespace Core\Lib;

/**
 * Exeptions issued from Models.
 */
class ModelException extends \Exception {

}

/**
 * Abstract model,included commond methods for data access and manipulations for derived classes.
 *
 */
abstract class ModelBase {
	/**
	 *
	 * Instances of the derived classes.
	 * @var array
	 */
	protected static $instances = array();

	/**
	 *
	 * @var \Db\Connection
	 */
	protected static $db;

	/**
	 * holds the table's field values which can be accessed via the magic __get, and these fields should be defined in the static $fields property of the derived class.
	 *
	 * @var array
	 */
	protected $fieldProperties = array();

	 // 操作状态
    const MODEL_INSERT          =   1;      //  插入模型数据
    const MODEL_UPDATE          =   2;      //  更新模型数据
    const MODEL_BOTH            =   3;      //  包含上面两种方式
    const MUST_VALIDATE         =   1;      // 必须验证
    const EXISTS_VALIDATE       =   0;      // 表单存在字段则验证
    const VALUE_VALIDATE        =   2;      // 表单值不为空则验证
	
	 // 最近错误信息
    protected $error            =   '';
	
	// 是否自动检测数据表字段信息
    protected $autoCheckFields  =   true;
    // 是否批处理验证
    protected $patchValidate    =   false;
	
	protected $_validate        =   array();  // 自动验证定义
	
	// 主键名称
    protected static $pk               =   '';
	/**
	 * get instance of the derived class
	 * @return \Core\Lib\ModelBase
	 */
	public static function instance() {
		$className = get_called_class();
		if (!isset(self::$instances[$className])) {
			self::$instances[$className] = new $className;
		}
		return self::$instances[$className];
	}

	/**
	 * magic __get method. You can access the instance of the default DbConnection and field, if filled, values directly.
	 *
	 * @param string $name
	 * @return \Core\Lib\DbConnection|multitype:
	 */
	public function __get($name) {
		switch ($name) {
			case 'db';
				return $this->getDb();
				continue;
			case 'redis':
				return $this->redis();
            case 'Api':
                return $this->Api();
			default:
				if (isset($this->fieldProperties[$name])) {
					return $this->fieldProperties[$name];
					continue;
				}
				Log::instance()->log('try get undefined property "' . $name . '" of class ' . get_called_class() . '. Forgot to call fillFields ?', array('trace_depth' => 2));
				continue;
		}
	}

	/**
	 * 获取cache.
	 *
	 * @param string $endpoint 获取的memcache名字.
	 *
	 * @return \Core\Lib\Memcache
	 */
	public function cache($endpoint = 'default') {
		return \Core\Lib\MemcachePool::instance($endpoint);
	}

	/**
	 * Get a redis instance.
	 * @param string $endpoint connection configruation name.
	 * @param string $as use redis as "cache" or storage. default: storage
	 * @return \RedisCache|\RedisStorage
	 */
	public function redis($endpoint = 'default', $as = 'storage') {
		return \Core\Lib\RedisDistributed::instance($endpoint, $as);
	}

	/**
	 * get a instance of DbConnection of the specified connecton name.
	 *
	 * @param string $name database configuration name that defined in Config\Db
	 * @return \Core\Lib\DbConnection
	 */
	public function getDb($name = 'default') {
		if (!self::$db || (self::$db instanceof \Core\Lib\DbConnection)) {
			self::$db = \Core\Lib\DbConnection::instance();
			self::$db->setCfgName($name);
		}
		return self::$db;
	}

	/**
	 * 获取平台架构监控日志对象.
	 *
	 * @param string $app 应用名称.
	 *
	 * @return mixed
	 */
	public function getMNLogger($app) {
		static $loggers = array();
		if (!isset($loggers[$app])) {
			$config = Sys::getCfg('MNLogger');
			if (!property_exists($config, $app)) {
				throw new Exception('Missing configuration for `MNLogger::' . $app . '`');
			}
			$loggers[$app] = new \Core\Lib\MNLogger\MNLogger($config->$app);
		}
		return $loggers[$app];
	}

	/**
	 * fill the table fields with the values. The fields that absent in the $value keys will be filled with null, while the keys which not defined in the $fields property will be ignored.
	 *
	 * @param array $values  e.g. array('id'=>32, 'user_name' => 'chaos' )
	 * @return \Core\Lib\ModelBase  the instance of the calss.array
	 * @throws \Core\Lib\ModelException
	 */
	public function fillFields(array $values) {
		if (!property_exists($this, 'fields') || !is_array($this::$fields)) {
			throw new ModelException('You cannot call this method, $fields propery is not defined in class ' . get_class($this) . ' or is not an array!');
		}

		$this->fieldProperties = array();

		foreach ($values as $k => $v) {
			if (!in_array($k, $this::$fields)) {
				Log::instance()->log('Try to fill a field "' . $k . '" that is not defined in property "fields" of Model ' . get_class($this) . '. Is it a typo ?', array('type' => E_USER_WARNING, 'trace_depth' => 2));
			} else {
				$this->fieldProperties[$k] = $v;
			}
		}
		return $this;
	}

	/**
	 * save record to db.<b>please ensure you're on the correct db connection before you call this method.</b>
	 *
	 * @param array $fieldValues
	 */
	public function save(array $fieldValues = array()) {
		$this->fillFields($fieldValues);
		if (count($this->fieldProperties) < 1) {
			throw new ModelException('Empty fields to save !');
		}
		if (!isset($this->fieldProperties[$this::PRIMARY_KEY])) {
			return $this->db->write()->insert($this::TABLE_NAME, $this->fieldProperties);
		} else {
			return $this->db->write()->update($this::TABLE_NAME, $this->fieldProperties, array($this::PRIMARY_KEY => $this->fieldProperties[$this::PRIMARY_KEY]));
		}
	}
	
	/**
	 * 业务日志,需要根据具体业务配置Core\Config\Log.
	 *
	 * @param string $cfgName 日志配置.
	 * @param array  $data    日志记录.
	 */
	public function log($cfgName, $data) {
		return \Core\Lib\Log::instance($cfgName)->log($data);
	}
	
	
	
	/**
     * 自动表单验证
     * @access protected
     * @param array $data 创建数据
     * @param string $type 创建类型
     * @return boolean
     */
    protected function autoValidation($data=array(),$type=1) {
		empty($data) && $data = $_POST;  //默认数据
        !empty($this->_validate) &&  $_validate   =   $this->_validate;
        // 属性验证
        if(isset($_validate)) { // 如果设置了数据自动验证则进行数据验证
            if($this->patchValidate) { // 重置验证错误信息
                $this->error = array();
            }
            foreach($_validate as $key=>$val) {
                // 验证因子定义格式
                // array(field,rule,message,condition,type,when,params)
                // 判断是否需要执行验证
                if(empty($val[5]) || ( $val[5]== self::MODEL_BOTH && $type < 3 ) || $val[5]== $type ) {
                    if(0==strpos($val[2],'{%') && strpos($val[2],'}'))
                        // 支持提示信息的多语言 使用 {%语言定义} 方式
                        $val[2]  =  substr($val[2],2,-1);
                    $val[3]  =  isset($val[3])?$val[3]:self::EXISTS_VALIDATE;
                    $val[4]  =  isset($val[4])?$val[4]:'regex';
                    // 判断验证条件
                    switch($val[3]) {
                        case self::MUST_VALIDATE:   // 必须验证 不管表单是否有设置该字段
                            if(false === $this->_validationField($data,$val)) 
                                return false;
                            break;
                        case self::VALUE_VALIDATE:    // 值不为空的时候才验证
                            if('' != trim($data[$val[0]]))
                                if(false === $this->_validationField($data,$val)) 
                                    return false;
                            break;
                        default:    // 默认表单存在该字段就验证
                            if(isset($data[$val[0]]))
                                if(false === $this->_validationField($data,$val)) 
                                    return false;
                    }
                }
            }
            // 批量验证的时候最后返回错误
            if(!empty($this->error)) return false;
        }
        return true;
    }

	/**
     * 验证表单字段 支持批量验证
     * 如果批量验证返回错误的数组信息
     * @access protected
     * @param array $data 创建数据
     * @param array $val 验证因子
     * @return boolean
     */
    protected function _validationField($data,$val) {

        if($this->patchValidate && isset($this->error[$val[0]]))
            return ; //当前字段已经有规则验证没有通过
		
        if(false === $this->_validationFieldItem($data,$val)){
            if($this->patchValidate) {
                $this->error[$val[0]]   =   $val[2];
            }else{
                $this->error            =   $val[2];
                return false;
            }
        }
        return ;
    }
	
	  /**
     * 返回模型的错误信息
     * @access public
     * @return string
     */
    public function getError(){
        return $this->error;
    }
	
	 /**
     * 获取主键名称
     * @access public
     * @return string
     */
    public function getPk() {
	
		if(empty($this::TABLE_NAME))
			return ;
		if(empty(self::$pk)){
			$info = $this->db->read()->queryAll('desc '.$this::TABLE_NAME);
			foreach($info as $val){
				if($val['Key']=='PRI'){
					self::$pk=$val['Field'];
					break;
				}
			}
			return self::$pk;
		}
		return self::$pk;
    }

	
	 /**
     * 根据验证因子验证字段
     * @access protected
     * @param array $data 创建数据
     * @param array $val 验证因子
     * @return boolean
     */
    protected function _validationFieldItem($data,$val) {
	
        switch(strtolower(trim($val[4]))) {
            case 'function':// 使用函数进行验证
            case 'callback':// 调用方法进行验证
                $args = isset($val[6])?(array)$val[6]:array();
                if(is_string($val[0]) && strpos($val[0], ','))
                    $val[0] = explode(',', $val[0]);
                if(is_array($val[0])){
                    // 支持多个字段验证
                    foreach($val[0] as $field)
                        (isset($data[$field]) && $_data[$field] = $data[$field])||$_data[$field]='';
                    array_unshift($args, $_data);
                }else{
                    array_unshift($args, $data[$val[0]]);
                }
                if('function'==$val[4]) {
                    return call_user_func_array($val[1], $args);
                }else{
                    return call_user_func_array(array(&$this, $val[1]), $args);
                }
            case 'confirm': // 验证两个字段是否相同
                return $data[$val[0]] == $data[$val[1]];
            case 'unique': // 验证某个值是否唯一
				
                if(is_string($val[0]) && strpos($val[0],','))
                    $val[0]  =  explode(',',$val[0]);
                $map = array();
                if(is_array($val[0])) {
                    // 支持多个字段验证
                    foreach ($val[0] as $field)
                        (isset($data[$field]) && $map[$field]   =  $data[$field]) || $map[$field]='';
                }else{
                    $map[$val[0]] = $data[$val[0]];
                }
                $pk =   $this->getPk();
                if(!empty($data[$pk]) && is_string($pk)) { // 完善编辑的时候验证唯一
                    $map[$pk.'<>'] = $data[$pk];
                }
                if($this->db->read()->select()->from($this::TABLE_NAME)->where($map)->queryRow())   return false;
                return true;
            default:  // 检查附加规则
                return $this->check($data[$val[0]],$val[1],$val[4]);
        }
    }
	
	
	 /**
     * 验证数据 支持 in between equal length regex expire ip_allow ip_deny
     * @access public
     * @param string $value 验证数据
     * @param mixed $rule 验证表达式
     * @param string $type 验证方式 默认为正则验证
     * @return boolean
     */
    public function check($value,$rule,$type='regex'){
		
        $type   =   strtolower(trim($type));
        switch($type) {
            case 'in': // 验证是否在某个指定范围之内 逗号分隔字符串或者数组
            case 'notin':
                $range   = is_array($rule)? $rule : explode(',',$rule);
                return $type == 'in' ? in_array($value ,$range) : !in_array($value ,$range);
            case 'between': // 验证是否在某个范围
            case 'notbetween': // 验证是否不在某个范围            
                if (is_array($rule)){
                    $min    =    $rule[0];
                    $max    =    $rule[1];
                }else{
                    list($min,$max)   =  explode(',',$rule);
                }
                return $type == 'between' ? $value>=$min && $value<=$max : $value<$min || $value>$max;
            case 'equal': // 验证是否等于某个值
            case 'notequal': // 验证是否等于某个值            
                return $type == 'equal' ? $value == $rule : $value != $rule;
            case 'length': // 验证长度
                $length  =  mb_strlen($value,'utf-8'); // 当前数据长度
                if(strpos($rule,',')) { // 长度区间
                    list($min,$max)   =  explode(',',$rule);
                    return $length >= $min && $length <= $max;
                }else{// 指定长度
                    return $length == $rule;
                }
            case 'expire':
                list($start,$end)   =  explode(',',$rule);
                if(!is_numeric($start)) $start   =  strtotime($start);
                if(!is_numeric($end)) $end   =  strtotime($end);
                return NOW_TIME >= $start && NOW_TIME <= $end;
            case 'ip_allow': // IP 操作许可验证
                return in_array(get_client_ip(),explode(',',$rule));
            case 'ip_deny': // IP 操作禁止验证
                return !in_array(get_client_ip(),explode(',',$rule));
            case 'regex':
            default:    // 默认使用正则验证 可以使用验证类中定义的验证名称
                // 检查附加规则
                return $this->regex($value,$rule);
        }
    }
	
	
	/**
     * 使用正则验证数据
     * @access public
     * @param string $value  要验证的数据
     * @param string $rule 验证规则
     * @return boolean
     */
    public function regex($value,$rule) {
        $validate = array(
            'require'   =>  '/\S+/',
            'email'     =>  '/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/',
            'url'       =>  '/^http(s?):\/\/(?:[A-za-z0-9-]+\.)+[A-za-z]{2,4}(:\d+)?(?:[\/\?#][\/=\?%\-&~`@[\]\':+!\.#\w]*)?$/',
            'currency'  =>  '/^\d+(\.\d+)?$/',
            'number'    =>  '/^\d+$/',
            'zip'       =>  '/^\d{6}$/',
            'integer'   =>  '/^[-\+]?\d+$/',
            'double'    =>  '/^[-\+]?\d+(\.\d+)?$/',
            'english'   =>  '/^[A-Za-z]+$/',

			'positive_integer'   =>  '/^[0-9]*[1-9][0-9]*$/', //正整数
            'date'      =>  '/^\d{4}(\-|\/|.)\d{1,2}\1\d{1,2}$/',
            'datetime'  =>  '/^\d{4}(\-|\/|.)\d{1,2}\1\d{1,2} \d{1,2}:\d{1,2}:\d{1,2}$/'

        );
        // 检查是否有内置的正则表达式
        if(isset($validate[strtolower($rule)]))
            $rule       =   $validate[strtolower($rule)];
        return preg_match($rule,$value)===1;
    }


    /**
     * 商城内部接口类
     * @access 
     */

    public function Api(){

        return \Core\Lib\WmApi::instance();
    }
	
    public function pagination($sql = "",$eachNum = 30,$show = 2){
        if(trim($sql) == ""){
            return false;
        }

        $countSql = "select count(1) as cnt from ({$sql})t";

        $count = $this->db->write()->queryRow($countSql);

        
        if($count){
            $count = $count['cnt'];
        }else{
            $count = 0;
        }


        $pager = new \Core\Lib\Page();
        $pager -> setEachNum($eachNum);
        $start = $pager->getLimitStart();
        $end   = $pager->getEachNum();
        $pager->setTotalNum($count);

        $sql .=" limit {$start},{$end} ";
        $rs = $this->db->write()->queryAll($sql);
        if($rs){
          $result['data'] = $rs;
          $result['page_str'] = $pager->show($show);
          $result['page_count'] = $pager->getTotalPage();
          $result['curr_page'] = $pager->getNowPage();
        }
        
        return  $result;


    }
}
