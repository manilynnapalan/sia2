<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Coaches extends MX_Controller {
	public function __construct() {
        parent::__construct();
        $this->load->model(array('Model'));
    } 
    
	public function index(){		
		$color = ['#db3939','#e83e8c','#6610f2','#007bff','#3498db','#20c997','#37ef61','#ffc107','#fd7e14','#d877df'];
		$account_id = $this->nativesession->get('id');
		$row = $this->checkAccountNotNull();
		$this->heading($row);
		$where = ['e.coach_id'=>$this->nativesession->get('id')];
		$data['allEvents'] = $this->Model->getAllEvents($where);;
		$data['bgcolor_index'] = array();
		$data['array_result'] = array();
		foreach($data['allEvents'] as $row){
			$where_event_id = ['att_event_id'=>$row->event_id];
			$result = $this->Model->getAttendancesByEventId($where_event_id);
			$ran_number = rand(0,9);
			array_push($data['array_result'], array(
				'date' => $row->date,
				'sport_name' => $row->event_name,
				'color' => $color[$ran_number],
				'num_rows' => count($result)
			));
			$data['bgcolor_index'][$row->event_id] = $color[$ran_number];
		}
		$year = $this->input->get('y') != null ? $this->input->get('y') : date('Y');
		$week = $this->input->get('w') != null ? $this->input->get('w') : (date('W') + 0);
		if($week > 52) {
		    $year++;
		    $week = 1;
		} elseif($week < 1) {
		    $year--;
		    $week = 52;
		}
		$year1 = $this->input->get('y') != null ? $this->input->get('y') : date('Y');
		$week1 = $this->input->get('w') != null ? $this->input->get('w') : (date('W') + 0);
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
			<a href="'.base_url('coaches/?w='.($week == 1 ? 52 : $week -1).'&y='.($week == 1 ? $year - 1 : $year)).'" style="border-bottom-right-radius: 0px; border-top-right-radius: 0px;" class="fc-prev-button btn btn-primary" aria-label="prev"><span class="fa fa-chevron-left"></span></a>
			<a href="'.base_url('coaches/?w='.($week == 52 ? 1 : 1 + $week).'&year='.($week == 52 ? 1 + $year : $year)).'" class="fc-next-button btn btn-primary" style="border-bottom-left-radius: 0px; border-top-left-radius: 0px;" aria-label="next"><span class="fa fa-chevron-right"></span></a> 
			<a href="'.base_url('coaches/?w='.(date('W') + 0).'&year='.date('Y')).'" class="fc-next-button btn btn-primary" aria-label="next">Today</a>
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

						    foreach($data['allEvents'] as $row){
						    	if(date('Y-m-d',strtotime($row->date)) == date('Y-m-d',$d1)){
							    	$data['calendar_table'] .= '<div class="small-box" style="background-color: '.$data['bgcolor_index'][$row->event_id].'; color:white;"> 
							    		<div class="inner">
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

	public function scuaa_games(){
		$row = $this->checkAccountNotNull();
		$this->checkAccountUpdated($row);
		$where = ['sport_id'=>$row->sports];
		$data['scuaaGames'] = $this->Model->getScuaaGames($where);
		$data['Sport'] = $row->sports;
		$this->heading($row);
		$this->load->view('scuaa_games',$data);
		$this->load->view('footer');
	}

	public function scuaa_forms($sg_id){
		$row = $this->checkAccountNotNull();
		$this->checkAccountUpdated($row);
		$where = ['id'=>$sg_id];
		$data['scuaaGameDetails'] = $this->Model->getScuaaGamesByRow('scuaa_games',$where);
		$ids = json_decode($data['scuaaGameDetails']->student_ids);
		
		$array_data = array();
		for($x=0;$x<count($ids);$x++){
			$where1 = ['i.account_id' => $ids[$x]];
			$student_info = $this->Model->playerDetailsByRow('information i',$where1);
			array_push($array_data,$student_info);
		}
		$data['studentsInfo'] = $array_data;
		$data['coachDetails'] = $row;
		// var_dump($row);exit;
		$this->heading($row);
		$this->load->view('scuaa_form',$data);
		$this->load->view('footer');
	}

	public function insert_game(){
		$row = $this->checkAccountNotNull();
		$this->checkAccountUpdated($row);
		$data = $this->input->post();
		$bool = $this->Model->insertData('scuaa_games',$data);
		if($bool != NULL){
			$message = base64_encode("success~SCUAA Game successfully saved.");
		} else {
			$message = base64_encode("errorrr~There's an error in saving.");
		}
		redirect(base_url('coaches/scuaa_games/?m='.$message));
	}

	public function update_scuaa($id){
		$row = $this->checkAccountNotNull();
		$this->checkAccountUpdated($row);
		$where = ['id'=>$id];
		$data = $this->input->post();
		$bool = $this->Model->update('scuaa_games',$data,$where);
		if($bool === TRUE){
			$message = base64_encode("success~SCUAA Game successfully updated.");
		} else {
			$message = base64_encode("errorrr~There's an error in updating.");
		}
		redirect(base_url('coaches/scuaa_games/?m='.$message));
	}

	public function delete_scuaa($id){
		$row = $this->checkAccountNotNull();
		$this->checkAccountUpdated($row);
		$where = ['id'=>$id];
		$bool = $this->Model->deleteData('scuaa_games',$where);
		if($bool === TRUE){
			$message = base64_encode("success~SCUAA Game successfully deleted.");
		} else {
			$message = base64_encode("errorrr~There's an error in deleting.");
		}
		redirect(base_url('coaches/scuaa_games/?m='.$message));
	}

	public function checklist($id){
		$row = $this->checkAccountNotNull();
		$this->checkAccountUpdated($row);
		$where = ['id'=>$id];
		$data['scuaaGameDetails'] = $this->Model->getScuaaGamesByRow('scuaa_games',$where);
		$data['Sport'] = $row->sports;
		$where1 = ['i.sports'=>$row->sports,'a.usertype'=>3];
		$data['players'] = $this->Model->getPlayersBySport($where1);
		
		$this->heading($row);
		$this->load->view('checklist',$data);
		$this->load->view('footer');
	}

	public function add_varsity_list($id){
		$row = $this->checkAccountNotNull();
		$this->checkAccountUpdated($row);
		$data = ['student_ids' => json_encode($this->input->post('hidden_var_ids'))];
		$where = ['id'=>$id];
		$bool = $this->Model->update('scuaa_games',$data,$where);
		if($bool === TRUE){
			$message = base64_encode("success~Kindly tell you players to update their data like weight, height, allergies and etc.");
			redirect(base_url('coaches/scuaa_forms/'.$id.'/?m='.$message));
		} else {
			$message = base64_encode("errorrr~There's an error in adding the students in checklist.");
			redirect(base_url('coaches/scuaa_games/'.$id.'/?m='.$message));
		}
		
	}

	public function venue(){
		$row = $this->checkAccountNotNull();
		$this->checkAccountUpdated($row);
		$data['allVenue'] = $this->Model->getAllVenue();
		$this->heading($row);
		$this->load->view('venues',$data);
		$this->load->view('footer');
	}

	public function insert_venue(){
		$this->checkAccountNotNull();
		$venue = $this->input->post('venue');
		$data = [
			'venue' => $venue
		];
		$check_duplicate = $this->Model->check_duplicate($data);
		if($check_duplicate == null){
			$this->Model->insertData('venue',$data);
			$message = base64_encode("success~Venue successfully saved.");
		} else {
			$message = base64_encode("errorrr~Duplicate venue. Data not save.");
		}
		
		redirect(base_url('coaches/venue/?m='.$message));
	}

	public function update_venue(){
		$this->checkAccountNotNull();
		$venue = $this->input->post('up_venue');
		$where = ['id'=>$this->uri->segment(3)];
		$data = [
			'venue' => $venue
		];
		$check_duplicate = $this->Model->check_duplicate($data);
		if($check_duplicate == null){
			$this->Model->update('venue',$data,$where);
			$message = base64_encode("success~Venue successfully updated.");
		} else {
			$message = base64_encode("errorrr~Duplicate venue. Data not save.");
		}
		
		redirect(base_url('coaches/venue/?m='.$message));
	}

	public function delete_venue(){
		$this->checkAccountNotNull();
		$where = ['id'=>$this->uri->segment(3)];
		$this->Model->deleteData('venue',$where);
		$message =  base64_encode("errorrr~Venue successfully deleted.");
		redirect(base_url('coaches/venue/?m='.$message));
	}

	public function events(){
		$row = $this->checkAccountNotNull();
		$this->checkAccountUpdated($row);
		$where = ['e.coach_id'=>$this->nativesession->get('id')];
		$data['allEvents'] = $this->Model->getAllEvents($where);
		$data['allVenue'] = $this->Model->getAllVenue();
		$this->heading($row);
		$this->load->view('events',$data);
		$this->load->view('footer');
	}

	public function insert_event(){
		$this->checkAccountNotNull();
		$coach_id = $this->nativesession->get('id');
		$event = $this->input->post('event');
		$description = $this->input->post('description');
		$venue = $this->input->post('venue');
		$date = $this->input->post('date');
		$start_time = $this->input->post('start_time');
		$end_time = $this->input->post('end_time');
		$where = [
			'e.date' => date('Y-m-d',strtotime($date)),
			'e.venue' => $venue,
			'e.start_time <=' => date('H:i:s',strtotime($start_time)),
			'e.end_time >' => date('H:i:s',strtotime($start_time))
		];
		$check_time_conflict = $this->Model->check_time_conflict($where);
		// var_dump($check_time_conflict);exit;
		if($check_time_conflict == null){
			$data = [
				'coach_id' => $coach_id,
				'event_name' => $event,
				'description' => $description,
				'venue' => $venue,
				'date' => date('Y-m-d',strtotime($date)),
				'start_time' => $start_time,
				'end_time' => $end_time
			];
			// var_dump($data);exit;
			$this->Model->insertData('events',$data);
			$message = base64_encode("success~Event successfully saved.");
		} else {
			$message = base64_encode("errorrr~Event conflict schedule. (".$check_time_conflict->sports.", ".$check_time_conflict->start_time."-".$check_time_conflict->end_time.")");
		}
		
		redirect(base_url('coaches/events/?m='.$message));
	}

	public function update_event(){
		$this->checkAccountNotNull();
		$where = ['id'=>$this->uri->segment(3)];
		$event = $this->input->post('up_event');
		$description = $this->input->post('up_description');
		$venue = $this->input->post('up_venue');
		$date = $this->input->post('up_date');
		$start_time = $this->input->post('up_start_time');
		$end_time = $this->input->post('up_end_time');
		$data = [
			'event_name' => $event,
			'description' => $description,
			'venue' => $venue,
			'date' => date('Y-m-d',strtotime($date)),
			'start_time' => $start_time,
			'end_time' => $end_time
		];
		// var_dump($data);exit;
		$this->Model->update('events',$data,$where);
		$message = base64_encode("success~Event successfully updated.");
		redirect(base_url('coaches/events/?m='.$message));
	}

	public function delete_events(){
		$this->checkAccountNotNull();
		$where = ['id'=>$this->uri->segment(3)];
		$this->Model->deleteData('events',$where);
		$message =  base64_encode("errorrr~Event successfully deleted.");
		redirect(base_url('coaches/events/?m='.$message));
	}

	public function athletes(){
		$row = $this->checkAccountNotNull();
		$this->checkAccountUpdated($row);
		$school_year = $this->input->get('sy');
		if($school_year != 'ALL SCHOOL YEAR' && $school_year != null){
			$where = [
				'sy_start' => explode('-', $school_year)[0],
				'sy_end' => explode('-', $school_year)[1],
				'i.sports'=>$row->sports,
				'a.usertype' => 3
			];
		} else {
			$where = [
				'i.sports'=>$row->sports,
				'a.usertype' => 3
			];
		}
		$data['allData'] = $this->Model->getAthletes($where);
		$data['sport'] = $row->sports;
		$this->heading($row);
		$this->load->view('athletes',$data);
		$this->load->view('footer');
	}

	public function insert_athletes(){	
		$this->checkAccountNotNull();
		$username = $this->input->post('id_number');
		$where = array(
			'username' => $username
		);
		$checkUN = $this->Model->CheckAccount('accounts',$where);
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

				$account_id = $this->Model->insertData('accounts',$accounts_data);
		        
		    $info_data = [
					"account_id" => $account_id,
					"firstname" => $fname,
					"lastname" => $lname,
					"middle_initial" => $mi,
					"birthdate" => date('Y-m-d',strtotime($datebirth)),
					"address" => $address,
					"course" => $course,
					"gender" => $gender,
					"sports" => $sport,
					"sy_start" => explode('-', $school_year)[0],
					"sy_end" => explode('-', $school_year)[1],
				];
				$check = $this->Model->insertData('information',$info_data);
				if($check != NULL){
        	$message = base64_encode('success~New user successfully added.');
				} else {
					$message = base64_encode("errorrr~There's an error in saving the data. Please contact the Developer.");
				}	
    } else {
    	$message = base64_encode('errorrr~Username has been used.');
    }
   	redirect(base_url('coaches/athletes/?m='.$message));
	}

	public function delete_athletes(){
		$this->checkAccountNotNull();
		$where1 = ['id'=>$this->uri->segment(3)];
		$this->Model->deleteData('accounts',$where1);
		$where = ['account_id'=>$this->uri->segment(3)];
		$this->Model->deleteData('information',$where);
		$message =  base64_encode("errorrr~Athlete successfully deleted.");
		redirect(base_url('coaches/athletes/?m='.$message));
	}

	public function update_athlete(){
		$this->checkAccountNotNull();
		$gender = $this->input->post('up_gender');
		$fname = $this->input->post('up_fname');
		$lname = $this->input->post('up_lname');
		$course = $this->input->post('up_course');
		$address = $this->input->post('up_address');
		$datebirth = $this->input->post('up_datebirth');
		$course_yr = $this->input->post('up_course_yr');
		$school_year = $this->input->post('up_school_year');
		$id = $this->uri->segment(3);

		$config['upload_path'] = FCPATH."assets\images";
    $config['allowed_types'] = 'gif|jpg|png|jpeg';
    $config['max_size'] = 100000;
    $config['max_width'] = 5000;
    $config['max_height'] = 5000;

    $this->load->library('upload', $config);
    $image_name = $_FILES['up_pro_pic']['name'];
    $image_path = './assets/pro_pic_images/'.$image_name;
  	if($this->upload->do_upload('up_pro_pic')){
    	$image_path = $this->upload->data()['file_name'];
    	$where1 = ['id'=>$id];
			$data1 = [
				"pro_pic" => $image_path
			];

			$this->Model->update('accounts',$data1,$where1);
    }

		$where = ['account_id'=>$id];
		$info_data = [
			"firstname" => $fname,
			"lastname" => $lname,
			"birthdate" => date('Y-m-d',strtotime($datebirth)),
			"address" => $address,
			"course" => $course,
			"sy_start" => explode('-', $school_year)[0],
			"sy_end" => explode('-', $school_year)[1],
			"gender" => $gender
		];

		$this->Model->update('information',$info_data,$where);
		$message =  base64_encode("success~Athlete information successfully updated.");
		redirect(base_url('coaches/athletes/?m='.$message));
	}

	public function attendance(){
		$row = $this->checkAccountNotNull();
		$this->checkAccountUpdated($row);
		$where = ['e.coach_id'=>$this->nativesession->get('id')];
		$data['allEvents'] = $this->Model->getAllEvents($where);
		$this->heading($row);
		$this->load->view('attendance',$data);
		$this->load->view('footer');
	}

	// public function athletes(){
	// 	$row1 = $this->checkAccountNotNull();
	// 	$where = ['a.usertype'=>3,'i.sports'=>$row1->sports];	
	// 	$array_results = array();
	// 	$allAthletes = $this->Model->getAthletes($where);
	// 	foreach($allAthletes as $row){
	// 		$where2 = ['coach_id' => $row1->account_id, 'date <' => date('Y-m-d') ];
	// 		$events = $this->Model->getEvents($where2);
	// 		$no_absent = 0;
	// 		foreach($events as $row2){
	// 			$where3 = [
	// 				'att_event_id' => $row2->id,
	// 				'att_account_id '=> $row->account_id
	// 			];
	// 			$result = $this->Model->getAttendancesByEventId($where3);
	// 			if($result == null){
	// 				$no_absent++;
	// 			}
	// 		}
	// 		array_push($array_results,[
	// 			'row_info' => $row,
	// 			'number_absences' => $no_absent
	// 		]);
	// 	}
	// 	$data['allResult'] = $array_results;
	// 	$data['sport'] = $row1->sports;
	// 	$this->heading($row1);
	// 	$this->load->view('athletes_status',$data);
	// 	$this->load->view('footer');
	// }

	public function checkAttendance(){
		$row = $this->checkAccountNotNull();
		$this->checkAccountUpdated($row);
		$where = ['att_event_id'=>$this->uri->segment(3)];
		$attendances = $this->Model->getAttendancesByEventId($where);
		$array_athletes_id = array();
		$array_attendance = array();
		$array_remarks = array();
		$array_status = array();
		if($attendances != null){
			foreach($attendances as $row1){
				array_push($array_athletes_id, $row1->att_account_id);
				array_push($array_attendance, $row1->time_present);
				array_push($array_remarks, $row1->remarks);
				array_push($array_status, $row1->status);
			}
		}
		$where1 = [
			'i.sports'=>$row->sports,
			'a.usertype'=>3
		];
		$data['AllAthletes'] = $this->Model->getAthletes($where1);

		$where2 = ['id'=>$this->uri->segment(3)];
		$data['eventRow'] = $this->Model->getEventById($where2);
		$data['arrayAccountId'] = $array_athletes_id;
		$data['arrayTimePresent'] = $array_attendance;
		$data['arrayRemarks'] = $array_remarks;
		$data['arrayStatus'] = $array_status;
		$this->heading($row);
		$this->load->view('checkAttendance',$data);
		$this->load->view('footer');
	}

	public function present_athletes(){

		$this->checkAccountNotNull();
		$event_id = $this->input->post('hidden_event_id');
		$status = $this->uri->segment(4);
		if($this->input->post('Yes')!= false){
			$status = 'Present';
			$message =  base64_encode("success~Athlete is present.");
		} else {
			$status = 'Absent';
			$message =  base64_encode("errorrr~Athlete is absent.");
		}
		$data = ['att_event_id'=>$event_id,'att_account_id'=>$this->uri->segment(3),'status'=>$status];
		$this->Model->insertData('attendances',$data);

		redirect(base_url('coaches/checkAttendance/'.$event_id.'?m='.$message));
	}

	public function add_remarks(){
		$this->checkAccountNotNull();
		$event_id = $this->uri->segment(4);
		$account_id = $this->uri->segment(3);
		$remarks = $this->input->post('remarks');
		$where = ['att_event_id'=>$event_id,'att_account_id'=>$account_id];
		$data = ['remarks'=>$remarks];
		$this->Model->update('attendances',$data,$where);

		$message =  base64_encode("success~Remarks successfully added.");
		redirect(base_url('coaches/checkAttendance/'.$event_id.'?m='.$message));
	}

	public function edit_status(){
		$this->checkAccountNotNull();
		$event_id = $this->uri->segment(4);
		$account_id = $this->uri->segment(3);
		$remarks = $this->input->post('remarks');
		$where = ['att_event_id'=>$event_id,'att_account_id'=>$account_id];
		if($this->input->post('Yes')!= false){
			$data = ['status'=>"Present"];
		} else {
			$data = ['status'=>"Absent"];
		}
		
		$this->Model->update('attendances',$data,$where);

		$message =  base64_encode("success~Status successfully changed.");
		redirect(base_url('coaches/checkAttendance/'.$event_id.'?m='.$message));
	}

	public function post(){
		$row = $this->checkAccountNotNull();
		$this->checkAccountUpdated($row);
		$where = "p.sport_team = '$row->sports' OR p.sport_team = 'admin'";
		$data['allDocumentation'] = $this->Model->getDocumentation($where);
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
				"sport_team" => $sports,
				"post_account_id" => $post_account_id
			];
			$check = $this->Model->insertData('posts',$data);
			if($check != NULL){
	    	$message = base64_encode('success~New documentation successfully added.');
			} else {
				$message = base64_encode("errorrr~There's an error in saving the data. Please contact the Developer.");
			}	
	   	redirect(base_url('coaches/post/?m='.$message));
	}

	public function delete_post(){
		$this->checkAccountNotNull();
		$where = ['id'=>$this->uri->segment(3)];
		$this->Model->deleteData('posts',$where);
		$message =  base64_encode("errorrr~Documentation successfully deleted.");
		redirect(base_url('coaches/post/?m='.$message));
	}

	public function changeAccount(){
		$row = $this->checkAccountNotNull();
		$data['row'] = $row;
		$this->heading($row);
		$this->load->view('changeAccount',$data);
		$this->load->view('footer');
	}

	public function update_user_account(){
		$this->checkAccountNotNull();
		$id = $this->nativesession->get('id');
		$username = $this->input->post('username');
		$password = base64_encode(md5($this->input->post('password')));
		$data = ['username'=>$username,'password'=>$password,'updated'=>1];
		$where = ['id'=>$id];
		$this->Model->update('accounts',$data,$where);
		$message = base64_encode("success~Your username and password successfully updated.");
		redirect(base_url('coaches/?m='.$message));
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
			"lastname" => $lname
		];
		// var_dump($data);exit;
		$this->Model->update('information',$data,$where);

		$message =  base64_encode("success~Admin information successfully updated.");
		redirect(base_url('coaches/profile/?m='.$message));
	}

	function heading($row){
		$data['hresult'] = $row;
		$this->load->view('head.php');
		$this->load->view('header.php',$data);
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

			if($rows->usertype != 2){
				$message = base64_encode("errorrr~Restricted page. Your account is not coach type.");
				redirect(base_url('?m='.$message));
			} else {
				return $rows;
			}
			
		}
	}

	function checkAccountUpdated($row){
		if($row->updated == 0){
			$message = base64_encode("errorrr~Before continuing, update your account.");
			redirect(base_url('coaches/changeAccount/?m='.$message));
		}
	}
	
}
