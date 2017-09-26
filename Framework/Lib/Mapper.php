<?php

/**
 * 数据
 */

namespace Core\Lib;

use \Core\Lib\ApiException as ApiException;
use \Exception as Exception;

abstract class Mapper {

    public $data = array(); //返回的数据
    public $field = array(); //数据列
    public $err = ''; //错误信息
    public $auto = array(); //自动填充
    public $valid = array(); //验证规则
	
	private static $single = null;//对象单列
	
    private function __construct() {
        $this->setDefaultData();
    }

	static public function instance(){
		if(!empty(self::$single)){
			self::$single;
		}else{
			self::$single = new static();
		}
		return self::$single;
	}
	
    //获取数据
    protected function getData() {
        return $this->data;
    }

    //设置数据
    public function setData(array $data) {
		$this->data = array();
        try {
			$this->setDefaultData();
            foreach ($data as $key => $val) {
                if (in_array($key, $this->field)) {
                    $this->data[$key] = $val;
                }
            }
            $this->autoValidate();
            return $this->data;
        } catch (Exception $e) {
            $this->setException($e);
        }
    }

    /*
      自动填充
     */
    private function autoFill() {
		if(empty($this->auto)){
			return false;
		}
        foreach ($this->auto as $k => $v) {
            if (array_key_exists($v[0], $this->data)) {
                switch ($v[1]) {
                    case 'value':
                        $this->data[$v[0]] = $v[2];
                        break;
                    case 'function':
                        $this->data[$v[0]] = call_user_func($v[2]);
                        break;
                }
            }
        }
    }

    //自动验证
    private function autoValidate() {
        if (empty($this->valid)) {
            return true;
        }
        foreach ($this->valid as $k => $v) {
            switch ($v[1]) {
                case 1:
                    if (!isset($this->data[$v[0]])) {
                        throw new Exception($v[2]);
                    }
                    if (!isset($v[4])) {
                        $v[4] = '';
                    }
                    if (!$this->check($this->data[$v[0]], $v[3], $v[4])) {
                        throw new Exception($v[2]);
                    }
                    break;
                case 0:
                    if (isset($this->data[$v[0]])) {
                        if (!$this->check($this->data[$v[0]], $v[3], $v[4])) {
                            throw new Exception($v[2]);
                        }
                    }
                    break;
                case 2:
                    if (isset($this->data[$v[0]]) && !empty($this->data[$v[0]])) {
                        if (!$this->check($this->data[$v[0]], $v[3], $v[4])) {
                            throw new Exception($v[2]);
                        }
                    }
            }
        }
        return true;
    }

    //检查数据类型
    protected function check($value, $rule = '', $parm = '') {
        switch ($rule) {
            case 'require':
                return !empty($value);
            case 'number':
                return is_numeric($value);
            case 'in':
                $tmp = explode(',', $parm);
                return in_array($value, $tmp);
            case 'between':
                list($min, $max) = explode(',', $parm);
                return $value >= $min && $value <= $max;
            case 'length':
                list($min, $max) = explode(',', $parm);
                return strlen($value) >= $min && strlen($value) <= $max;
            case 'email':
                // 判断$value是否是email,可以用正则表达式,但现在没学.
                // 因此,此处用系统函数来判断
                return (filter_var($value, FILTER_VALIDATE_EMAIL) !== false);
            default:
                return false;
        }
    }

    //设置列
    public function setFields(array $data) {
        $this->field = $data;
    }

    //设置默认数据
    private function setDefaultData() {
        foreach ($this->field as $val) {
            $this->data[$val] = '';
        }
        $this->autoFill();
    }

    //检查数据包格式
    private function checkData() {
        foreach ($this->data as $key => $val) {
            if (!in_array($key, $this->field)) {
                unset($this->data[$key]);
            }
        }
        foreach ($this->field as $val) {
            if (!isset($this->data[$val])) {
                $this->data[$val] = '';
            }
        }
    }

    //设置异常
    private function setException(Exception $e) {
        $this->err = $e->getMessage();
    }

    //设置
    public function __set($name, $val) {
        try {
            if (!in_array($name, $this->field)) {
                throw new Exception($name.'列不存在');
            }
            $this->data[$name] = $val;
        } catch (Exception $e) {
            $this->setException($e);
        }
    }

    //获取
    public function __get($name) {
        try {
            if (!in_array($name, $this->field)) {
                throw new Exception($name.'列不存在');
            }
            return $this->data[$name];
        } catch (Exception $e) {
            $this->setException($e);
        }
    }

}
