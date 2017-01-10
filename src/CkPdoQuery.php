<?php
/**
 * Created by PhpStorm.
 * User: kontem
 * Date: 16/5/17
 * Time: 11:30
 */

namespace CkPdo;


class CkPdoQuery
{
    public static $sql_str = '';

    public function __construct($db_obj = null)
    {
        if (empty($db_obj)) {
            return false;
        } else {
            $this->db = $db_obj;
        }
    }

    public function selectTbl($table_name, $where_keys = null)
    {
        if ($this->db != false) {
            if (is_array($where_keys)) {
                foreach ($where_keys as $k => $v) {
                    $where_str = empty($where_str) ? "`{$k}`=:{$k}" : " AND `{$k}`=:{$k}";
                }
            }elseif (is_string($where_keys)) {
                $where_str = $where_keys;
            }

            $sql = "SELECT * FROM {$table_name} WHERE {$where_str}";

            return $this->sqlQuery($sql, $where_keys);
        }
    }
    public function selectTblAll($table_name, $where_keys = null)
    {
        if ($this->db != false) {
            if (is_array($where_keys)) {
                foreach ($where_keys as $k => $v) {
                    $where_str = empty($where_str) ? "`{$k}`=:{$k}" : " AND `{$k}`=:{$k}";
                }
            }elseif (is_string($where_keys)) {
                $where_str = $where_keys;
            }

            $sql = "SELECT * FROM {$table_name} WHERE {$where_str}";

            return $this->sqlQuery($sql, $where_keys,true);
        }
    }
    public function getColumn($tablename=''){
        $stmt = $this->db->prepare("DESCRIBE $tablename");
        $stmt->execute();
        $table_fields = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        $fields_arr = array();
        foreach($table_fields as $vals){
            $fields_arr[]=$vals;
        }
        return $fields_arr;
    }
    public function sqlQuery($sql, $insert_data = null,$is_all=false,$is_query=false)
    {
        self::$sql_str = $sql;
        if ($is_query == true) {
           return $this->db->query($sql);
        }
        if ($this->db != false) {
            $stmt = $this->db->prepare($sql);
            if (!empty($insert_data) && is_array($insert_data)) {
                foreach ($insert_data as $key => $value) {
                    if (isset($value[ 2 ])) {
                        //$sth->bindParam(':colour', $colour, PDO::PARAM_STR, 12);
                        $stmt->bindParam(':' . $key, $value[ 0 ], $value[ 1 ], $value[ 2 ]);
                    } elseif (isset($value[ 1 ])) {
                        $stmt->bindParam(':' . $key, $value[ 0 ], $value[ 1 ]);
                    } else {
                        $stmt->bindParam(':' . $key, $value[ 0 ]);
                    }
                }
            }

            $stmt->execute();
            $sql_identifier_info = explode(' ', $sql);
            if (strtolower($sql_identifier_info[ 0 ]) == 'insert') {
                if ($this->db->lastInsertId() > 0) {
                    return $this->db->lastInsertId();
                } else {
                    return $stmt->rowCount();
                }
            }
            if (strtolower($sql_identifier_info[ 0 ]) == 'update') {
                if ($stmt->rowCount()) {
                    return true;
                } else {
                    return false;
                }
            }
            if ($is_all==true) {
                $row = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            }else{
                $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            }
        }

        return $row;
    }

    public function query($sql)
    {
        return $this->sqlQuery($sql);
    }
    public function checkConnect($db_name){
        return $this->db->exec("use ".$db_name);
    }

    public function insertTbl($table_name, $insert_data, $insert_type = 'INTO')
    {
        if ($this->db != false) {
            $prep = array();
            foreach ($insert_data as $k => $v) {
                $prep[ ':' . $k ] = $v;
            }
            $sql = "INSERT {$insert_type} {$table_name} ( `" . implode('`, `', array_keys($insert_data)) . "`) ";
            $sql .= "VALUES (" . implode(', ', array_keys($prep)) . ")";

            return $this->sqlQuery($sql, $insert_data);
        }
    }

    public function insertUpdateSql($table = null,$data,$debug = false){
        if(empty($table) || empty($data)){
            return false;
        }
        $sql = null;
        #$sql = "INSERT IGNORE INTO {$table} SET ".getSqlString($data);
        $sql = "INSERT INTO {$table} SET ".$this->getSqlString($data)." ON DUPLICATE KEY UPDATE ".$this->getSqlString($data);
        return $sql;
    }
    public function ignoreSql($table = null,$data,$debug = false){
        if(empty($table) || empty($data)){
            return false;
        }
        $sql = null;
        $sql = "INSERT IGNORE INTO {$table} SET ".$this->getSqlString($data);
        #$sql = "INSERT INTO {$table} SET ".getSqlString($data)." ON DUPLICATE KEY UPDATE ".getSqlString($data);
        return $sql;
    }
    public function updateSql($table = null,$data,$where){
        if(empty($table) || empty($data)){
            return false;
        }
        $sql = null;
        $sql = "UPDATE {$table} SET ".$this->getSqlString($data)." {$where}";
        #$sql = "INSERT INTO {$table} SET ".getSqlString($data)." ON DUPLICATE KEY UPDATE ".getSqlString($data);
        return $sql;
    }
    public function getSqlString($data){
        if(is_array($data)){
            $sqlString = null;
            foreach ($data as $key => $value) {
                $sqlString .= empty($sqlString) ? "`{$key}` ='{$value}'" : ",`{$key}` ='{$value}'" ;
            }
            return $sqlString;
        }
        return false;
    }

    public function updateTbl($table_name, $insert_data, $where_keys)
    {
        if ($this->db != false) {
            $u_str = '';
            foreach ($insert_data as $k => $v) {
                $u_str = empty($u_str) ? "`{$k}`=:{$k}" : ",`{$k}`=:{$k}";
            }
            $where_str = '';
            foreach ($where_keys as $k => $v) {
                $where_str = empty($where_str) ? "`{$k}`=:{$k}" : " AND `{$k}`=:{$k}";
            }
            $sql = "UPDATE {$table_name} SET {$u_str} WHERE {$where_str}";
            $i_data = array_merge($insert_data, $where_keys);

            return $this->sqlQuery($sql, $i_data);
        }
    }
}
