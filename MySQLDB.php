<?php

Class MySQLDB{
    private $conn;
    public $error = array();
    private $log = array();
    protected $table;

    static private $sql = "";

    function __construct() {
        $this->connectDb();
        $this->conn->set_charset("utf8");
    }

    private function connectDb() {
        $this->conn = new \mysqli("localhost", "root", "", "mvideo");
        if ($this->conn->connect_error) {
            $this->SetError(__METHOD__, $this->conn->connect_error);
        } else {
            $this->SetLog(__METHOD__, "Conected: " . "mvideo");
        }
    }

    function select(array $list = array(),$table = null){
        $table = $table?$table:$this->table;
        $str = "";
        if(count($list) > 0){
            foreach ($list as $k => $v){
                $list[$k] = $table.".".$v;
            }
            $str = implode(" , ",$list);
        }else{
            $str = "*";
        }
         self::$sql = "SELECT ".$str." FROM ".$table." ";
         return $this;
    }

    function delete($table = null){
        $table = $table?$table:$this->table;
        self::$sql = "DELETE FROM ".$table." ";
        return $this;
    }

    function update(array $set = array(),$table = null){
        $table = $table?$table:$this->table;
        $set_ = array();
        foreach ($set as $k => $v) {
            $set_[] = $k." = '".$v."'";
        }
        self::$sql = "UPDATE ".$table." SET ".implode(" , ",$set_)." ";
        return $this;
    }

    function where(array $arr = array()){
        $tmp = array();
        foreach ($arr as $k => $v){
            $tmp[] = $k." = '".$v."'";
        }
        self::$sql.=" WHERE ".implode(" AND ",$tmp);
        return $this;
    }

    function order($coll = null){
        if(is_null($coll)) return $this;
        self::$sql.=" ORDER BY ".$coll;
        return $this;
    }

    function limit($limit = null,$offset = 0){
        if(is_null($limit)) return $this;
        self::$sql.=" LIMIT ".$limit." OFFSET ".$offset;
        return $this;
    }

    function doQuery(){
        $conn = $this->conn;
        $out = array();
        $result = $conn->query(self::$sql);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $out[] = $row;
            }
        } else {
            $this->SetLog(__METHOD__, "0 results");
        }

        self::$sql = "";
        return $out;
    }

    function query($sql){
        self::$sql = $sql;
        return $this;
    }

    function insert($arr,$table = null){
        $table = $table?$table:$this->table;
        $conn = $this->conn;
        $cols = array();
        $values = array();
        foreach ($arr as $k => $v){
            $cols[] = $k;
            $values[] = "'".$v."'";
        }

        $sql = "INSERT INTO ".$table." (".implode(" , ",$cols)." ) VALUES ( ".implode(",",$values)." )";
        if ($conn->query($sql) === TRUE) {
            $this->SetLog(__METHOD__, "New record created successfully");
            return $conn->insert_id;
        } else {
            $this->SetError(__METHOD__, $conn->error);
            return false;
        }
    }

    private function setLog($met, $log) {
        $date = date("d-m-Y H:i:s");
        $this->log[] = $date." - ".$met . ":" . $log;
    }

    private function setError($met, $err) {
        $date = date("d-m-Y H:i:s");
        $this->error[] = $date." - ".$met . ":" . $err;
    }


}