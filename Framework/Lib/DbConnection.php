<?php
namespace Core\Lib;
use \PDO as PDO;
/**
 * mysql only
 * @uses \Core\Lib\Sys
 * @todo More detailed SqlLog
 * @todo Dryrun support
 */
class DbConnection
{
    /**
     * instance of the DbConnection
     * @var \Core\Lib\DbConnection
     */
    protected static $instance;
    protected static $dbConfigs;
    protected $currentCfgName='default';
    protected static $writeConnections = array ();
    protected static $readConnections = array ();
    /**
     * Established connection.
     *
     * @var \Pdo
     */
    protected $connection;

    /**
     * If directly return query result from page caches. Use noCache() method to change this value.
     *
     * @var boolean
     */
    protected $withCache = true;

    /**
     * Cached results of queries in the same page/request.
     *
     * @var array
     */
    protected $cachedPageQueries = array();

    /**
     * If in global transaction. refers to {@link self::beginTransaction}
     *
     * @var Boolean
     */
    protected $inGlobalTransaction=false;
    protected $queryBeginTime;
    protected $queryEndTime;
    protected $connectionCfg = array();
    protected $allowRealExec = true;
    protected $allowSaveToNonExistingPk = false;
    protected $allowGuessConditionOperator = true; //null: allow but warning.      false: not allowed and throw exception.     true: allowed
    protected $autoCloseLastStatement = false;
    protected $lastSql;
    protected $lastStmt;
    protected $select_sql_top;
    protected $select_sql_columns;
    protected $select_sql_from_where;
    protected $select_sql_group_having;
    protected $select_sql_order_limit;

    protected $memoryUsageBeforeFetch;
    protected $memoryUsageAfterFetch;

    const UPDATE_NORMAL = 0;
    const UPDATE_IGNORE = 1;

    const INSERT_ON_DUPLICATE_UPDATE = 'ondup_update';
    const INSERT_ON_DUPLICATE_UPDATE_BUT_SKIP = 'ondup_exclude';
    const INSERT_ON_DUPLICATE_IGNORE = 'ondup_ignore';

    protected function __construct($dsn = null, $username = null, $passwd = null, $options = array())
    {
        if(!self::$dbConfigs)
        {
            self::$dbConfigs = Sys::getCfg('Db');
        }
        if (! is_null ( $dsn ))
        {
            $this->connect($dsn, $username, $passwd, $options);
        }
    }

    /**
     * Get the current connection object. DO NOT heavily use this method in a single script.
     *
     * @return \PDO
     */
    public function getConn()
    {
        $this->reConnect();
        return $this->connection;
    }

    /**
     * Close all connections.
     *
     * @return boolean
     */
    public function closeAll()
    {
        foreach (static::$readConnections as $k => $v)
        {
            $v = null;
            unset(static::$readConnections[$k]);
        }
        foreach (static::$writeConnections as $k => $v)
        {
            $v = null;
            unset(static::$writeConnections[$k]);
        }
        return true;
    }

    /**
     * Clear all connection query caches of a request page.
     */
    public function clearPageCaches()
    {
        foreach (static::$readConnections as $link)
        {
            $link->destroyPageCache();
        }
        foreach (static::$writeConnections as $link)
        {
            $link->destroyPageCache();
        }
        return true;
    }

    /**
     * Clean the query caches of the current connection.
     *
     * @return array
     */
    public function destroyPageCache()
    {
        return $this->cachedPageQueries = array();
    }

    /**
     * get a instance of \Core\Lib\DbConnection
     * @return \Core\Lib\DbConnection
     */
    public static function instance()
    {
        if(!static::$instance)
        {
            static::$instance = new static();
        }
        return static::$instance;
    }
    /**
     * set config name for the current instance. after this all read()/write() method will use this connection of this config.<br />
     * Example:<br />
     * <pre>
     *     <code>
     *     $db = new \Core\Lib\DbConnection();
     *     $db->read('CfgNotOfDefault')->query($sql);
     *     $db->write('CfgNotOfDefault')->query($sql);
     *     //Equals to the following.
     *     $db->setCfg('CfgNotOfDefault');
     *     $db->read()->query($sql);
     *     $db->write()->query($sql);
     *     </code>
     * </pre>
     * @param string $name
     */
    public function setCfgName($name)
    {
        $this->currentCfgName = $name;
    }
    /**
     *
     * @param string $name
     * @throws DbException
     * @return \Core\Lib\DbConnection
     */
    public function write($name = 'default') {
        if($name == 'default' && func_num_args() == 0 && $this->currentCfgName)
        {
            $name = $this->currentCfgName;
        }
        if (empty ( self::$writeConnections[$name] ) && ! $this->addWriteConnection ($name)) {
            throw new DbException ( 'No available write connections. Please use addWriteConnection to initialize  first', 42001 );
        }
        return self::$writeConnections[$name];
    }

