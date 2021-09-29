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
		//total call
		$query = $this->get_nic_datewise($date);
		$total_received_call =  $query->cnt;

		//$data = (object) ['total_received_call' =>  $total_received_call];
		//$data->total_received_call_physical_handicapped = $this->get_physical_handicapped($date,"Yes");
		//$data->total_emergency_food_call = $this->emergency_food($date);
		//$data->total_emergency_food_to_gov = $this->emergency_food_call_to_gov($date);
		//$data->remedy_social_problems = $this->get_remedy_social_problems($date);
		//$data->child_marriage = $this->get_child_marriage($date);
		$data = new stdclass();
		//$data->received_call
		$received_call = (object) ['total_received_call' => $total_received_call];

		$physical_handicapped = (object)['total_received_call_physical_handicapped' => $this->get_physical_handicapped($date,"Yes")];

		$emergency_food_call = (object)['total_emergency_food_call' => $this->emergency_food($date) ];

		$emergency_food_call_to_goverment = (object) ['total_emergency_food_call_to_gov' => $this->emergency_food_call_to_gov($date) ];

		$remedy_social_problems = (object)['total_remedy_social_problems' => $this->get_remedy_social_problems($date)];

		$child_marriage = (object)['total_child_marriage' => $this->get_child_marriage($date)];

		


		//gender count
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
		
		$area = $this->get_area_wise_data($date,1,[]);
		//$parea = $this->process_area_wise_data($area);
		//print_r($parea);
		//$received_call->area = $area->data;
		
		foreach($area->data as $v){
			$Division = [];
			// $key ="total_received_call_". $v->division;
			// $received_call->$key = $v->cnt;
			$received_call->Division[$v->division] = $v->cnt;
			//$received_call->area = [''];

			// $var2 = 'physical_handicapped_'.$v->division;
			// $physical_handicapped->$var2 = $this->get_physical_handicapped($date,"Yes",'',$v->division);
			$physical_handicapped->Division[$v->division] = $this->get_physical_handicapped($date,"Yes",'',$v->division);

			// $var3 = 'emergency_food_call_'.$v->division;
			// $emergency_food_call->$var3 = $this->emergency_food($date,'','',$v->division);
			$emergency_food_call->Division[$v->division] = $this->emergency_food($date,'','',$v->division);

			// $var4 = 'emergency_food_call_to_goverment_'.$v->division;
			// $emergency_food_call_to_goverment->$var4 = $this->emergency_food_call_to_gov($date,'','',$v->division);
			$emergency_food_call_to_goverment->Division[$v->division]= $this->emergency_food_call_to_gov($date,'','',$v->division);

			// $var5 = 'remedy_social_problems_'.$v->division;
			// $remedy_social_problems->$var5 = $this->get_remedy_social_problems($date,[],$v->division);
			// $var5 = $this->get_remedy_social_problems($date,[],$v->division);
			
			$remedy_social_problems->Division[$v->division] = $this->get_remedy_social_problems($date,[],$v->division);

			$child_marriage->Division[$v->division] = $this->get_child_marriage($date,[],$v->division);
		}
		
		
		$data->project_name = "333";
		
		$data->date = $date;
		
		$data->intrgrated_services = 20;

		$data->receieved_call = $received_call;
		
		$data->physical_handicapped = $physical_handicapped;

		$data->emergency_food_call = $emergency_food_call;

		$data->emergency_food_call_to_goverment = $emergency_food_call_to_goverment;

		$data->remedy_social_problems = $remedy_social_problems;

		$data->child_marriage = $child_marriage;
		
		//$male = $this->get_gender($date,"Male");
		//$female = $this->get_gender($date,"Female");

		
		//$physical_handicapped_male = $this->get_physical_handicapped($date,"Yes","Male");
		//$physical_handicapped_female = $this->get_physical_handicapped($date,"Yes","Female");

		/*
		$data = [
			'total_received_call' => $total_received_call,
			'male' => $male,
			'female' => $female,
			'physical_handicapped_yes' => $physical_handicapped_yes,
			'physical_handicapped_male' => $physical_handicapped_male,
			'physical_handicapped_female' => $physical_handicapped_female, 
		];
		*/
		

		//$data->areaAll = $this->get_area_wise_data($date,1);	

		

		

		
		
		
		print_r($data);
		// print_r(json_encode((array)$data));
		return $data;

	}

	public function get_gender($date,$gender){
		$where = ['gender' => $gender];
		$query = $this->get_nic_datewise($date,$where);
		//$data = (object) ['gender' => strtolower($gender) , 'cnt' => $query->cnt];
		//return $data;
		return $query->cnt;
	}

	public function process_area_wise_data($request){
		$area = [];
		foreach($request->data as $v){
			//$v->division. ' ' . $v->cnt . "\n";
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
		#echo $extra;
		$field = [$group,'count(*) as cnt'];
		$query = $this->get_nic_datewise($date,$where,$field,$extra);
		//var_dump($query);
		//print_r($query->data[1]->cnt);
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