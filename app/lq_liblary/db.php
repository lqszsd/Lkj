<?php
/**
 * MyPDO
 */
$db = Db::getInstance('localhost', 'root', 'root', 'class', 'utf8');
class Db
{
    protected static $_instance = null;
    protected $dsn;
    protected $dbh;
    protected $table_prefix="lq_";
    protected $table_name="test";
    protected $sql_info=[];
    /**
     * 构造
     *
     * @return MyPDO
     */
    protected function __construct($dbHost, $dbUser, $dbPasswd, $dbName, $dbCharset)
    {
        try {
            $this->dsn = 'mysql:host='.$dbHost.';dbname='.$dbName;
            $this->dbh = new PDO($this->dsn, $dbUser, $dbPasswd);
            $this->dbh->exec('SET character_set_connection='.$dbCharset.', character_set_results='.$dbCharset.', character_set_client=binary');
        } catch (PDOException $e) {
            $this->outputError($e->getMessage());
        }
        $this->initTable();
    }
    public function initTable(){
        //先查询table表
        $data=$this->query("show tables;",'All');
        $table_name = "{$this->table_prefix}{$this->table_name}";
        $table_array=[];
        //foreach 取出表名
        foreach ($data as $key=>$value){
            $table_array[]=$value['Tables_in_class'];
        }
        //var_dump($table_array);
        //判断当前表是否在数据库存在
        if(isset($table_array[$table_name])){
            //如果不存在就创建数据库
            $this->query("CREATE TABLE `{$table_name}` ( `id` INT NOT NULL AUTO_INCREMENT, `created_at` DATETIME NOT NULL , `updated_at` TIMESTAMP on update CURRENT_TIMESTAMP() NOT NULL DEFAULT CURRENT_TIMESTAMP() , PRIMARY KEY (`id`)) ENGINE = InnoDB;",'All');
        }
       //获取当前数据库字段
        $filed=$this->getFields($table_name);
        $table_info=$this->getTable();
        foreach ($table_info as $key=>$value) {
            //如果数据库不存在这个字段就增加这个字段
            if (!isset($filed[$key])) {
                $this->query("ALTER TABLE `{$table_name}` ADD `{$key}` {$value['type']}({$value['length']}) NOT NULL DEFAULT '{$value['default']}' COMMENT '{$value['comment']}' AFTER `{$filed['id']}`;");
            }
            //如果存在需要更新的标志就更新
            if ($value['updated'] == 1) {
                $this->query("ALTER TABLE `{$table_name}` MODIFY COLUMN `{$key}`  {$value['type']}({$value['length']}) NOT NULL DEFAULT '{$value['default']}' COMMENT '{$value['comment']}' AFTER `{$filed['id']}`;");
            }
        }
    }
   protected function getTable(){
        return $this->sql_info;
   }
    public static function generateType($type, $comment, $length, $default,$updated_type)
    {
        return [
            "type" => $type,
            "comment" => $comment,
            "length" => $length,
            "default" => $default,
            "updated"=>$updated_type
        ];
    }

    public static function generateINT($comment = "", $length = 11, $default = 0,$updated_type)
    {
        return self::generateType("INT", $comment, $length, $default,$updated_type);
    }

    public static function generateVARCHAR($comment = "", $length = 128, $default = '',$updated_type)
    {
        return self::generateType("VARCHAR", $comment, $length, $default,$updated_type);
    }

    public static function generateTEXT($comment = "", $default = '',$updated_type)
    {
        return self::generateType("TEXT", $comment, 0, $default,$updated_type);
    }

    public static function generateLONGTEXT($comment = "", $default = '',$updated_type)
    {
        return self::generateType("LONGTEXT", $comment, 0, $default,$updated_type);
    }

    public static function generateDATETIME($comment = "", $default = '0000-00-00 00:00:00',$updated_type)
    {
        return self::generateType("DATETIME", $comment, 0, $default,$updated_type);
    }

    public static function generateDECIMAL($comment = "", $default = '0',$updated_type)
    {
        return self::generateType("DECIMAL", $comment, '10,2', $default,$updated_type);
    }
    /**
     * 防止克隆
     *
     */
    private function __clone() {}