    /**
     *
     * @param string $name
     * @throws DbException
     * @return \Core\Lib\DbConnection
     * @todo connection name select
     */
    public function read($name = 'default') {
        if($name == 'default' && func_num_args() == 0 && $this->currentCfgName)
        {
            $name = $this->currentCfgName;
        }
        if (empty ( self::$readConnections[$name] ) && ! $this->addReadConnection ($name)) {
            throw new DbException ( 'No available read connections. Please use addReadConnection to initialize  first', 42001 );
        }
        return  self::$readConnections[$name];
    }
    /**
     * initialize read connections
     *
     * @param string $name
     * @return \Core\Lib\DbConnection
     */
    public function addReadConnection($name = 'default') {
        if (isset(self::$dbConfigs->read [$name] )) {
            $cfg = self::$dbConfigs->read [$name];
            $connection = new self ( $cfg ['dsn'], $cfg ['user'], $cfg ['password'], $cfg ['options'] );
            $connection->connectionCfg = $cfg;
            self::$readConnections [$name] = $connection;
            return self::$readConnections [$name];
        } else {
            throw new DbException ( 'Read configuration of "' . $name . '" is not found.', 42003 );
        }
    }

    /**
     * initialize write connections
     *
     * @param string $name
     * @uses \Core\Lib\DbConnection
     */
    public function addWriteConnection($name = 'default') {
        if (isset(self::$dbConfigs->write [$name] )) {
            $cfg = self::$dbConfigs->write [$name];
            $connection = new self ( $cfg ['dsn'], $cfg ['user'], $cfg ['password'], $cfg ['options'] );
            $connection->connectionCfg = $cfg;
            self::$writeConnections [$name] = $connection;
            return self::$writeConnections [$name];
        } else {
            throw new DbException ( 'Write configuration of "' . $name . '" is not found.', 42003 );
        }
    }
    /**
     *
     * @param string $dsn
     * @param string $user
     * @param string $password
     * @param array $options
     * @return \Core\Lib\DbConnection
     */
    public function connect($dsn, $user = null, $password = null, $options = array())
    {
        if(is_array($dsn))
        {
            extract($dsn);
        }
        if($this->connection)
        {
            return $this->connection;
        }
        else
        {
            try {
                try {
                    $this->connection = new \PDO($dsn, $user, $password, $options);
                } catch (\PDOException $ex) {
                    if ($ex->getCode() === 2002) { // Retry once on connection error.
                        $this->connection = new \PDO($dsn, $user, $password, $options);
                    } else {
                        throw $ex;
                    }
                }
            }
            catch(\Exception $ex)
            {
                $this->logException($ex);
                throw $ex;
            }
        }
        return $this;
    }

    protected function reConnect()
    {
        $this->connection = null;
        return $this->connect($this->connectionCfg);
    }

    public function insert($table, $params, $onDup = null)
    {
        $columns = '';
        $values = '';
        foreach ($params as $column => $value)
        {
            $columns .= $this->quoteObj($column) . ',';
            $values .= is_null($value) ? "NULL," : ($this->quote($value) . ',');
        }
        $columns = substr($columns, 0, strlen($columns) - 1);
        $values = substr($values, 0, strlen($values) - 1);

        $sql_part_ignore = '';
        $sql_part_on_dup = '';

        if (empty($onDup))
        {
            //do nothing, use the default behavior
        }
        else if ($onDup == self::INSERT_ON_DUPLICATE_IGNORE)
        {
            $sql_part_ignore = 'IGNORE';
        }
        else if ($onDup == self::INSERT_ON_DUPLICATE_UPDATE)
        {
            if(func_num_args() >= 4)
                $update_params = func_get_arg(3);
            else
                $update_params = $params;

            $updates = array();
            foreach ($update_params as $column => $value)
            {
                if (is_int($column))
                    $updates[] = "$value";
                else
                    $updates[] = $this->quoteObj($column) . "=" . (is_null($value) ? "null" : $this->quote($value));
            }
            if($updates)
                $sql_part_on_dup = 'ON DUPLICATE KEY UPDATE ' . join(",", $updates);
        }
        else if ($onDup == self::INSERT_ON_DUPLICATE_UPDATE_BUT_SKIP)
        {
            $noUpdateColumnNames = func_get_arg(3);
            if( ! is_array($noUpdateColumnNames))
                throw new Exception('invalid INSERT_ON_DUPLICATE_UPDATE_BUT_SKIP argument');

            $updates = array();
            foreach ($params as $column => $value)
            {
                if (!in_array($column, $noUpdateColumnNames)) {
                    $column = $this->quoteObj($column);
                    $updates[] = "$column=" . (is_null($value) ? "null" : $this->quote($value));
                }
            }
            $sql_part_on_dup = 'ON DUPLICATE KEY UPDATE ' . join(",", $updates);
        }

        $table = $this->quoteObj($table);
        $sql = "INSERT $sql_part_ignore INTO $table ($columns) VALUES ($values) $sql_part_on_dup";
        $ret = $this->exec($sql);
        if ($ret === false)
            return false;
        $id = $this->connection->lastInsertId();
        if ($id)
            return $id;
        return ! ! $ret;

    }

