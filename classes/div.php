<?php 

Class CallInfo extends DB{

    

	protected $tbl = "nic_dump";
	public function __construct(){
		parent::__construct();
	}


    public function get_division(){
        $date = isset($_REQUEST['date']) ? $_REQUEST['date']: '';
        $Division = ['Dhaka,Khulna'];
        foreach ($Division as $v) {
           $count_child_marriage = $this->prevent_child_marriage($v);
        }
    }

    public function prevent_child_marriage($Division){
        $sql = "SELECT * from ".$tbl."WHERE division = ".$Division.";";
        echo get_query($sql);
    }

}

?>