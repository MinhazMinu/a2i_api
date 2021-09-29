<?php 

Class CallInfo extends DB{

	protected $tbl = "nic_dump";
	public function __construct(){
		parent::__construct();
	}

	public function get_nic_datewise($date, $where = [], $field = '*' , $extra = ''){
		$where_clause = '';
		$params = [];
		if(count($where) > 0){
			$info = $this->genWhere($where);
			$where_clause = ' AND ' . $info->where;
			$params = $info->params;
		}
	
		if(is_array($field)){
            $field = implode(',', $field);
        }else $field = '*';
		
		$sql = 'select '. $field .' from ' . $this->tbl . ' where date(`submit_time`) =:date ' . $where_clause ." " . $extra;
		$params[':date'] = $date;
		//echo $sql;
		//print_r($params);
		$query = $this->getData($sql,$params);
		return $query;
	}

	public function get_api(){
		$date = isset($_REQUEST['date']) ? $_REQUEST['date']: '';
		
		$query = $this->get_nic_datewise($date);
		$total_received_call =  $query->cnt;

		
		$data = new stdclass();
		$moreinfo = new stdclass();
		
		
		$received_call = (object) ['total_received_call' => $total_received_call];

		$physical_handicapped = (object)['total_received_call_physical_handicapped' => $this->get_physical_handicapped($date,"Yes")];

		$emergency_food_call = (object)['total_emergency_food_call' => $this->emergency_food($date) ];

		$emergency_food_call_to_goverment = (object) ['total_emergency_food_call_to_gov' => $this->emergency_food_call_to_gov($date) ];

		$remedy_social_problems = (object)['total_remedy_social_problems' => $this->get_remedy_social_problems($date)];

		$child_marriage = (object)['total_child_marriage' => $this->get_child_marriage($date)];


		$data->project_name = "333";
		
		$data->date = $date;
		
		$moreinfo->intrgrated_services = 20;

		$moreinfo->phone_call_confirmed_by_agent = $received_call->total_received_call;

		
		// $gender = ['male','female','others','na'];
		$gender = ['male','female'];
		foreach($gender as $v){
			$var1 = "total_received_call_".$v; 
			$received_call->$var1 = $this->get_gender($date,$v);

			$get_physical_handicapped = "physical_handicapped_" . $v;
			$physical_handicapped->$get_physical_handicapped = $this->get_physical_handicapped($date,"Yes",$v);

			$var2 = "emergency_food_". $v;
			$emergency_food_call->$var2 = $this->emergency_food($date,$v);

			$var3 = "emergency_food_physicaly_handicapped_". $v;			
			$emergency_food_call->$var3 = $this->emergency_food($date,$v,"Yes");

			$var4 = "emergency_food_call_to_gov_". $v;
			$emergency_food_call_to_goverment->$var4 = $this->emergency_food_call_to_gov($date,$v);

			$var5 = "emergency_food_call_to_gov_physicaly_handicapped_". $v;			
			$emergency_food_call_to_goverment->$var5 = $this->emergency_food_call_to_gov($date,$v,"Yes");
		}
		
		$division_area = $this->get_area_wise_data($date,1,[]);
		$infoall=[];
		foreach($division_area->data as $v){
			
			$infoall[] =$this->get_moreinfo($date,$v);

		}
		
		
		$moreinfo->moreinfo = $infoall;
		$data->data = $moreinfo;	
		//print_r($data);
		
		return $data;

	}

	public function get_moreinfo($date,$v){

		$info = [];

		$info['division_id'] = $v->division;

		$info['remedy_social_problems'] = $this->get_remedy_social_problems($date,[],$v->division);

		$info['prevent_child_marriage'] = $this->get_child_marriage($date,[],$v->division);

		$info['relief_call_to_administration']= $this->emergency_food_call_to_gov($date,'','',$v->division);

		$info['relief_calls'] = $this->emergency_food($date,'','',$v->division);
		return $info;

	} 

	public function get_gender($date,$gender){
		$where = ['gender' => $gender];
		$query = $this->get_nic_datewise($date,$where);
		return $query->cnt;
	}

	public function process_area_wise_data($request){
		$area = [];
		foreach($request->data as $v){
			$area[] = ['division' => $v->division, 'cnt' => $v->cnt];
		}
		return $area; 
	}

	

	public function get_physical_handicapped($date,$physical_handicapped, $gender = '',$division= ''){
		$where = ['Physical_Handicap' => $physical_handicapped];
		if($gender != '')  $where['gender'] = $gender;
		if($division != '')  $where['division'] = $division;
		$query = $this->get_nic_datewise($date,$where);
		return $query->cnt;
	}

	/*
	 * type: 1- div, 2- dist, 3- thana
	 */

	public function get_area_wise_data($date,$type, $where = [], $code = ''){
		switch ($type) {
			case '1':
				$group = "division";
				break;
			case '2':
				$group = "district";
				break;
		}
		$extra = " AND division <>'NA' AND division <>'' group by " . $group;
		
		$field = [$group,'count(*) as cnt'];
		$query = $this->get_nic_datewise($date,$where,$field,$extra);

		return $query;

	}

	public function get_remedy_social_problems($date,$where = [],$division = ''){
		$field = ['call_type'];
		$where = ['call_type'=> 'Complaints'];
		if($division != '')  $where['division'] = $division;
		$extra = " AND cmp_typ <> 'Food Assistance for COVID-19' ";

		// $extra = "AND call_type = 'Complaints'  AND cmp_typ = 'Food Assistance for COVID-19' ";
		$query = $this->get_nic_datewise($date,$where,$field,$extra);
		return $query->cnt;
	}


	public function get_child_marriage($date,$where=[],$division = ''){
		$where = ['asst_type' => 'Early Marriage',
					'call_type' => '!= Queries'
				];
		if($division != '')  $where['division'] = $division;
		
		$query = $this->get_nic_datewise($date,$where);
		return $query->cnt;
	}
	

	public function emergency_food($date, $gender = '',$Physical_Handicap='',$division=''){
		$where =[
				'cmp_typ' =>'Food Assistance for COVID-19'
				];
		$extra = " AND (call_type = 'Complaints' OR call_type = 'Queries') ";

		if($gender != '')  $where['gender'] = $gender;
		if($Physical_Handicap != '')  $where['Physical_Handicap'] = $Physical_Handicap;
		if($division != '')  $where['division'] = $division;
		$query = $this->get_nic_datewise($date,$where,"*",$extra);
		return $query->cnt;
	}

	public function emergency_food_call_to_gov($date, $gender = '',$Physical_Handicap='',$division=''){
		$where =[
			'call_type' => 'Complaints',
			'cmp_typ' =>'Food Assistance for COVID-19'
			];

	if($gender != '')  $where['gender'] = $gender;
	if($Physical_Handicap != '')  $where['Physical_Handicap'] = $Physical_Handicap;
	if($division != '')  $where['division'] = $division;
	$query = $this->get_nic_datewise($date,$where);
	return $query->cnt;
	}


	
   





}
?>