    public function replace($table, $params)
    {
        $columns = '';
        $values = '';
        foreach ($params as $column => $value)
        {
            $columns .= $this->quoteObj($column) . ',';
            $values .= is_null($value) ? "NULL," : ($this->quote($value) . ',');
        }

        $columns = substr($columns, 0, strlen($columns) - 1);
        $values = substr($values, 0, strlen($values) - 1);

        $table = $this->quoteObj($table);
        $sql = "REPLACE INTO $table ($columns) VALUES ($values)";
		
        $ret = $this->exec($sql);

        if ($ret === false)
            return false;

        $id = $this->connection->lastInsertId();
        if ($id)
            return $id;
	
        return $ret;
    }

    public function quote($data, $paramType = PDO::PARAM_STR) {
        if (is_array ( $data ) || is_object ( $data )) {
            $return = array ();
            foreach ( $data as $k => $v ) {
                $return [$k] = $this->quote ( $v );
            }
            return $return;
        } else {
            $data = $this->connection->quote ( $data );
            if (false === $data)
                $data = "''";
            return $data;
        }
    }

    /**
     * quote object names.<br />
     * e.g. as mysql, a table name "user" will be quoted to "`user`", column name "t1.cl1 as haha" will be quoted to "`t1`.`cl1` AS `haha`"
     *
     * @param string|array $objName
     * @todo only mysql is currently supported.
     */
    public function quoteObj($objName) {
        if (is_array ( $objName ))
        {
            $return = array ();
            foreach ( $objName as $k => $v )
            {
                $return[] = $this->quoteObj($v);
            }
            return $return;
        }
        else
        {
            $v = trim($objName);
            $v = str_replace('`', '', $v);
            $v = preg_replace('# +AS +| +#i', ' ', $v);
            $v = explode(' ', $v);
            foreach($v as $k_1=>$v_1)
            {
                $v_1 = trim($v_1);
                if($v_1 == '')
                {
                    unset($v[$k_1]);
                    continue;
                }
                if(strpos($v_1, '.'))
                {
                    $v_1 = explode('.', $v_1);
                    foreach($v_1 as $k_2=>$v_2)
                    {
                        $v_1[$k_2] = '`'.trim($v_2).'`';
                    }
                    $v[$k_1] = implode('.', $v_1);
                }
                else
                {
                   $v[$k_1] = '`'.$v_1.'`';
                }
            }
            $v = implode(' AS ', $v);
            return $v;
        }
    }

    public function throwException($message = null, $code = null, $previous = null) {
        $errorInfo = $this->connection->errorInfo ();
        $ex = new DbException ( $message . ' (DriverCode:'.$errorInfo[1].')'. $errorInfo [2], $code, $previous );
        $this->logException($ex);
        throw $ex;
    }

    /**
     * Indicates the next query do not use page caches.
     *
     * @return self
     */
    public function noCache()
    {
        $this->withCache = false;
        return $this;
    }