    /**
     * Singleton instance
     *
     * @return Object
     */
    public static function getInstance($dbHost, $dbUser, $dbPasswd, $dbName, $dbCharset)
    {
        if (self::$_instance === null) {
            self::$_instance = new self($dbHost, $dbUser, $dbPasswd, $dbName, $dbCharset);
        }
        return self::$_instance;
    }

    /**
     * Query 查询
     *
     * @param String $strSql SQL语句
     * @param String $queryMode 查询方式(All or Row)
     * @param Boolean $debug
     * @return Array
     */
    public function query($strSql, $queryMode = 'All', $debug = false)
    {
        if ($debug === true) $this->debug($strSql);
        $recordset = $this->dbh->query($strSql);
        $this->getPDOError();
        if ($recordset) {
            $recordset->setFetchMode(PDO::FETCH_ASSOC);
            if ($queryMode == 'All') {
                $result = $recordset->fetchAll();
            } elseif ($queryMode == 'Row') {
                $result = $recordset->fetch();
            }
        } else {
            $result = null;
        }
        return $result;
    }

    /**
     * Update 更新
     *
     * @param String $table 表名
     * @param Array $arrayDataValue 字段与值
     * @param String $where 条件
     * @param Boolean $debug
     * @return Int
     */
    public function update($table, $arrayDataValue, $where = '', $debug = false)
    {
        $this->checkFields($table, $arrayDataValue);
        if ($where) {
            $strSql = '';
            foreach ($arrayDataValue as $key => $value) {
                $strSql .= ", `$key`='$value'";
            }
            $strSql = substr($strSql, 1);
            $strSql = "UPDATE `$table` SET $strSql WHERE $where";
        } else {
            $strSql = "REPLACE INTO `$table` (`".implode('`,`', array_keys($arrayDataValue))."`) VALUES ('".implode("','", $arrayDataValue)."')";
        }
        if ($debug === true) $this->debug($strSql);
        $result = $this->dbh->exec($strSql);
        $this->getPDOError();
        return $result;
    }

    /**
     * Insert 插入
     *
     * @param String $table 表名
     * @param Array $arrayDataValue 字段与值
     * @param Boolean $debug
     * @return Int
     */
    public function insert($table, $arrayDataValue, $debug = false)
    {
        $this->checkFields($table, $arrayDataValue);
        $strSql = "INSERT INTO `$table` (`".implode('`,`', array_keys($arrayDataValue))."`) VALUES ('".implode("','", $arrayDataValue)."')";
        if ($debug === true) $this->debug($strSql);
        $result = $this->dbh->exec($strSql);
        $this->getPDOError();
        return $result;
    }

    /**
     * Replace 覆盖方式插入
     *
     * @param String $table 表名
     * @param Array $arrayDataValue 字段与值
     * @param Boolean $debug
     * @return Int
     */
    public function replace($table, $arrayDataValue, $debug = false)
    {
        $this->checkFields($table, $arrayDataValue);
        $strSql = "REPLACE INTO `$table`(`".implode('`,`', array_keys($arrayDataValue))."`) VALUES ('".implode("','", $arrayDataValue)."')";
        if ($debug === true) $this->debug($strSql);
        $result = $this->dbh->exec($strSql);
        $this->getPDOError();
        return $result;
    }

    /**
     * Delete 删除
     *
     * @param String $table 表名
     * @param String $where 条件
     * @param Boolean $debug
     * @return Int
     */
    public function delete($table, $where = '', $debug = false)
    {
        if ($where == '') {
            $this->outputError("'WHERE' is Null");
        } else {
            $strSql = "DELETE FROM `$table` WHERE $where";
            if ($debug === true) $this->debug($strSql);
            $result = $this->dbh->exec($strSql);
            $this->getPDOError();
            return $result;
        }
    }

    /**
     * execSql 执行SQL语句
     *
     * @param String $strSql
     * @param Boolean $debug
     * @return Int
     */
    public function execSql($strSql, $debug = false)
    {
        if ($debug === true) $this->debug($strSql);
        $result = $this->dbh->exec($strSql);
        $this->getPDOError();
        return $result;
    }

