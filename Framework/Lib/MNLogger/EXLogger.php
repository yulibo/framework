<?php
namespace Core\Lib\MNLogger;

class EXLogger extends Base {
    protected static $filePermission = 0777;
    protected $_logdirBaseName = 'exception';
    protected static $configs=array();
    protected static $instance = array();
    protected static $handlerRegistered = false;
    protected $exceptionHandlerOwned = false;

    public function __construct($config)
    {
        if(!static::$handlerRegistered)
        {
            // 此方法仍有某些情况无法追踪错误, 比如: 内存溢出。只有在php的内部日志中才能查到.
            register_shutdown_function(array($this, "log_fatal_handler"));
            $previous = set_exception_handler(array($this, "log_exception"));
            if($previous)
            {// 如果业务已有自己的exception handler,为了不影响其原有行为，此处不做覆盖，但需要在业务的exception handler中添加MNLogger\EXLogger::log($ex)的调用。
                restore_exception_handler();
            }
            else
            {
                $this->exceptionHandlerOwned = true;
            }
            static::$handlerRegistered = true;
        }
        parent::__construct($config);
    }

    /**
     * @param string $config
     * @return static
     */
    public static function instance($config='exception')
    {
        return parent::instance($config);
    }

    /**
     * Initialize parameters/configs of logger. Then Logger::instance($configname) can retrieve an instance by config name.EXLogger::setUp() will initialize all instances.
     * @param array $configs
     */
    public static function setUp(array $configs)
    {
        foreach($configs as $k => $config)
        {
            if($k !== 'exception')
            {
                continue;
            }
            static::instance($config);
        }
        return parent::setUp($configs);
    }

    public function log_fatal_handler() {
    	$errors = error_get_last();
        if(strpos($errors['message'], 'Uncaught exception') === 0 && $this->exceptionHandlerOwned)
        {// 不和exception_handler重复记录同一条异常日志.
            return true;
        }
	    if ($errors["type"] == E_ERROR || $errors['type'] == E_USER_ERROR) {
	    	$error_msg = "\n". $errors['type'] . " {$errors['message']} in {$errors['file']} on line {$errors['line']}:\n";
	    	$error_msg .= $this->debug_backtrace_string();
            $this->_log('ERROR', $error_msg);
	    }
	}

	public function log_error($errno, $errstr, $errfile, $errline) {
        if($errno == E_ERROR || $errno == E_USER_ERROR) {
            $error_msg = "\n". $errno . " {$errstr} in {$errfile} on line {$errline}:\n";
            $error_msg .= $this->debug_backtrace_string();
            $this->_log('ERROR', $error_msg);
            throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
        }
        return false;
	}

	private function debug_backtrace_string() {
	    $stack = '';
	    $i = 0;
	    $trace = debug_backtrace();
	    unset($trace[0]); //Remove call to this function from stack trace
	    unset($trace[1]); //Remove call to log_error function from stack trace
	    foreach($trace as $node) {
            if(isset($node['file']) && isset($node['line'])) {
                $stack .= "#$i ".$node['file'] ."(" .$node['line']."): "; 
            }
	        if(isset($node['class'])) {
	            $stack .= $node['class'] . "->"; 
	        }
	        $stack .= $node['function'] . "()" . PHP_EOL;
	        $i++;
	    }
	    return $stack;
	} 

     // This exception will throw out and caught a fault error, or will log the error twice.
	 public function log_exception($e) {
	 	$this->_log_exception($e, false);
        // 继续抛出, 不影响框架引用者原有处理逻辑
        throw $e;
	 }

	private function _log_exception($e, $caught) {
		if($caught) {
			$type = 'WARN';
		} else {
			$type = 'ERROR';
		}
		//$class_name = get_class($e);
		$msg = $e->getMessage();
		$file = $e->getFile();
		$line = $e->getLine();
		$error_msg = "\n{$msg} in {$file} on line {$line}:\n". $e->getTraceAsString(). "\n";
        
        if(DEBUG_MODE){
        echo "  <style>body{margin:0px;padding:0px}</style>
                <div style='
                background-color: #f2dede;  
                border: 1px solid #eee;
                border-left-color:#ce4844;
                border-left-width: 5px;
                border-radius: 3px;
                padding:0px 10px 0px 10px;
                '>
                <h3 style='color:#ce4844;'>I'm EXLogger message.</h3>
                <pre style='white-space: pre;
                word-wrap: break-word;line-height: 1.4em;'>";
        print_r($error_msg);
        echo "</pre></div>";           
        }    
        $this->_log($type, $error_msg);
	}

    public function log($e) {
    	$this->_log_exception($e, true);

    }

    private function _log($type, $exception)
    {
        if ($this->_on === self::OFF) {
            return;
        }

        global $owl_context;

        if(!isset($owl_context['uuid'])) {
            $owl_context['uuid'] = '';
        }

        if(!isset($owl_context['trace_id'])) {
            $owl_context['trace_id'] = '';
        }

        $start = (int)(microtime(true)*1000);
        $line = "\nBegin:" . date('Y-m-d H:i:s') . "\n";
        $line .= "OWL {$this->_app} {$this->_ip}{$owl_context['uuid']}{$owl_context['trace_id']} Exception {$type} {$exception}";
        $end = (int)(microtime(true)*1000);
        $line .= "End:" . date('Y-m-d H:i:s') . "  Total:" . ($end - $start) . "ms";
        
        if (!$this->_fileHandle) {
            $this->_fileHandle = fopen($this->_logFilePath, 'a');
            if (!$this->_fileHandle) {
                throw new \Exception('Can not open file: ' . $this->_logFilePath);
            }
        }
        if (!fwrite($this->_fileHandle, $line)) {
            throw new \Exception('Can not append to file: ' . $this->_logFilePath);
        }
    }
}