    /**
     * By default, results (from select statement) are to be get from page caches. Please use the following syntax to get results from database in every query.
     * E.G.<pre>
     * DbConnection::instance()->read()->noCache()->query('....');
     * </pre>
     * @return PDOStatement
     * @see PDO::query()
     */
    public function query($sql=null)
    {
        static $retryCount = 0;

        $withCache = false;//$this->withCache;
        //reset withCache to true in every query, so the next query will use cache again.
        $this->withCache = true;

        if (empty($sql))
        {
            $this->lastSql = $this->getSelectSql();  // 涓嶉渶瑕乼rim锛屾嫾鎺ュ嚱鏁颁繚璇佷互SELECT寮�ご
        } else {
            $this->lastSql = trim($this->buildSql($sql));
        }
        $sqlCmd = strtoupper(substr($this->lastSql, 0, 6));
        if(in_array($sqlCmd, array('UPDATE', 'DELETE')) && stripos($this->lastSql, 'where') === false)
        {
            throw new DbException('no WHERE condition in SQL(UPDATE, DELETE) to be executed! please make sure it\'s safe', 42005);
        }
   
        if($this->allowRealExec || $sqlCmd == 'SELECT')
        {
            $cacheKey = md5($this->lastSql);
            if($withCache && isset($this->cachedPageQueries[$cacheKey]))
            {
                return $this->cachedPageQueries[$cacheKey];
            }
//             $trace = $this::getExternalCaller();
//             \Core\Lib\MNLogger\TraceLogger::instance('trace')->MYSQL_CS($this->connectionCfg['dsn'], $trace['class'].'::'.$trace['method'], $this->lastSql, $trace['file'].':'.$trace['line']);
//             $this->memoryUsageBeforeFetch = memory_get_usage();
             $this->queryBeginTime = microtime( true );
           
            $this->lastStmt = $this->connection->query($this->lastSql);
        }
        else
        {
            $this->lastStmt = true;
        }
   
        $this->queryEndTime = microtime(true);
        $this->logQuery($this->lastSql);
        if(false === $this->lastStmt)
        {
            // connection broken, retry one time
            $errorInfo = $this->connection->errorInfo();
            if($retryCount < 1 && $this->needConfirmConnection() && 2006 == $errorInfo[1])
            {
                $retryCount += 1;
                $this->reConnect();
                $result = $this->query($sql);
                $retryCount = 0;
                return $result;
            }
            $retryCount = 0;
            $this->throwException('Query failure.SQL:' . $this->lastSql . '. ('.$errorInfo[2].')', 42004 );
        }
        if(isset($cacheKey))
        {
            $this->cachedPageQueries[$cacheKey] = $this->lastStmt;
        }
		$this->lastSql = null;
		$this->queryEndTime = null;
        return $this->lastStmt;
    }

    /**
     *
     * @see PDO::exec()
     */
    public function exec($sql=null)
    {
        static $retryCount = 0;
        $this->queryBeginTime = microtime ( true );
        $re = $this->connection->exec($sql);
        $this->queryEndTime = microtime ( true );
        $this->logQuery($sql);
        if (false === $re) {
            // connection broken, retry one time
            $errorInfo = $this->connection->errorInfo();
            if($retryCount < 1 && $this->needConfirmConnection() && 2006 == $errorInfo[1])
            {
                $retryCount += 1;
                $this->reConnect();
                $result = $this->exec($sql);
                $retryCount = 0;
                return $result;
            }
            $retryCount = 0;
            $this->throwException('Query failure.SQL:' . $sql . '. ('.$errorInfo[2].')', 42004);
        }
		$this->lastSql = $sql;
        return $re;
    }

    /**
     * @param boolean $global If use transaction for all queries.
     *                        If it is set to true, you have to set it to true when "commit" or "rollback" to make all queries effective within it.
     *                        Once in global transaction, any nested in transactions are disabled, and will be included within the global transaction.
     *                        Notice: glboal transaction can not be netsted within any other transactions, it should be stated from the outmost level.
     */
    public function beginTransaction($global=false)
    {
        if($global && $this->connection->inTransaction())
        {// Allow one global transaction only.
            $this->connection->rollBack();
            throw new DbException('You cannot begin global transaction at this moment. There are active transactions or GlobalTransaction has already started !', 42101);
        }
        else if(!$global && $this->inGlobalTransaction)
        {// If global transaction started, then ignore all normal transactions(just not start them).
            return true;
        }
        else if($global)
        {// Start global transaction.
            $this->inGlobalTransaction = true;
        }

        if(!$this->connection->inTransaction())
        {// Re-connect before begin a transaction. If inTranaction then skip this step to avoid breaking nested transactions.
            $this->reConnect();
        }
        $this->connection->beginTransaction();
    }

    /**
     *
     * @param boolean $global if commit the global transaction.
     *
     * @return boolean
     */
    public function commit($global=false)
    {
        if($this->inGlobalTransaction && !$global)
        {// Prevent commiting a global transaction unexpectedly in a normal transaction.
            return true;
        }
        else
        {// Ready to commit the global transaction.
            $this->inGlobalTransaction = false;
        }
        return $this->connection->commit();
    }

