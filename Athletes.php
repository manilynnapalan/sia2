<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Athletes extends MX_Controller {
	public function __construct() {
        parent::__construct();
        $this->load->model(array('Model'));
    } 
    
	public function index()
	{		
		$row = $this->checkAccountNotNull();
		$this->checkAccountUpdated($row);
		$where = [
			'i.sports'=>$row->sports,
			 'a.usertype' => 2
		];
		$getCoachData = $this->Model->CheckAccount('accounts',$where);
		$where = ['e.coach_id'=>$getCoachData->account_id,'date >= ' => date('Y-m-d')];
		$data['allEvents'] = $this->Model->getAllEvents($where);
		$this->heading($row);
		$this->load->view('upcoming_events',$data);
		$this->load->view('footer');
	} 

	public function view_Attendance(){
		$row = $this->checkAccountNotNull();
		$where = [
			'i.sports'=>$row->sports,
			 'a.usertype' => 2
		];
		$getCoachData = $this->Model->CheckAccount('accounts',$where);
		$where = ['e.coach_id'=>$getCoachData->account_id];
		$allEvents = $this->Model->getAllEvents($where);
		// var_dump($where);exit;
		$array_result = array();
		foreach($allEvents as $value){
			$where1 = ['att_event_id'=>$value->event_id,'att_account_id' => $row->account_id];
			$checkResult = $this->Model->getAttendancesByEventId($where1);
			if($checkResult != null){
				$status = 'Present';
			} else {
				$status = 'Absent';
			}
			array_push($array_result,[
				'eventRow' => $value,
				'status' => $status
			]);
		}
		$data['result'] = $array_result;
		$this->heading($row);
		$this->load->view('view_attendance',$data);
		$this->load->view('footer');
	}

	public function post(){
		$row = $this->checkAccountNotNull();
		$where = "p.sport_team = '$row->sports' OR p.sport_team = 'admin'";;
		$data['allDocumentation'] = $this->Model->getDocumentation($where);
		$this->heading($row);
		$this->load->view('post_documentation',$data);
		$this->load->view('footer');
	}

	public function changeAccount(){
		$row = $this->checkAccountNotNull();
		$data['row'] = $row;
		$this->heading($row);
		$this->load->view('changeAccount',$data);
		$this->load->view('footer');
	}

	public function update_user_account(){
		$row = $this->checkAccountNotNull();
		$id = $this->nativesession->get('id');
		$username = $this->input->post('username');
		$password = base64_encode(md5($this->input->post('password')));
		$data = ['username'=>$username,'password'=>$password,'updated'=>1];
		$where = ['id'=>$id];
		$this->Model->update('accounts',$data,$where);
		$message = base64_encode("success~Your username and password successfully updated.");
		redirect(base_url('athletes/?m='.$message));
	}

	public function profile(){
		$row = $this->checkAccountNotNull();
		$this->checkAccountUpdated($row);
		$data['hresult'] = $row;
		$this->heading($row);
		$this->load->view('profile',$data);
		$this->load->view('footer');
	}

	public function update_profile(){
		$row = $this->checkAccountNotNull();
		$lname = $this->input->post('lname');
		$fname = $this->input->post('fname');
		$mi = $this->input->post('mi');
		$extension = $this->input->post('extension');
		$course = $this->input->post('course');
		$blood_type = $this->input->post('blood_type');
		$weight = $this->input->post('weight');
		$height = $this->input->post('height');
		$allergies = $this->input->post('allergies');
		$medications = $this->input->post('medications');
		$contact_number = $this->input->post('contact_number');


		$username = $this->input->post('username');
		$password = $this->input->post('password');

		$config['upload_path'] = FCPATH."assets\images";
	    $config['allowed_types'] = 'gif|jpg|png|jpeg';
	    $config['max_size'] = 100000;
	    $config['max_width'] = 5000;
	    $config['max_height'] = 5000;

	    $this->load->library('upload', $config);
	    $image_name = $_FILES['pro_pic']['name'];
	    $image_path = './assets/pro_pic_images/'.$image_name;
	  	if($this->upload->do_upload('pro_pic')){
	    	$image_path = $this->upload->data()['file_name'];
	    	$where1 = ['id'=>$this->nativesession->get('id')];
				if($password != null){
					$data_account = [
						'username' => $username,
						"pro_pic" => $image_path,
						'password' => base64_encode(md5($password))
					];
				} else {
					$data_account = [
						'username' => $username,
						"pro_pic" => $image_path
					];
				}
				$this->Model->update('accounts',$data_account,$where1);
	    } else {
	    	$where1 = ['id'=>$this->nativesession->get('id')];
				if($password != null){
					$data_account = [
						'username' => $username,
						'password' => base64_encode(md5($password))
					];
				} else {
					$data_account = [
						'username' => $username
					];
				}
				$this->Model->update('accounts',$data_account,$where1);
	    }
			$where = ['account_id'=>$this->nativesession->get('id')];
			$data = [
				"firstname" => $fname,
				"middle_initial" => $mi,
				"extension" => $extension,
				"course" => $course,
				"blood_type" => $blood_type,
				"weight" => $weight,
				"height" => $height,
				"allergies" => $allergies,
				"medications" => $medications,
				"contact_number" => $contact_number,
				"lastname" => $lname
			];
			// var_dump($data);exit;
			$this->Model->update('information',$data,$where);

			$message =  base64_encode("success~Your information successfully updated.");
			redirect(base_url('athletes/profile/?m='.$message));
	}

	public function surveys(){
		$row = $this->checkAccountNotNull();
		$allSurveys = $this->Model->getAllSurveys();
		$array_survey = array();
		foreach($allSurveys as $value){
			$where = ['account_id'=>$row->account_id,'survey_id'=>$value->id];
			$check = $this->Model->checkSurvey($where);
			if(count($check)==0){
				array_push($array_survey, $value);
			}
		}
		$data['AnswerSurvey'] = $array_survey;
		$data['allCriteria'] = $this->Model->getAllData('survey_criterias');
		$this->heading($row);
		$this->load->view('surveys',$data);
		$this->load->view('footer');
	}

	public function sent_survey(){
		$row = $this->checkAccountNotNull();
		$survey_id = $this->uri->segment(3);
		$account_id = $row->account_id;
		$array_answer = array();
		$criterias = $this->Model->getAllData('survey_criterias');
		foreach($criterias as $value){
			$array_answer[$value->id] = $this->input->post('answer'.$value->id);
		}
		$data = [
			'account_id' =>$account_id,
			'survey_id' =>$survey_id,
			'answer' =>json_encode($array_answer),
			'name' =>$this->input->post('checkbox'),
			'suggestions' =>$this->input->post('suggestions')
		];

		$message = base64_encode('success~Survey successfully sent.');
		$this->Model->insertData('athletes_surveys',$data);
		redirect(base_url('athletes/surveys/?m='.$message));
	}

	function heading($row){
		$data['hresult'] = $row;
		$allSurveys = $this->Model->getAllSurveys();
		$count_survey = 0;
		foreach($allSurveys as $value){
			$where = ['account_id'=>$row->account_id,'survey_id'=>$value->id];
			$check = $this->Model->checkSurvey($where);
			if(count($check)==0){
				$count_survey++;
			}
		}

		$data['numSurveys'] = $count_survey;
		$this->load->view('head.php');
		$this->load->view('header.php',$data);
	}

	public function logout(){	
		$this->nativesession->delete('id');
		redirect(base_url());
	}

	function checkAccountNotNull(){
		$id = $this->nativesession->get('id');
		if($id == NULL){
			$message = base64_encode("errorrr~You have to login first before you can access the page.");
			redirect(base_url('?m='.$message));
		} else {
			$where = array(
				'a.id' => $id
			);
			$rows = $this->Model->CheckAccount( 'accounts' , $where );

			if($rows->usertype != 3){
				$message = base64_encode("errorrr~Restricted page. Your account is not athlete type.");
				redirect(base_url('?m='.$message));
			} else {
				return $rows;
			}
			
		}
	}

	function checkAccountUpdated($row){
		if($row->updated == 0){
			$message = base64_encode("errorrr~Before continuing, update your account.");
			redirect(base_url('athletes/changeAccount/?m='.$message));
		}
	}
	
}
