<?php
require_once __DIR__ . '/../app.php';
class DB extends PDO
{

    protected $dbuser, $dbpass, $dbhost, $dbname, $dbprefix, $db;
    protected $msg = "Error Updating information";

    public function __construct()
    {
        $this->getCon();
    }

    public function getCon()
    {
        $this->setConProp();
        try {
            $con = new PDO("mysql:host=" . $this->dbhost . ";dbname=" . $this->dbname, $this->dbuser, $this->dbpass);
            $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->db = $con;
        } catch (PDOException $e) {
            print_r($e->getMessage());
            return $this->getresp(1, $e->getMessage());
        }
    }

    public function setConProp()
    {
        $db_config = require_once __DIR__   . "/../config/config.php";
        foreach ($db_config as $k => $v)  $this->$k = $v;
    }

    public function get_query($sql, $params = [])
    {
        try {
            $query = $this->db->prepare($sql);
            $query->execute($params);
            return $query;
        } catch (PDOException $e) {
            return $this->getresp(1, $this->msg);
        }
    }

    public function getData($sql, $params = [])
    {
        $data = (object) ['data' => ''];
        $query = $this->get_query($sql, $params);
        #print_r($query);
        #echo $sql;
        $data->cnt = $query->rowCount();
        if ($data->cnt == 0)  $this->getresp(1, "No data found");
        if ($query) {
            $data->data  = $query->fetchAll(PDO::FETCH_OBJ);
        }
        return $data;
    }

    public function genWhere($request, $implode = ''){
        $where = [];
        if(count($request) == 0)  return $where;
        foreach($request as $key=>$val){
            $where_clause[] = "`$key`=:$key";
            $params[':'.$key] = $val;
        }
        $implode = ($implode == '') ? ' AND ':$implode;
        $where  = implode ($implode,$where_clause);
        $data = (object) ['where' => $where, 'params' => $params ];
        return $data;
    }

    public function getField($field,$where){
        $info = $this->genWhere($field,','); // for update field in db
        $winfo = $this->genWhere($field);
        $params = $info->params;
        foreach ($winfo->params as $key => $value) {
            $params[$key] = $value;
        }
        $data = (object) ['field'=> $info, 'where'=> $winfo, 'params' => $params];
        return $data;
    }

    public function get($tbl,$field = '*',$where, $extra = ''){
        $info = $this->genWhere($where);
        if(is_array($field)){
            $field = implode(',', '`' .$field . '`');
        }else $field = '*';
        
        $where_clause = '';
        $params = [];

        if(count((array) $info) > 0){
            $where_clause = " where " . $info->where;
            $params = $info->params;
        }

        $sql = "select " . $field . " from " . $tbl . $where_clause . " " . $extra;
        #echo $extra;
        $query = $this->getData($sql,$params);
        return $query;
    }




    public function getresp($code, $msg)
    {
        $obj = new stdclass();
        $obj->err = $code;
        $obj->msg = $msg;
        return $obj;
    }
}