    /**
     *
     * @param boolean $global if rollback the global transaction.
     *
     * @return boolean
     */
    public function rollback($global = false)
    {
        if($this->inGlobalTransaction && !$global)
        {// Prevent rollback a global transaction unexpectedly in a normal transaction.
            return true;
        }
        else
        {// Ready to rollback the global transaction.
            $this->inGlobalTransaction = false;
        }
        return $this->connection->rollBack();
    }

    /**
     * Check if confirmation of connection is needed by setting "confirm_link" of configuration  to true.
     * This is mostly used in Daemons which use long connections.
     *
     * @return boolean
     */
    public function needConfirmConnection()
    {
        if(isset($this->connectionCfg['confirm_link']) || $this->connectionCfg['confirm_link'] !== false)
        {
            return true;
        }
        return false;
    }

    public function buildWhere($condition = array(), $logic = 'AND')
    {
        $s = $this->buildCondition($condition, $logic);
        if( $s ) $s = ' WHERE ' . $s;
        return $s;
    }

    public function buildCondition($condition = array(), $logic = 'AND')
    {
        if( ! is_array($condition))
        {
            if (is_string($condition))
            {
                //forbid to use a CONSTANT as condition
                $count = preg_match('#\>|\<|\=| #', $condition, $logic);
                if(!$count)
                {
                    throw new DbException('bad sql condition: must be a valid sql condition');
                }
                $condition = explode($logic[0], $condition);
                $condition[0] = $this->quoteObj($condition[0]);
                $condition = implode($logic[0], $condition);
                return $condition;
            }

            throw new DbException('bad sql condition: ' . gettype($condition));
        }
        $logic = strtoupper($logic);
        $content = null;
        foreach ($condition as $k => $v)
        {
            $v_str = null;
            $v_connect = '';

            if (is_int($k))
            {
                //default logic is always 'AND'
                if ($content)
                    $content .= $logic . ' (' . $this->buildCondition($v) . ') ';
                else
                    $content = '(' . $this->buildCondition($v) . ') ';
                continue;
            }

            $k = trim($k);

            $maybe_logic = strtoupper($k);
            if (in_array($maybe_logic, array('AND', 'OR')))
            {
                if ($content)
                    $content .= $logic . ' (' . $this->buildCondition($v, $maybe_logic) . ') ';
                else
                    $content = '(' . $this->buildCondition($v, $maybe_logic) . ') ';
                continue;
            }

            $k_upper = strtoupper($k);
            //the order is important, longer fist, to make the first break correct.
            $maybe_connectors = array('>=', '<=', '<>', '!=', '>', '<', '=',
                    ' NOT BETWEEN', ' BETWEEN', 'NOT LIKE', ' LIKE', ' IS NOT', ' NOT IN', ' IS', ' IN');
            foreach ($maybe_connectors as $maybe_connector)
            {
                $l = strlen($maybe_connector);

                if (substr($k_upper, -$l) == $maybe_connector)
                {
                    $k = trim(substr($k, 0, -$l));
                    $v_connect = $maybe_connector;
                    break;
                }
            }

            if (is_null($v))
            {
                $v_str = ' NULL';
                if( $v_connect == '') {
                    $v_connect = 'IS';
                }
            }
            else if (is_array($v))
            {
                if($v_connect == ' BETWEEN') {
                    $v_str = $this->quote($v[0]) . ' AND ' . $this->quote($v[1]);
                }
                else if ( is_array($v) && ! empty($v) ) {
                    // 'key' => array(v1, v2)
                    $v_str = null;

                    foreach ($v AS $one)
                    {
                        if(is_array($one)) {
                            // (a,b) in ( (c, d), (e, f) )
                            $sub_items = '';
                            foreach($one as $sub_value) {
                                $sub_items .= ',' . $this->quote($sub_value);
                            }
                            $v_str .= ',(' . substr($sub_items, 1) . ')' ;
                        } else {
                            $v_str .= ',' . $this->quote($one);
                        }
                    }
                    $v_str = '(' . substr($v_str, 1) . ')';
                    if (empty($v_connect)) {
                        if($this->allowGuessConditionOperator === null || $this->allowGuessConditionOperator === true)
                        {
                            if($this->allowGuessConditionOperator === null)
                                Log::instance()->log("guessing condition operator is not allowed: use '$k IN'=>array(...)", array('type'=>E_WARNING));

                            $v_connect = 'IN';
                        }
                        else
                            throw new DbException("guessing condition operator is not allowed: use '$k IN'=>array(...)");
                    }
                }
                else if (empty($v)) {
                    // 'key' => array()
                    $v_str = $k;
                    $v_connect = '<>';
                }
            }
            else {
                $v_str = $this->quote($v);
            }

            if(empty($v_connect))
                $v_connect = '=';

            $quoted_k = $this->quoteObj($k);
            if ($content)
                $content .= " $logic ( $quoted_k $v_connect $v_str ) ";
            else
                $content = " ($quoted_k $v_connect $v_str) ";
        }

        return $content;
    }

