<?php

class Utils extends DB
{

    public function __construct()
    {
        parent::__construct();
    }

    public function get_role($code = '')
    {
        $sql = "select * from apex_role";
        if ($code != '')  $sql .= " where code= $code";
        $query = $this->getData($sql, []);
        return $query;
    }
}