    /**
     * 获取字段最大值
     *
     * @param string $table 表名
     * @param string $field_name 字段名
     * @param string $where 条件
     */
    public function getMaxValue($table, $field_name, $where = '', $debug = false)
    {
        $strSql = "SELECT MAX(".$field_name.") AS MAX_VALUE FROM $table";
        if ($where != '') $strSql .= " WHERE $where";
        if ($debug === true) $this->debug($strSql);
        $arrTemp = $this->query($strSql, 'Row');
        $maxValue = $arrTemp["MAX_VALUE"];
        if ($maxValue == "" || $maxValue == null) {
            $maxValue = 0;
        }
        return $maxValue;
    }

    /**
     * 获取指定列的数量
     *
     * @param string $table
     * @param string $field_name
     * @param string $where
     * @param bool $debug
     * @return int
     */
    public function getCount($table, $field_name, $where = '', $debug = false)
    {
        $strSql = "SELECT COUNT($field_name) AS NUM FROM $table";
        if ($where != '') $strSql .= " WHERE $where";
        if ($debug === true) $this->debug($strSql);
        $arrTemp = $this->query($strSql, 'Row');
        return $arrTemp['NUM'];
    }

    /**
     * 获取表引擎
     *
     * @param String $dbName 库名
     * @param String $tableName 表名
     * @param Boolean $debug
     * @return String
     */
    public function getTableEngine($dbName, $tableName)
    {
        $strSql = "SHOW TABLE STATUS FROM $dbName WHERE Name='".$tableName."'";
        $arrayTableInfo = $this->query($strSql);
        $this->getPDOError();
        return $arrayTableInfo[0]['Engine'];
    }

    /**
     * beginTransaction 事务开始
     */
    private function beginTransaction()
    {
        $this->dbh->beginTransaction();
    }

    /**
     * commit 事务提交
     */
    private function commit()
    {
        $this->dbh->commit();
    }

    /**
     * rollback 事务回滚
     */
    private function rollback()
    {
        $this->dbh->rollback();
    }

    /**
     * transaction 通过事务处理多条SQL语句
     * 调用前需通过getTableEngine判断表引擎是否支持事务
     *
     * @param array $arraySql
     * @return Boolean
     */
    public function execTransaction($arraySql)
    {
        $retval = 1;
        $this->beginTransaction();
        foreach ($arraySql as $strSql) {
            if ($this->execSql($strSql) == 0) $retval = 0;
        }
        if ($retval == 0) {
            $this->rollback();
            return false;
        } else {
            $this->commit();
            return true;
        }
    }
    /**
     * checkFields 检查指定字段是否在指定数据表中存在
     *
     * @param String $table
     * @param array $arrayField
     */
    private function checkFields($table, $arrayFields)
    {
        $fields = $this->getFields($table);
        foreach ($arrayFields as $key => $value) {
            if (!in_array($key, $fields)) {
                $this->outputError("Unknown column `$key` in field list.");
            }
        }
    }

    /**
     * getFields 获取指定数据表中的全部字段名
     *
     * @param String $table 表名
     * @return array
     */
    private function getFields($table)
    {
        $fields = array();
        $recordset = $this->dbh->query("SHOW COLUMNS FROM $table");
        $this->getPDOError();
        $recordset->setFetchMode(PDO::FETCH_ASSOC);
        $result = $recordset->fetchAll();
        foreach ($result as $rows) {
            $fields[] = $rows['Field'];
        }
        return $fields;
    }

    /**
     * getPDOError 捕获PDO错误信息
     */
    private function getPDOError()
    {
        if ($this->dbh->errorCode() != '00000') {
            $arrayError = $this->dbh->errorInfo();
            $this->outputError($arrayError[2]);
        }
    }

    /**
     * debug
     *
     * @param mixed $debuginfo
     */
    private function debug($debuginfo)
    {
        var_dump($debuginfo);
        exit();
    }

    /**
     * 输出错误信息
     *
     * @param String $strErrMsg
     */
    private function outputError($strErrMsg)
    {
        throw new Exception('MySQL Error: '.$strErrMsg);
    }

    /**
     * destruct 关闭数据库连接
     */
    public function destruct()
    {
        $this->dbh = null;
    }
}
?>