    protected function buildSql($sql)
    {
        $realSql = '';
        if (is_string($sql))
            return $sql;
        if (is_array($sql)) {
            $realSql = '';
            foreach ($sql as $k => $v)
            {
                if (is_int($k))
                    $realSql .= $v . " ";
                else if ($k == 'where' || $k == 'WHERE')
                    $realSql .= " WHERE " . $this->buildCondition($v) . " ";
                else
                    Log::instance()->log('unknown key("'.$k.'") in sql.');
            }
        }

        return $realSql;
    }

    public function setAllowRealExec($v)
    {
        $this->allowRealExec = $v;
    }

    /**
     * 鍙湁鍦ㄤ富閿笉鏄嚜澧瀒d鐨勬椂鍊欙紝璋冪敤saveWithoutNull鐨勬椂鍊欐墠闇�allowSaveToNonExistingPk
     */
    public function setAllowSaveToNonExistingPk($v)
    {
        $this->allowSaveToNonExistingPk = $v;
    }

 
    public function setAllowGuessConditionOperator($v)
    {
        $this->allowGuessConditionOperator = $v;
    }

    public function getLastSql()
    {
        return $this->lastSql;
    }

    public function getSelectSql()
    {
        return "SELECT {$this->select_sql_top} {$this->select_sql_columns} {$this->select_sql_from_where} {$this->select_sql_group_having} {$this->select_sql_order_limit}";
    }

    /**
     * @param string $columns
     * @return \Core\Lib\DbConnection
     */
    public function select($columns = '*')
    {
        $this->select_sql_top = '';
        $this->select_sql_columns = $columns;
        $this->select_sql_from_where = '';
        $this->select_sql_group_having = '';
        $this->select_sql_order_limit = '';
        return $this;
    }

    /**
     * @param $n
     * @return \Core\Lib\DbConnection
     */
    public function top($n)
    {
        $n = intval($n);
        $this->select_sql_top = "TOP $n";
    }


    /**
     * @param $table
     * @return \Core\Lib\DbConnection
     */
    public function from($table)
    {
        $table = $this->quoteObj($table);
        $this->select_sql_from_where .= " FROM $table ";
        return $this;
    }

    /**
     * @param array $cond
     * @return \Core\Lib\DbConnection
     */
    public function where($cond)
    {
		if(!empty($cond)){
			$cond = $this->buildCondition($cond);
		}else{
			$cond=' 1 ';
		}
        $this->select_sql_from_where .= " WHERE $cond ";
        return $this;
    }

    protected function joinInternal($join, $table, $cond)
    {
        $table = $this->quoteObj($table);
        $this->select_sql_from_where .= " $join $table ";
        if (is_string($cond)
        && (strpos($cond, '=') === false && strpos($cond, '<') === false && strpos($cond, '>') === false))
        {
            $column = $this->quoteObj($cond);
            $this->select_sql_from_where .= " USING ($column) ";
        }
        else
        {
            $cond = $this->buildCondition($cond);
            $this->select_sql_from_where .= " ON $cond ";
        }
        return $this;
    }

    /**
     * @param $table
     * @param $cond
     * @return \Core\Lib\DbConnection
     */
    public function join($table, $cond)
    {
        return $this->joinInternal('JOIN', $table, $cond);
    }

    /**
     * @param $table
     * @param $cond
     * @return \Core\Lib\DbConnection
     */
    public function leftJoin($table, $cond)
    {
        return $this->joinInternal('LEFT JOIN', $table, $cond);
    }

    /**
     * @param $table
     * @param $cond
     * @return \Core\Lib\DbConnection
     */
    public function rightJoin($table, $cond)
    {
        return $this->joinInternal('RIGHT JOIN', $table, $cond);
    }

