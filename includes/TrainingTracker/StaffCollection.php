<?php

namespace TrainingTracker;

class StaffCollection extends \PSU\Collection {

	public static $child = '\TrainingTracker\Staff';

	public function __construct(){
	}

	public function get(){
		
		$client = \PSU::api('backend'); //load api
		$users = $client->get('support/users'); //get all the people that work at the help desk
		return $users;
		
	}//end get


	public function mentees_filter($it = null){
		if ( ! $it ){
			$it = $this->getIterator();
		}//end if

		return new Staff_MenteeFilterIterator( $it );

	}//end mentees_filter



	public function staff_filter($it = null){
		if ( ! $it ){
			$it = $this->getIterator();
		}//end if

		return new Staff_FilterIterator( $it );

	}//end staff_filter
	

	public function mentees(){
		$users = new StaffCollection();
		$ct = 0;

		foreach ( $users->mentees_filter() as $i ){
			$mentees_array[$ct]['username'] = $i->username;
			$mentees_array[$ct]['privileges'] = $i->privileges;
			$mentees_array[$ct]['wpid'] = $i->wpid;
			$mentees_array[$ct]['name'] = $i->name;
			$ct++;
		}//end foreach

		return $mentees_array;

	}//end mentees_filter


	//populate meta, used to populate the wpid and current level (trainee, consultant, etc..)
	public function populate_meta(){
		
		$users = new StaffCollection();
		$ct=0;
		foreach( $users->staff_filter() as $i){
			$staff_array[$ct]['username'] = $i->username;
			$staff_array[$ct]['privileges'] = $i->privileges;
			$staff_array[$ct]['wpid'] = $i->wpid;
			$staff_array[$ct]['name'] = $i->name;
			$ct++;
		}
		
		//\PSU::db('hr')->debug=true;

		foreach ($staff_array as $i){
		//check to see if they exist, if not add them to training_tracker_checklist_meta database table


		  //making my own auto increment field
		  $row = \PSU::db('hr')->GetOne("SELECT MAX(id) FROM training_tracker_checklist_meta");
		  $row +=1;
			$id = $row;		

			$result = \PSU::db('hr')->GetOne("SELECT * from training_tracker_checklist_meta where wpid=?",array($i['wpid']));
			
			if (isset($result[1])){
				//if thir wpid exists update their current level
				$result2 = \PSU::db('hr')->Execute("UPDATE training_tracker_checklist_meta SET current_level=? where wpid=?",array($i['privileges'], $i['wpid']));
			}else{
				//if they don't exist add them.
				$result3 = \PSU::db('hr')->Execute("INSERT INTO training_tracker_checklist_meta (wpid,current_level,id) VALUES (?,?,?)",array($i['wpid'],$i['privileges'],$id));
			}
			
		}
		return true;	
	}//end populate meta

	public function mentors(){
		$users = new StaffCollection();
		$ct = 0;

		foreach ( $users->mentors_filter() as $i ){
			$mentors_array[$ct]['username'] = $i->username;
			$mentors_array[$ct]['privileges'] = $i->privileges;
			$mentors_array[$ct]['wpid'] = $i->wpid;
			$mentors_array[$ct]['name'] = $i->name;
			$ct++;
		}//end foreach

		return $mentors_array;

	}//end mentees


	public function staff(){ //everybody minus jr. shift supervisors.
		$users = new StaffCollection();
		$ct = 0;

		foreach ( $users->staff_filter() as $i ){
			$mentors_array[$ct]['username'] = $i->username;
			$mentors_array[$ct]['privileges'] = $i->privileges;
			$mentors_array[$ct]['wpid'] = $i->wpid;
			$mentors_array[$ct]['name'] = $i->name;
			$ct++;
		}//end foreach

		return $mentors_array;

	}//end staff

	public function mentors_filter($it = null){
		if ( ! $it ){
			$it = $this->getIterator();
		}//end if

		return new Staff_MentorFilterIterator( $it );

	}//end mentors_filter


}//end function


class Staff_FilterIterator extends \PSU_FilterIterator {
	public function accept() {
		$staff = $this->current();

		return 'trainee' == $staff->privileges || 'sta' == $staff->privileges || 'shift_leader' == $staff->privileges;
	}//end accept
}//end 


class Staff_MenteeFilterIterator extends \PSU_FilterIterator {
	public function accept() {
		$mentee = $this->current();

		return 'trainee' == $mentee->privileges || 'sta' == $mentee->privileges;
	}//end accept
}//end 


class Staff_MentorFilterIterator extends \PSU_FilterIterator {
	public function accept() {
		$mentor = $this->current();

		return 'shift_leader' == $mentor->privileges || 'supervisor' == $mentor->privileges;
	}//end accept
}//end class
