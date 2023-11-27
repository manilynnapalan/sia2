<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends MX_Controller {
	public function __construct() {
        parent::__construct();
        $this->load->model(array('Adm_model'));
        $this->load->library(array('qrlib'));
        $sy = $this->Adm_model->getSchoolYear()->school_year;
        $this->nativesession->set('school_year',$sy);
        // var_dump(base64_encode(md5('Ch@ngeMe!')));exit;
    } 
    
	public function index()
	{	
		$where = [];
		$data['announcements'] = $this->Adm_model->checkDuplicateData( 'posts' , $where );
		$this->load->view('index',$data);
	}
    
	public function login_page()
	{	
		$this->load->view('login_page');
	}

	public function login(){
		$username = $this->input->post('username');
		$password = $this->input->post('password');
		$where = array(
			'username' => $username,
			'password' => base64_encode(md5($password))
		);
		// var_dump($where);exit;
		$rows = $this->Adm_model->CheckAccount( 'accounts' , $where );
		if($rows != NULL){
			if($rows->usertype == 1){
				$this->nativesession->set('id',$rows->account_id);
				$message = base64_encode('success~Welcome '.$rows->username.'!');
				redirect(base_url('admin/home/?m='.$message));
			} else if($rows->usertype == 2){
				$this->nativesession->set('id',$rows->account_id);
				$message = base64_encode('success~Welcome '.$rows->username.'!');
				redirect(base_url('coaches/?m='.$message));
			} else if($rows->usertype == 3){
				$this->nativesession->set('id',$rows->account_id);
				$message = base64_encode('success~Welcome '.$rows->username.'!');
				redirect(base_url('athletes/?m='.$message));
			}
		} else {
			$message = base64_encode("errorrr~Incorrect username or password.");
			redirect(base_url('admin/login_page/?m='.$message));
		}
	}

	public function home(){
		$account_id = $this->nativesession->get('id');
		$row = $this->checkAccountNotNull();
		$this->heading($row);
		$school_year_start = explode('-', $this->nativesession->get('school_year'))[0];
		$school_year_end = explode('-', $this->nativesession->get('school_year'))[1];

		$data['allSports'] = $this->Adm_model->getAllSPorts();
		$data['array_result'] = array();
		foreach($data['allSports'] as $row){
			$result = $this->Adm_model->getRowsBySports($row->sport_name,$school_year_start,$school_year_end);
			array_push($data['array_result'], array(
				'sport_name' => $row->sport_name,
				'color' => $row->calendar_color,
				'result' => $result,
				'num_rows' => count($result)
			));
		}
		// var_dump($data['array_result']);
		// exit;
		$allEvents = $this->Adm_model->getAllEventsFooter();
		$year = $this->input->get('y') != null ? $this->input->get('y') : date('Y');
		$week = $this->input->get('w') != null ? $this->input->get('w') : (date('W') + 1);
		if($week > 52) {
		    $year++;
		    $week = 1;
		} elseif($week < 1) {
		    $year--;
		    $week = 52;
		}
		$year1 = $this->input->get('y') != null ? $this->input->get('y') : date('Y');
		$week1 = $this->input->get('w') != null ? $this->input->get('w') : (date('W') + 1);
		if($week1 > 52) {
		    $year1++;
		    $week1 = 1;
		} elseif($week1 < 1) {
		    $year1--;
		    $week1 = 52;
		}
		if($week > 52) {
		    $year1++;
		    $week1 = 1;
		} elseif($week1 < 1) {
		    $year1--;
		    $week1 = 52;
		}
		if($week < 10) {
		  $week3 = '0'. $week;
		} else {
			$week3 = $week;
		}
		$day_text_start = 0;
		$day_text_end = 6;
		$data['calendar_table'] =  '<div class="card"> <div class="card-header"> 
			<a href="'.base_url('admin/home/?w='.($week == 1 ? 52 : $week -1).'&y='.($week == 1 ? $year - 1 : $year)).'" style="border-bottom-right-radius: 0px; border-top-right-radius: 0px;" class="fc-prev-button btn btn-primary" aria-label="prev"><span class="fa fa-chevron-left"></span></a>
			<a href="'.base_url('admin/home/?w='.($week == 52 ? 1 : 1 + $week).'&year='.($week == 52 ? 1 + $year : $year)).'" class="fc-next-button btn btn-primary" style="border-bottom-left-radius: 0px; border-top-left-radius: 0px;" aria-label="next"><span class="fa fa-chevron-right"></span></a> 
			<a href="'.base_url('admin/home/?w='.(date('W')+0).'&year='.date('Y')).'" class="fc-next-button btn btn-primary" aria-label="next">Today</a>
    	<h1 class="text-center">'.date('M d',strtotime($year ."W". $week3 . $day_text_start)).' - '.date('d, Y',strtotime($year ."W". $week3 . $day_text_end)).'</h1>
			</div>
      <div class="card-body">
        <table class="table table-bordered">
          <thead>
            <tr>';
              if($week < 10) {
							  $week = '0'. $week;
							}
							for($day= 0; $day <= 6; $day++) {
						    $d = strtotime($year ."W". $week . $day);
						    $days = date('D d/m', $d);
						    $today = date('D d/m');
						    if($today == $days){
						    	$data['calendar_table'] .=  "<th style='background-color:#fffadf; width: 14.28%;text-align: center;'>".$days."</th>";
						    } else {
						    	$data['calendar_table'] .=  "<th style='width: 14.28%;text-align: center;'>".$days."</th>";
						    }
							}
            $data['calendar_table'] .= '</tr> </thead> <tbody> <tr>';
              if($week1 < 10) {
							  $week1 = '0'. $week1;
							}
							for($day1= 0; $day1 <= 6; $day1++) {
						    $d1 = strtotime($year1 ."W". $week1 . $day1);
						    $days1 = date('D d/m', $d1);
						    $today1 = date('D d/m');
						    $style = $today1 == $days1 ? 'style="background-color:#fffadf"' : '';
						    $data['calendar_table'] .=  '<td '.$style.'>';

						    foreach($allEvents as $row){
						    	if(date('Y-m-d',strtotime($row->date)) == date('Y-m-d',$d1)){
							    	$data['calendar_table'] .= '<div class="small-box" style="background-color: '.$row->calendar_color.'; color:white;"> 
							    		<div class="inner">
	                			<label>'.$row->sport_name.'</label></br>
	                			<label>'.$row->venue.'</label></br>
	                			<label>'.$row->event_name.'</label></br>
	                			<label>'.$row->description.'</label></br>
	                			<label>'.$row->start_time.'-'.$row->end_time.'</label></br>
	              			</div>
	            			</div>';
	            		}
            		}
							}
            $data['calendar_table'] .=  '</td> </tr> </tbody> </table> </div> </div>';
		$this->load->view('home',$data);
		$this->load->view('footer',$data);
	}

	public function athletes(){
		$account_id = $this->nativesession->get('id');
		$school_year = $this->input->get('sy');
		if($school_year != 'ALL SCHOOL YEAR' && $school_year != null){
			$where = [
				'sy_start' => explode('-', $school_year)[0],
				'sy_end' => explode('-', $school_year)[1],
				'a.usertype' => 3
			];
		} else {
			$where = [
				'a.usertype' => 3
			];
		}
		
		$row = $this->checkAccountNotNull();
		$data['allSports'] = $this->Adm_model->getAllSPorts();
		$data['allData'] = $this->Adm_model->getAllRowsByUsertype($where);
		$this->heading($row);
		$this->load->view('athletes',$data);
		$this->load->view('footer');
	}

	public function insert_athletes(){	
		$username = $this->input->post('id_number');
		$where = array(
			'username' => $username
		);
		$checkUN = $this->Adm_model->CheckAccount('accounts',$where);
		if($checkUN == NULL){
			$gender = $this->input->post('gender');
			$sport = $this->input->post('sport');
			$fname = $this->input->post('fname');
			$lname = $this->input->post('lname');
			$mi = $this->input->post('mi');
			$course = $this->input->post('course');
			$address = $this->input->post('address');
			$datebirth = $this->input->post('datebirth');
			$course_yr = $this->input->post('course_yr');
			$school_year = $this->input->post('school_year');

			$config['upload_path'] = FCPATH."assets\images";
      $config['allowed_types'] = 'gif|jpg|png|jpeg';
      $config['max_size'] = 100000;
      $config['max_width'] = 5000;
      $config['max_height'] = 5000;

      $this->load->library('upload', $config);
      $image_name = $_FILES['pro_pic']['name'];
      $image_path = './assets/pro_pic_images/'.$image_name;
      	if(!$this->upload->do_upload('pro_pic')){
            $message1 = $this->upload->display_errors();
            if('You did not select a file to upload.' == $message1){
            	$message = base64_encode("success~New user successfully added. The profile picture is default image because you didn't select an image.");
            } else {
            	$message =  base64_encode("success~".$message1);
            }
            
            $image_path = 'pro_pic_icon_admin.png';
        } else {
        	$image_path = $this->upload->data()['file_name'];
        	$message = base64_encode('success~New user successfully added.');
        }

        $accounts_data = [
					"username" => $username,
					"password" => base64_encode(md5('Ch@ngeMe!')),
					"pro_pic" => $image_path,
					"usertype" => 3
				];

				$account_id = $this->Adm_model->insertData('accounts',$accounts_data);
		        
		    $info_data = [
					"account_id" => $account_id,
					"firstname" => $fname,
					"lastname" => $lname,
					"middle_initial" => $mi,
					"birthdate" => date('Y-m-d',strtotime($datebirth)),
					"address" => $address,
					"course" => $course,
					"gender" => $gender,
					"sy_start" => explode('-', $school_year)[0],
					"sy_end" => explode('-', $school_year)[1],
					"sports" => $sport
				];
				$check = $this->Adm_model->insertData('information',$info_data);
				if($check != NULL){
        	$message = base64_encode('success~New user successfully added.');
				} else {
					$message = base64_encode("errorrr~There's an error in saving the data. Please contact the Developer.");
				}	
    } else {
    	$message = base64_encode('errorrr~Username has been used.');
    }
   	redirect(base_url('admin/athletes/?m='.$message));
	}

	public function update_athlete(){
		$this->checkAccountNotNull();
		$gender = $this->input->post('up_gender');
		$sport = $this->input->post('up_sport');
		$fname = $this->input->post('up_fname');
		$lname = $this->input->post('up_lname');
		$course = $this->input->post('up_course');
		$address = $this->input->post('up_address');
		$datebirth = $this->input->post('up_datebirth');
		$course_yr = $this->input->post('up_course');
		$school_year = $this->input->post('up_school_year');
		$id = $this->uri->segment(3);

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
    	$where1 = ['id'=>$id];
			$data1 = [
				"pro_pic" => $image_path
			];

			$this->Adm_model->updateData('accounts',$data1,$where1);
    }

		$where = ['account_id'=>$id];
		$info_data = [
			"firstname" => $fname,
			"lastname" => $lname,
			"birthdate" => date('Y-m-d',strtotime($datebirth)),
			"address" => $address,
			"course" => $course,
			"gender" => $gender,
			"sy_start" => explode('-', $school_year)[0],
			"sy_end" => explode('-', $school_year)[1],
			"sports" => $sport
		];

		$this->Adm_model->updateData('information',$info_data,$where);
		$message =  base64_encode("success~Athlete information successfully updated.");
		redirect(base_url('admin/athletes/?m='.$message));
	}

	public function delete_athletes(){
		$this->checkAccountNotNull();
		$where1 = ['id'=>$this->uri->segment(3)];
		$this->Adm_model->deleteData('accounts',$where1);
		$where = ['account_id'=>$this->uri->segment(3)];
		$this->Adm_model->deleteData('information',$where);
		$message =  base64_encode("errorrr~Athlete successfully deleted.");
		redirect(base_url('admin/athletes/?m='.$message));
	}

	public function surveys(){
		$account_id = $this->nativesession->get('id');
		$row = $this->checkAccountNotNull();
		$data['getSchoolYear'] = $this->Adm_model->getAllSPorts();
		$data['allSports'] = $this->Adm_model->getAllSPorts();
		$data['allCriteria'] = $this->Adm_model->getAllData('survey_criterias');
		$data['getSentSurvey'] = $this->Adm_model->getAllData('surveys_to_answer');
		$array_result = array();
		foreach($data['getSentSurvey'] as $value){
			$where = ['t1.survey_id'=>$value->id];
			$rows = $this->Adm_model->getAllAthletesSurvey('athletes_surveys',$where);
			array_push($array_result, [
				'survey_row' => $value,
				'numberResponces' => count($rows)
			]);
		}
		$data['result'] = $array_result;
		// var_dump($array_result);exit;
		$this->heading($row);
		$this->load->view('surveys',$data);
		$this->load->view('footer');
	}

	public function insert_criteria(){
		$row = $this->checkAccountNotNull();
		$criteria = $this->input->post('criteria');

		$data = ['criteria'=>$criteria];
		$check = $this->Adm_model->checkDuplicateData('survey_criterias',$data);
		if(count($check) > 0){
			$message = base64_encode('success~Criteria exist.');
		} else {
			$message = base64_encode('success~Criteria successfully saved.');
			$this->Adm_model->insertData('survey_criterias',$data);
		}
		
   	redirect(base_url('admin/surveys/?m='.$message));
	}

	public function update_criteria(){
		$row = $this->checkAccountNotNull();
		$criteria = $this->input->post('criteria');
		$where = ['id'=>$this->uri->segment(3)];

		$data = ['criteria'=>$criteria];
		$check = $this->Adm_model->checkDuplicateData('survey_criterias',$data);
		if(count($check) > 0){
			$message = base64_encode('errorrr~Criteria exist.');
		} else {
			$message = base64_encode('success~Criteria successfully updated.');
			$this->Adm_model->updateData('survey_criterias',$data,$where);
		}
		
   	redirect(base_url('admin/surveys/?m='.$message));
	}

	public function delete_criteria(){
		$this->checkAccountNotNull();
		$where1 = ['id'=>$this->uri->segment(3)];
		$this->Adm_model->deleteData('survey_criterias',$where1);
		$message =  base64_encode("errorrr~Criteria successfully deleted.");
		redirect(base_url('admin/surveys/?m='.$message));
	}

	public function send_survey(){
		$this->checkAccountNotNull();
		$school_year = $this->input->post('school_year');
		$semester = $this->input->post('semester');
		$data = ['school_year'=>$school_year,'semester'=>$semester];
		$check = $this->Adm_model->checkDuplicateData('surveys_to_answer',$data);
		if(count($check) > 0){
			$message = base64_encode('errorrr~You have already send a survey.');
		} else {
			$message = base64_encode('success~Survey successfully send.');
			$this->Adm_model->insertData('surveys_to_answer',$data);
		}
		
   	redirect(base_url('admin/surveys/?m='.$message));
	}

	public function coaches(){
		$row = $this->checkAccountNotNull();
		$data['allSports'] = $this->Adm_model->getAllSPorts();
		$where = ['usertype'=>2];
		$data['allCoaches'] = $this->Adm_model->getAllRowsByUsertype($where);
		$this->heading($row);
		$this->load->view('coaches',$data);
		$this->load->view('footer');
	}

	public function view_survey(){
		$row = $this->checkAccountNotNull();
		$where = ['survey_id'=>$this->uri->segment(3)];
		$data['result_array'] = array();
		$result = $this->Adm_model->getAllAthletesSurvey('athletes_surveys',$where);
		foreach($result as $value){
			$where = ['a.usertype' => 2, 'i.sports'=> $value->sports];
			array_push($data['result_array'], array(
				'coach_info' => $this->Adm_model->getCoachInfoBySport($where),
				'student_info' => $value
			));
		}	
		$data['survey_criterias'] = $this->Adm_model->getAllData('survey_criterias');
		
		$data['hresult'] = $row;
		$this->heading($row);
		$this->load->view('view_surveys',$data);
		$this->load->view('footer');
	}

	public function insert_reg(){	
		$fname = $this->input->post('fname');
		$lname = $this->input->post('lname');
		$username = $this->input->post('username');
		$where = array(
			'username' => $username
		);
		$checkUN = $this->Adm_model->CheckAccount('accounts',$where);
		if($checkUN == NULL){
			$gender = $this->input->post('gender');
			$sport = $this->input->post('sport');

			$config['upload_path'] = FCPATH."assets\images";
      $config['allowed_types'] = 'gif|jpg|png|jpeg';
      $config['max_size'] = 100000;
      $config['max_width'] = 5000;
      $config['max_height'] = 5000;

      $this->load->library('upload', $config);
      $image_name = $_FILES['pro_pic']['name'];
      $image_path = './assets/pro_pic_images/'.$image_name;
      	if(!$this->upload->do_upload('pro_pic')){
            $message1 = $this->upload->display_errors();
            if('You did not select a file to upload.' == $message1){
            	$message = base64_encode("success~New user successfully added. The profile picture is default image because you didn't select an image.");
            } else {
            	$message =  base64_encode("success~".$message1);
            }
            
            $image_path = 'pro_pic_icon_admin.png';
        } else {
        	$image_path = $this->upload->data()['file_name'];
        	$message = base64_encode('success~New user successfully added.');
        }

        $accounts_data = [
					"username" => $username,
					"password" => base64_encode(md5('Ch@ngeMe!')),
					"pro_pic" => $image_path,
					"usertype" => 2
				];

				$account_id = $this->Adm_model->insertData('accounts',$accounts_data);
		        
		    $info_data = [
					"account_id" => $account_id,
					"firstname" => $fname,
					"lastname" => $lname,
					"gender" => $gender,
					"sports" => $sport
				];

				$check = $this->Adm_model->insertData('information',$info_data);
				if($check != NULL){
        	$message = base64_encode('success~New user successfully added.');
				} else {
					$message = base64_encode("errorrr~There's an error in saving the data. Please contact the Developer.");
				}	
    } else {
    	$message = base64_encode('errorrr~Username has been used.');
    }
   	redirect(base_url('admin/coaches/?m='.$message));
	}

	public function update_data(){
		$this->checkAccountNotNull();
		$fname = $this->input->post('fname');
		$lname = $this->input->post('lname');
		$gender = $this->input->post('gender');
		$sport = $this->input->post('sport');
		$password = $this->input->post('password');

		if($password != null){
			$where1 = ['account_id'=>$this->uri->segment(3)];
			$data1 = [
				"password" => base64_encode(md5($password))
			];
			$this->Adm_model->updateData('accounts',$data1,$where1);
		}
		$where = ['account_id'=>$this->uri->segment(3)];
		$data = [
			"firstname" => $fname,
			"lastname" => $lname,
			"gender" => $gender,
			"sports" => $sport
		];

		$this->Adm_model->updateData('information',$data,$where);
		$message =  base64_encode("success~Coach information successfully updated.");
		redirect(base_url('admin/coaches/?m='.$message));
	}

	public function delete_coach(){
		$this->checkAccountNotNull();
		$where = ['id'=>$this->uri->segment(3)];
		$this->Adm_model->deleteData('accounts',$where);

		$where1 = ['account_id'=>$this->uri->segment(3)];
		$this->Adm_model->deleteData('information',$where1);
		$message =  base64_encode("errorrr~Coach information successfully deleted.");
		redirect(base_url('admin/coaches/?m='.$message));
	}

	public function sports(){
		$account_id = $this->nativesession->get('id');
		$row = $this->checkAccountNotNull();
		$data['allSports'] = $this->Adm_model->getAllSPorts();
		$this->heading($row);
		$this->load->view('sports',$data);
		$this->load->view('footer');
	}

	public function insert_sport(){
		$account_id = $this->nativesession->get('id');
		$this->checkAccountNotNull();
		$data = ['sport_name'=>$this->input->post('sport')];
		$check = $this->Adm_model->checkDuplicateData('sports',$data);
		if($check != null){
			$message =  base64_encode("errorrr~Team sport already exist!");
		} else {
			$this->Adm_model->insertData('sports',$data);
			$message =  base64_encode("success~Team sport successfully added.");
		}
		redirect(base_url('admin/sports/?m='.$message));
	}

	public function update_sport(){
		$account_id = $this->nativesession->get('id');
		$this->checkAccountNotNull();
		$where = ['id'=>$this->uri->segment(3)];
		$data = ['sport_name'=>$this->input->post('sport')];
		$check = $this->Adm_model->checkDuplicateData('sports',$data);
		if($check != null){
			$message =  base64_encode("errorrr~Team sport already exist!");
		} else {
			$this->Adm_model->updateData('sports',$data,$where);
			$message =  base64_encode("success~Team sport successfully updated.");
		}
		redirect(base_url('admin/sports/?m='.$message));
	}

	public function delete_sport(){
		$account_id = $this->nativesession->get('id');
		$this->checkAccountNotNull();
		$where = ['id'=>$this->uri->segment(3)];
		$this->Adm_model->deleteData('sports',$where);
		$message =  base64_encode("errorrr~Team sport successfully deleted.");
		redirect(base_url('admin/sports/?m='.$message));
	}
	
	public function attendance(){
		$row = $this->checkAccountNotNull();
		if($this->input->get('ts') != null){
			$where = ['i.sports'=>$this->input->get('ts')];	
		} else {
			$where = [];	
		}
		
		$data['allEvents'] = $this->Adm_model->getAllEvents($where);
		$data['allSports'] = $this->Adm_model->getAllSPorts();
		$this->heading($row);
		$this->load->view('attendance',$data);
		$this->load->view('footer');
	}

	public function checkAttendance(){
		$row = $this->checkAccountNotNull();
		$where = ['att_event_id'=>$this->uri->segment(3)];
		$attendances = $this->Adm_model->getAttendancesByEventId($where);
		$array_athletes_id = array();
		$array_attendance = array();
		$array_status = array();
		$array_remarks = array();
		if($attendances != null){
			foreach($attendances as $row1){
				array_push($array_athletes_id, $row1->att_account_id);
				array_push($array_attendance, $row1->time_present);
				array_push($array_remarks, $row1->remarks);
				array_push($array_status, $row1->status);
			}
		}
		$where2 = ['e.id'=>$this->uri->segment(3)];
		$data['eventRow'] = $this->Adm_model->getEventById($where2);
		// var_dump($data['eventRow'] );exit;
		$where1 = [
			'i.sports'=>$data['eventRow']->sports,
			'a.usertype'=>3
		];
		$data['AllAthletes'] = $this->Adm_model->getAthletes($where1);
		$no_presents = 0;
		foreach ($data['AllAthletes'] as $row) {
			if(in_array($row->account_id, $array_athletes_id)){
				$no_presents++;
			}
		}

		$data['arrayAccountId'] = $array_athletes_id;
		$data['arrayTimePresent'] = $array_attendance;
		$data['arrayRemarks'] = $array_remarks;
		$data['noPresents'] = $no_presents;
		$data['arrayStatus'] = $array_status;
		$this->heading($row);
		$this->load->view('checkAttendance',$data);
		$this->load->view('footer');
	}

	public function athleteStatus(){
		$row6 = $this->checkAccountNotNull();
		if(@$this->input->get('ts') != null){
			$where = ['a.usertype'=>3,'i.sports'=>$this->input->get('ts')];	
		} else {
			$where = ['a.usertype'=>3];	
		}
		$array_results = array();
		$allAthletes = $this->Adm_model->getAthletes($where);
		foreach($allAthletes as $row){
			$where1 = ['i.sports'=>$row->sports,'a.usertype'=>2];
			$coach_row = $this->Adm_model->CheckAccount('accounts',$where1);
			$where2 = ['coach_id'=>@$coach_row->id];
			$events = $this->Adm_model->getEventsByCoachID($where2);
			$no_absent = 0;
			foreach($events as $row1){
				$where3 = [
					'att_event_id' => $row1->id,
					'att_account_id '=> $row->account_id
				];
				$result = $this->Adm_model->getAttendancesByEventId($where3);
				// var_dump($result);exit;
				if($result != NULL){
					if($result->status == 'Absent'){
						$no_absent++;
					}
				}
			}
			array_push($array_results,[
				'row_info' => $row,
				'coach' => $coach_row,
				'number_absences' => $no_absent
			]);
		}
		$data['allResult'] = $array_results;
		$data['allSports'] = $this->Adm_model->getAllSPorts();
		$this->heading($row6);
		$this->load->view('athletes_status',$data);
		$this->load->view('footer');
	}

	public function post(){
		$row = $this->checkAccountNotNull();
		$data['allDocumentation'] = $this->Adm_model->getDocumentation();
		$this->heading($row);
		$this->load->view('post_documentation',$data);
		$this->load->view('footer');
	}

	public function insert_documentation(){	
		$row = $this->checkAccountNotNull();
		$title = $this->input->post('title');
		$description = $this->input->post('description');
		$post_account_id = $this->nativesession->get('id');
		$sports = $row->sports;

		$config['upload_path'] = FCPATH."assets\post_images";
	    $config['allowed_types'] = 'gif|jpg|png|jpeg';
	    $config['max_size'] = 100000;
	    $config['max_width'] = 5000;
	    $config['max_height'] = 5000;

	    $this->load->library('upload', $config);
	    $image_name = $_FILES['pro_pic']['name'];
	    $image_path = './assets/pro_pic_images/'.$image_name;
	  	if(!$this->upload->do_upload('pro_pic')){
	        $message1 = $this->upload->display_errors();
	        if('You did not select a file to upload.' == $message1){
	        	$message = base64_encode("success~New user successfully added. The profile picture is default image because you didn't select an image.");
	        } else {
	        	$message =  base64_encode("success~".$message1);
	        }
	        
	        $image_path = 'pro_pic_icon_admin.png';
	    } else {
	    	$image_path = $this->upload->data()['file_name'];
	    	$message = base64_encode('success~New user successfully added.');
	    }

	    $data = [
				"title" => $title,
				"description" => $description,
				"image" => $image_path,
				"sport_team" => 'admin',
				"post_account_id" => $row->account_id
			];
			$check = $this->Adm_model->insertData('posts',$data);
			if($check != NULL){
	    	$message = base64_encode('success~New documentation successfully added.');
			} else {
				$message = base64_encode("errorrr~There's an error in saving the data. Please contact the Developer.");
			}	
	   	redirect(base_url('admin/post/?m='.$message));
	}

	public function delete_post(){
		$this->checkAccountNotNull();
		$where = ['id'=>$this->uri->segment(3)];
		$this->Adm_model->deleteData('posts',$where);
		$message =  base64_encode("errorrr~Documentation successfully deleted.");
		redirect(base_url('admin/post/?m='.$message));
	}

	public function profile(){
		$row = $this->checkAccountNotNull();
		// var_dump($row);exit;
		$data['hresult'] = $row;
		$this->heading($row);
		$this->load->view('profile',$data);
		$this->load->view('footer');
	}

	public function update_profile(){
		$row = $this->checkAccountNotNull();
		$lname = $this->input->post('lname');
		$fname = $this->input->post('fname');
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
			$this->Adm_model->update('accounts',$data_account,$where1);
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
			$this->Adm_model->update('accounts',$data_account,$where1);
    }
		$where = ['account_id'=>$this->nativesession->get('id')];
		$data = [
			"firstname" => $fname,
			"lastname" => $lname
		];

		$this->Adm_model->updateData('information',$data,$where);

		$message =  base64_encode("success~Admin information successfully updated.");
		redirect(base_url('admin/profile/?m='.$message));
	}

	public function update_SY(){
		$account_id = $this->nativesession->get('id');
		$this->checkAccountNotNull();
		$where = ['id'=>1];
		$data = ['school_year'=>$this->input->post('school_year')];
		$this->Adm_model->updateData('active_school_year',$data,$where);
		$message =  base64_encode("success~School Year successfully set.");
		redirect(base_url('admin/home/?m='.$message));
	}

	function heading($row){
		$data['hresult'] = $row;
		// var_dump($data['hresult']);exit;
		$this->load->view('head.php');
		$this->load->view('header.php',$data);
	}

	public function logout()
	{	
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
			$rows = $this->Adm_model->CheckAccount( 'accounts' , $where );
			if($rows->usertype != 1){
				$message = base64_encode("errorrr~Restricted page. Your account is not an admin type.");
				redirect(base_url('?m='.$message));
			} else {
				return $rows;
			}
			
		}
	}
}