    public function update($table, $params, $cond, $options = 0, $order_by_limit = '')
    {
        if (empty($params))
            return false;

        if(is_string($params))
        {
            $update_str = $params;
        }
        else
        {
            $update_str = '';

            foreach ($params as $column => $value)
            {
                if (is_int($column)) {
                    $update_str .= "$value,";
                }
                else
                {
                    $column = $this->quoteObj($column);
                    $value = is_null($value) ? 'NULL' : $this->quote($value);
                    $update_str .= "$column=$value,";
                }
            }
            $update_str = substr($update_str, 0, strlen($update_str) - 1);
        }

        $table = $this->quoteObj($table);
        if(is_numeric($cond))
            $cond = $this->quoteObj('id') . "='$cond'";
        else
            $cond = $this->buildCondition($cond);
        $sql = "UPDATE ";
        if ($options == self::UPDATE_IGNORE)
            $sql .= " IGNORE ";
        $sql .= " $table SET $update_str WHERE $cond $order_by_limit";
		
		
        $ret = $this->exec($sql);
        return $ret;
    }

    public function delete($table, $cond)
    {
        $table = $this->quoteObj($table);
        $cond = $this->buildCondition($cond);
        $sql = "DELETE FROM {$table} WHERE $cond";
        $ret = $this->exec($sql);
        return $ret;
    }

    /**
     * @param $group
     * @return \Core\Lib\DbConnection
     */
    public function group($group)
    {
        $this->select_sql_group_having .= " GROUP BY $group ";
        return $this;
    }

    /**
     * @param $having
     * @return \Core\Lib\DbConnection
     */
    public function having($cond)
    {
        $cond = $this->buildCondition($cond);
        $this->select_sql_group_having .= " HAVING $cond ";
        return $this;
    }

    /**
     * @param $order
     * @return \Core\Lib\DbConnection
     */
    public function order($order)
    {
        $this->select_sql_order_limit .= " ORDER BY $order ";
        return $this;
    }

    public function isDriver($name)
    {
        $driver = $this->getAttribute(PDO::ATTR_DRIVER_NAME);
        if(is_array($name))
            return in_array($driver, $name);
        return $driver == $name;
    }

    public function queryScalar($sql = null, $default = null)
    {
        $stmt = $this->query($sql);
        $v = $stmt->fetchColumn(0);
      //  ////$this->memoryUsageAfterFetch = memory_get_usage();
      //  //\Core\Lib\MNLogger\TraceLogger::instance('trace')->MYSQL_CR($this->lastStmt ? 'SUCCESS':'EXCEPTION', $this->memoryUsageAfterFetch - $this->memoryUsageBeforeFetch);
        if($v !== false)
            return $v;
        return $default;
    }

    public function querySimple($sql = null, $default = null)
    {
        return $this->queryScalar($sql, $default);
    }

    /**
     * @param string|null $sql
     * @return array
     */
    public function queryRow($sql = null)
    {
        $stmt = $this->query($sql);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
		$stmt->closeCursor(); 
        ////$this->memoryUsageAfterFetch = memory_get_usage();
        //\Core\Lib\MNLogger\TraceLogger::instance('trace')->MYSQL_CR($this->lastStmt ? 'SUCCESS':'EXCEPTION', $this->memoryUsageAfterFetch - $this->memoryUsageBeforeFetch);
        return $data;
    }

    /**
     * @param string|null $sql
     * @return array
     */
    public function queryColumn($sql = null)
    {
        $stmt = $this->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
      //  ////$this->memoryUsageAfterFetch = memory_get_usage();
      //  //\Core\Lib\MNLogger\TraceLogger::instance('trace')->MYSQL_CR($this->lastStmt ? 'SUCCESS':'EXCEPTION', $this->memoryUsageAfterFetch - $this->memoryUsageBeforeFetch);
        return $data;
    }

