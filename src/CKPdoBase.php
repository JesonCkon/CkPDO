<?php
/**
 * Created by PhpStorm.
 * User: kontem
 * Date: 16/5/16
 * Time: 12:02
 */

namespace CkPdo;


class CKPdoBase
{
    private static $_instance = null;
    private $default_db_config = array();
    private $db_config_list = array();
    private $pdo_list = array();
    private $sql_type = '';
    private $all_sql_type = array('select', 'show', 'insert', 'update', 'delete');

    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new CKPdo();
        }

        return self::$_instance;
    }

    public function init($config)
    {
        self::getInstance();
        if(empty($config)){
            return false;
        }
        if (is_array($config)) {
            $this->default_db_config = current($config);
            $this->db_config_list = $config;
        } elseif (is_string($config) && is_file($config)) {
            $this->default_db_config = parse_ini_file($config);
            $this->db_config_list[] = $this->default_db_config;
        }
        $this->Connect();
        return $this;
    }

    private function Connect($index=null)
    {
        $db_config = empty($index) ? $this->default_db_config : $this->db_config_list[ $index ];
        if (empty($db_config)) {
            return false;
        }
        $dsn = 'mysql:dbname=' . $db_config[ "dbname" ] . ';host=' . $db_config[ "host" ] . '';
        $db_index = empty($index)?'default':$index;
        try {
            $this->pdo_list[ $db_index ] = new \PDO($dsn, $db_config[ "user" ], $db_config[ "password" ], array(
                \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
            ));
            //PDO::ATTR_ERRMODE：错误报告
            //PDO::ERRMODE_EXCEPTION 抛出 exceptions 异常
            //$this->pdo_list[ $db_index ]->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            //PDO::ATTR_EMULATE_PREPARES 本地预处理语句
            $this->pdo_list[ $db_index ]->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
            $this->pdo_list[ $db_index ]->bConnected = true;
        } catch (\PDOException $e) {
            echo $e->getMessage();
            die();
        }
    }

    public function __destruct()
    {
        foreach ($this->pdo_list as $key => $value) {
            $this->pdo_list[ $key ] = null;
            //echo "再见";
            //$this->pdo_list[ $key ]->bConnected = false;
        }
    }
    public function getDb($index=null){
        return isset($this->pdo_list[$index])?$this->pdo_list[$index]:false;
    }
    public function getDbObject($index=null){
        if (!isset($this->pdo_list[$index])) {
            $this->Connect($index);
        }
        $db_object = isset($this->pdo_list[$index])?$this->pdo_list[$index]:false;
        return new CkPdoQuery($db_object);
    }
}