    /**
     * @param string|null $sql
     * @param string $key
     * @return array
     */
    public function queryAllAssocKey($sql, $key)
    {
        $rows = array();
        $stmt = $this->query($sql);
        if ($stmt)
        {
            while (($row = $stmt->fetch(PDO::FETCH_ASSOC)) !== false)
                $rows[$row[$key]] = $row;
        }
     //   ////$this->memoryUsageAfterFetch = memory_get_usage();
     //   //\Core\Lib\MNLogger\TraceLogger::instance('trace')->MYSQL_CR($this->lastStmt ? 'SUCCESS':'EXCEPTION', $this->memoryUsageAfterFetch - $this->memoryUsageBeforeFetch);
        return $rows;
    }

	
    /**
     * @param string|null $sql
     * @param string $key
     * @return array
     */
    public function queryAll($sql = null, $key = '')
    {
        if($key)
            return $this->queryAllAssocKey($sql, $key);
        return $this->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find($table, $cond, $order = '')
    {
        if(is_numeric($cond))
            $cond = array('id'=>"$cond");
        $table = $this->quoteObj($table);
        $where = $this->buildWhere($cond);

        if($order && strncasecmp($order, 'ORDER BY', 8) != 0)
            $order = 'ORDER BY ' . $order;
        $sql = "SELECT * FROM $table $where $order";
        return $this->queryRow($sql);
    }

    public function findAll($table, $cond, $order = '')
    {
        $table = $this->quoteObj($table);
        $where = $this->buildWhere($cond);
        if($order && strncasecmp($order, 'ORDER BY', 8) != 0)
            $order = 'ORDER BY ' . $order;
        $sql = "SELECT * FROM $table $where $order";
        return $this->queryAll($sql);
    }

    public function count($table, $cond = array(), $columns = '*')
    {
        $table = $this->quoteObj($table);
        $where = array();
        if($cond)
            $where = $this->buildWhere($cond);
        $sql = "SELECT COUNT($columns) FROM $table $where";
        return $this->querySimple($sql);
    }

    //general implemention
    public function exists($table, $cond)
    {
        $table = $this->quoteObj($table);
        $where = $this->buildWhere($cond);
        $sql = "SELECT 1 FROM $table $where LIMIT 1";
        return ! ! $this->querySimple($sql);
    }

    /**
     * @param $a
     * @param null $b
     * @return \Core\Lib\DbConnection
     */
    public function limit($a, $b = null)
    {
        if (is_null($b)) {
            $a = intval($a);
            $this->select_sql_order_limit .= " LIMIT $a ";
        }
        else
        {
            $a = intval($a);
            $b = intval($b);
            $this->select_sql_order_limit .= " LIMIT $a, $b ";
        }
        return $this;
    }

    public function logQuery($sql) {
        if (property_exists(static::$dbConfigs, 'DEBUG') && property_exists(static::$dbConfigs, 'DEBUG_LEVEL'))
        {
            $logString = 'Begin:' . date ('Y-m-d H:i:s', $this->queryBeginTime ) . "\n";
            $logString .= 'SQL: ' . $sql . "\n";
            switch (static::$dbConfigs->DEBUG_LEVEL) {
                case 2 :
                    //looks ugly
                    $tempE = new \Exception ();
                    $logString .= "Trace:\n" . $tempE->getTraceAsString () . "\n";
                    continue;
                case 1 :
                default :
                    continue;
            }
            $logString .= 'End:' . date ( 'Y-m-d H:i:s', $this->queryEndTime ) . '  Total:' . sprintf ( '%.3f', ($this->queryEndTime - $this->queryBeginTime) * 1000 ) . 'ms' . "\n";
            Log::instance ( 'db' )->log ( $logString );
        }
		unset($logString);
    }

    public static function getExternalCaller()
    {
        $trace = debug_backtrace(false);
        $caller = array('class'=>'', 'method'=>'', 'file'=>'', 'line'=>'');
        $k = 0;
        foreach($trace as $k => $line)
        {
            if(isset($line['class']) &&  strpos($line['class'], __NAMESPACE__) === 0)
            {
                continue;
            }
            else if(isset($line['class']))
            {
                $caller['class'] = $line['class'];
                $caller['method'] = $line['function'];
            }
            else if(isset($line['function']))
            {
                $caller['method'] = $line['function'];
            }
            else
            {
                $caller['class'] = 'main';
            }
            break;
        }
        while(!isset($line['file']) && $k > 0)
        {// 鍙兘鍦╡val鎴栬�call_user_func閲岃皟鐢ㄧ殑銆�            $line = $trace[--$k];
        }
        $caller['file'] = $line['file'];
        $caller['line'] = $line['line'];
        return $caller;
    }

    /**
     * 璁板綍Exception鑷崇洃鎺ф棩蹇椼�闇�厛鍦ㄩ」鐩腑鍒濆鍖栥�
     *
     * @param \Exception $ex
     *
     * @uses \Core\Lib\MNLogger\EXLogger
     */
    protected function logException(\Exception $ex)
    {
        \Core\Lib\MNLogger\EXLogger::instance()->log($ex);
    }
    
    public function __destruct() {
		unset($this->lastStmt);
    	if($this->connection) $this->closeAll();
    	unset($this->connection);
    }
}

/**
 * Exceptions about database.DbException is bundled with {@link \Lib\Db} in the
 * same script file.<br />
 * Exception code as below:<br/>
 * <b>Error code start with "42" are database exceptions.</b>
 * <pre>
 * <code>
 * 42000 => connection failure
 * 42001 => no connections available
 * 42003 => specified connecton configuration not found
 * 42004 => Query failure.
 * 42005 => logical eror. bad sql etc.
 * 42101 => Global transaction conflicts.
 * </code>
 * </pre>
 */
class DbException extends \Exception
{
    
}
