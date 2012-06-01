<?php

require_once 'autoload.php';
PSU::session_start(); // force ssl + start a session

$GLOBALS['BASE_URL'] = '/webapp/training-tracker';
$GLOBALS['BASE_DIR'] = __DIR__;

$GLOBALS['TITLE'] = 'Training Tracker';
$GLOBALS['TEMPLATES'] = $GLOBALS['BASE_DIR'] . '/templates';

includes_psu_register( 'TrainingTracker', $GLOBALS['BASE_DIR'] . '/includes' );

require_once $GLOBALS['BASE_DIR'] . '/API.php';
require_once 'klein/klein.php';

require_once $GLOBALS['BASE_DIR'] . '/includes/TrainingTrackerAPI.class.php';

if( file_exists( $GLOBALS['BASE_DIR'] . '/debug.php' ) ) {
	include $GLOBALS['BASE_DIR'] . '/debug.php';
}

include 'includes/functions.php';

IDMObject::authN();

//if( ! IDMObject::authZ('permission', 'mis') ) {
//	die('You do not have access to this application.');
//}
 

/**
 * Routing provided by klein.php (https://github.com/chriso/klein.php)
 * Make some objects available elsewhere.
 */
respond( function( $request, $response, $app ) {
	// initialize the template
	$app->tpl = new PSUTemplate;

	// get the logged in user
	$app->user = PSUPerson::get( $_SESSION['wp_id'] ); 

	// assign user to template
	$app->tpl->assign( 'user', $app->user );
});

//Catch all
respond(function( $request, $response, $app ) {

	$wpid = $_SESSION['wp_id'];

	$staff_collection = new TrainingTracker\StaffCollection();
	$staff_collection->load(); 

	

	$valid_users = $staff_collection->valid_users();
	$admin = $staff_collection->admins();
	$mentor = $staff_collection->mentors();

	$is_valid = false;
	$is_mentor = false;
	$is_admin = false;

	foreach ($admin as $teacher){
		if ($wpid == $teacher['wpid']){
			$is_admin = true;
			$is_mentor = true;
		}
	}
	foreach ($mentor as $teacher){
		if ($wpid == $teacher['wpid']){
			$is_mentor = true;
		}
	}


	foreach ($valid_users as $user){
		if ($wpid == $user['wpid']){
			$is_valid = true;
		}
	}	

	if (!$is_valid){
		die("You do not have access to this application.");
	}
	$app->tpl->assign('wpid', $wpid);
	$app->tpl->assign('is_admin', $is_admin);
	$app->tpl->assign('is_mentor', $is_mentor);
});

// the person select page
respond( '/?', function( $request, $response, $app ) {
	$wpid = $_SESSION['wp_id'];
	$current_user['name'] = PSUPerson::get($wpid)->formatname("f l");
	$current_user['wpid'] = $wpid;

	$staff_collection = new TrainingTracker\StaffCollection();
	$staff_collection->load(); 

	$staff = $staff_collection->staff();
	foreach ($staff as $person){
		$pidm = PSU::get('idmobject')->getidentifier($person['wpid'], 'wp_id', 'pidm');
		$result = PSU::db('hr')->GetOne("SELECT PIDM FROM person_checklists WHERE PIDM=? AND closed=?", array($pidm, 0));
		if (!$result){
			$type = PSU::db('hr')->GetOne("SELECT type FROM checklist WHERE slug=?",array($person['privileges']));
			$inserted = PSU::db('hr')->Execute("INSERT INTO person_checklists (type, PIDM, closed) VALUES (?, ?, ?)", array($type, $pidm, 0));
			PSU::dbug($pidm);
		}
	}

	if (!$is_admin){
		foreach ($staff as $person){
			if ($person['wpid'] == $current_user['wpid']){
				$current_user['percent'] = $person['percent'];		
			}
		}
	}

	$app->tpl->assign('current_user', $current_user);
	$app->tpl->assign('staff', $staff);
	$app->tpl->display('index.tpl');
});


//teams creation page
respond( '/teams', function( $request, $response, $app ) {

	$wpid = $_SESSION['wp_id'];

	$staff_collection = new TrainingTracker\StaffCollection();
	$staff_collection->load(); 

	$admin = $staff_collection->admins();
	$is_admin = false;
	foreach ($admin as $teacher){
		if ($wpid == $teacher['wpid']){
			$is_admin = true;
			$is_mentor = true;
		}
	}

	if (!$is_admin){
		die("You do not have access to this page.");
	}
	//getting all the mentors and mentees at the help desk.
	$staff_collection = new TrainingTracker\StaffCollection();
	$staff_collection->load(); 
	$mentor = $staff_collection->mentors();
	$mentee = $staff_collection->mentees();

	//check to see if the mentors have a team. Are they mentoring anybody at the moment.
	foreach ($mentor as &$teacher){ 
		$team = PSU::db('hr')->GetRow("SELECT * FROM teams WHERE mentor = ?", array($wpid));//do this every where

		if ($team){//if team
			$teacher['team'] = true;
		}
		else{
			$student['team'] = false;
		}
	}
	
	//check to see if the mentee has a team
	foreach ($mentee as &$student){

		$wpid = $student['wpid'];

		//if they have a team $team will be true, else it will be false
		$team = PSU::db('hr')->GetAll("SELECT * FROM teams WHERE mentee = ?", array($wpid));

		//if they have a team set their mentor.
		if ($team){
			$student['team'] = true;
			$student['team_leader'] = $team[0]['mentor'];
		}
		else{
			//if they don't have a team set the team leader to mentee, the default channel for no team.
			$student['team_leader'] = 'mentee';
			$student['team'] = false;
		}
	}
	$count = 0;
	$mentor_string = "";

	//prepairing a string to use for javascript #mentor0, #mentor1... etc.
	for ($i = 0; $i < sizeOf($mentor); $i++){ 
		$mentor_string .= ('#mentor-' . $i . ", ");
		$count = $i;
	}//end for
	$count = 0;
	$mentor_string_li = "";

	//same string as before but with li at the end, #mentor0 li, #mentor1 li... etc.
	for ($i = 0; $i < sizeOf($mentor); $i++){ 
		$mentor_string_li .= ('#mentor-' . $i . " li, ");
		$count = $i;
	}//end for

	//add final case to the string
	$mentor_string_li .= "#mentor" . ($count+1 . " li"); 
	$mentor_string .= "#mentor" . ($count+1);
	
	$app->tpl->assign('mentor_string_li', $mentor_string_li); //assigning php variables to smarty
	$app->tpl->assign('mentor_string', $mentor_string);
	$app->tpl->assign('checklist_item_categories', $result);
	$app->tpl->assign('mentee', $mentee);
	$app->tpl->assign('mentor', $mentor);
	$app->tpl->display('teams.tpl'); //go go gadget show page
});
  
// checklist page
respond( '/checklist/[:wpid]?', function( $request, $responce, $app ) {

	$active_user['wpid'] = $_SESSION['wp_id'];
	$active_user['name'] = PSUPerson::get($active_user['wpid'])->formatname("f l");
	$wpid = $request->wpid;
	$person = PSUPerson::get($wpid);
	$current_user['username'] = $person->username;
	$current_user['name'] = $person->formatname("f l");
	$current_user['pidm'] = $person->pidm;
	$current_user['wpid'] = $wpid;
	$current_user_level = PSU::db('calllog')->GetOne("SELECT user_privileges FROM call_log_employee WHERE user_name=?",array($current_user['username']));
	$active_user_level = PSU::db('calllog')->GetOne("SELECT user_privileges FROM call_log_employee WHERE user_name=?",array($_SESSION['username']));
	PSU::dbug($current_user);
	
	$checklist_id = PSU::db('hr')->GetOne("SELECT id FROM person_checklists WHERE pidm=? AND closed=?", array($pidm, 0));

die();
	//get the data for which check boxes are checked
	$checklist_checked = PSU::db('hr')->GetAll("SELECT * FROM person_checklist_items WHERE checklist_id=? AND responce=?",array($checklist_id, "complete"));

	$last_modified = PSU::db('hr')->GetAll("SELECT * FROM training_tracker_checklist_meta WHERE wpid = ?", array($current_user['wpid']));

	//the title is the title name in the box.
	if (strlen($last_modified[0]['modified_by'])>2){
		$title = $current_user['name'] . " - Last modified by " . PSUPerson::get($last_modified[0]['modified_by'])->formatname("f l") . " on " . $last_modified[0]['time'];
	}
	else{
		$title = $current_user['name']; 
	}

	//getting comments from database
	$comments = PSU::db('hr')->GetOne("SELECT comments FROM training_tracker_checklist_meta WHERE wpid = ?", array($wpid));
	if (!$comments){ //if there are no saved comments this is set default
		$comments = "Comments go here";
	}
	
	$staff_collection = new TrainingTracker\StaffCollection(); //get the people that work at the helpdesk
	$staff_collection->load(); 
	$mentor = $staff_collection->mentors();//select all the mentors
	$mentee = $staff_collection->mentees();//select all the mentees

	//populating some variables to generate the checklist.
	$checklist_items = get_checklist_items($current_user_level); //todo make this grab items based off of checklist id, 
																															 //fix these functions to work with new db structure
	$checklist_item_sub_cat = get_checklist_sub_cat($current_user_level);
	$checklist_item_cat = get_checklist_item_categories($current_user_level);

	$stats = get_stats($current_user['wpid']);
	$progress = $stats['progress'];

	$app->tpl->assign('checklist_id', $checklist_id);	
	$app->tpl->assign('progress', $progress);	
	$app->tpl->assign('title', $title);	
	$app->tpl->assign('checked', $checklist_checked);
	$app->tpl->assign('comments', $comments);	
	$app->tpl->assign('active_user', $active_user);	
	$app->tpl->assign('current_user', $current_user);	
	$app->tpl->assign('current_user_level', $current_user_level);	
	$app->tpl->assign('active_user_level', $active_user_level);	
	$app->tpl->assign('mentee', $mentee);	
	$app->tpl->assign('checklist_items', $checklist_items);
	$app->tpl->assign('checklist_item_sub_cat', $checklist_item_sub_cat);
	$app->tpl->assign('checklist_item_cat', $checklist_item_cat);
	$app->tpl->display('checklist.tpl');
});

//statistics page
respond( '/statistics/[:wpid]?', function( $request, $responce, $app ) {

	$active_user['wpid'] = $_SESSION['wp_id'];
	$active_user['name'] = PSUPerson::get($active_user['wpid'])->formatname("f l");

	$wpid = $request->wpid;

	$current_user['name'] = PSUPerson::get($wpid)->formatname("f l");
	$current_user['wpid'] = $wpid;

	//get the data for which check boxes are checked
	$checklist_checked = PSU::db('hr')->GetOne("SELECT checkboxes FROM training_tracker_checklist_meta WHERE wpid = ?",array($wpid));

	//active user being the person looking at this
	//current user is the person the active user is looking at
	$current_user_level = PSU::db('hr')->GetOne("SELECT current_level FROM training_tracker_checklist_meta WHERE wpid = ?", array($wpid));
	$active_user_level = PSU::db('hr')->GetOne("SELECT current_level FROM training_tracker_checklist_meta WHERE wpid = ?", array($active_user['wpid']));

	$last_modified = PSU::db('hr')->GetAll("SELECT * FROM training_tracker_checklist_meta WHERE wpid = ?", array($current_user['wpid']));

	//check to see if somebody has modified the current user
	//the title is the title name in the box.
	if (strlen($last_modified[0]['modified_by'])>2){
		$title = $current_user['name'] . " - Last modified by " . PSUPerson::get($last_modified[0]['modified_by'])->formatname("f l") . " on " . $last_modified[0]['time'];
	}
	else{
		$title = $current_user['name']; 
	}

	//getting comments from database
	$comments = PSU::db('hr')->GetOne("SELECT comments FROM training_tracker_checklist_meta WHERE wpid = ?", array($wpid));
	if (!$comments){ //if there are no saved comments this is set default
		$comments = "Comments go here";
	}
	
	$staff_collection = new TrainingTracker\StaffCollection(); //get the people that work at the helpdesk
	$staff_collection->load(); 
	$mentor = $staff_collection->mentors();//select all the mentors
	$mentee = $staff_collection->mentees();//select all the mentees

	//populating some variables to generate the checklist.
	$checklist_items = get_checklist_items($current_user_level);
	$checklist_item_sub_cat = get_checklist_sub_cat($current_user_level);
	$checklist_item_cat = get_checklist_item_categories($current_user_level);

	$stats = get_stats($wpid);
	$progress = $stats['progress'];
	unset($stats['progress']);

	$total = 0;
	$ct = 0;
	foreach ($stats as $statistic){
		$ct++;
		$total += ($statistic);
	}
	$progress = round(($total/$ct), 2);
	foreach ($checklist_item_sub_cat as &$sub_cat){
		$letter = $sub_cat['sub_category'];
		$sub_cat['stat'] = $stats["$letter"];
	}

	$app->tpl->assign('progress', $progress);	
	$app->tpl->assign('title', $title);	
	$app->tpl->assign('checked', $checklist_checked);
	$app->tpl->assign('comments', $comments);	
	$app->tpl->assign('active_user', $active_user);	
	$app->tpl->assign('current_user', $current_user);	
	$app->tpl->assign('current_user_level', $current_user_level);	
	$app->tpl->assign('active_user_level', $active_user_level);	
	$app->tpl->assign('mentee', $mentee);	
	$app->tpl->assign('checklist_items', $checklist_items);
	$app->tpl->assign('checklist_item_sub_cat', $checklist_item_sub_cat);
	$app->tpl->assign('checklist_item_cat', $checklist_item_cat);
	$app->tpl->assign('current_level', $current_level);
	$app->tpl->assign('stats', $stats);
	$app->tpl->display('statistics.tpl');
});
//view teams
respond( '/myteam/[:wpid]?', function( $request, $responce, $app ) {
	$mentor_wpid = $request->wpid;
	$teams_data = PSU::db('hr')->GetAll("SELECT * FROM teams WHERE mentor=?", array($mentor_wpid));
	
	$teams = array();	

	foreach($teams_data as $team){
		$stats = get_stats($team['mentee']);
		$progress = $stats['progress'];
		$mentee = $team['mentee'];
		$teams["$mentee"]['percent'] = $progress;
		$teams["$mentee"]['wpid'] = $team['mentee'];
		$teams["$mentee"]['name'] = PSUPerson::get($team['mentee'])->formatname("f l");
	}
	$app->tpl->assign('teams', $teams);
	$app->tpl->display('myteam.tpl');
});
respond( '/viewteams', function( $request, $responce, $app ) {

	//get all the teams from the database
	$teams = PSU::db('hr')->GetAll("SELECT * FROM teams");

	//get all the mentors from the database
	$mentors = PSU::db('hr')->GetAll("SELECT DISTINCT mentor FROM teams");
	$i=0;

	//insted of using wpid, like it is stored in the database. PSUPerson is called to get their name
	//formatted First name Last name, and it is stored in an associative array. Each mentor's name
	//is a spot in the array.
	foreach ($mentors as $mentor){
		$teamarray[PSUPerson::get($mentor['mentor'])->formatname("f l")]['mentees'] = array();
		$teamarray[PSUPerson::get($mentor['mentor'])->formatname("f l")]['mentor'] = PSUPerson::get($mentor['mentor'])->formatname("f l");
		$i++;
	}

	//go through each team and add the mentee to the mentor's team, with their formatted name.
	foreach ($teams as $team){
		array_push($teamarray[PSUPerson::get($team["mentor"])->formatname("f l")]['mentees'], PSUPerson::get($team["mentee"])->formatname("f l"));
	}

	$app->tpl->assign('team_array', $teamarray);
	$app->tpl->display('viewteams.tpl');
});

//the axax post part for teams
respond( '/teams_post', function( $request, $responce, $app ) {

	//get the data to variables from the posted data
	$post_data = $_POST['data'][0]; //mentee's wpid
	$mentor = $_POST['data'][1]; //mentors wpid

	//PSU::db('hr')->debug=true;
	$result = PSU::db('hr')->GetAll("SELECT * FROM teams WHERE mentee = '$post_data'");
	//try to select a team with that mentee

	if (isset($result[0])){
		$result1 = PSU::db('hr')->Execute("UPDATE teams SET mentor='$mentor' WHERE mentee = '$post_data'");
		// if team exists with that mentee replace mentor	
	}
	else{
		$result2 = PSU::db('hr')->Execute("INSERT INTO teams (mentor, mentee) VALUES ('$mentor', '$post_data')");
		//if the mentee isn't in a team make them a team
	}
	if ($mentor == NULL){
		//if you move the mentee back to the mentee category in the team builder, it removes their database entry.
		$result3 = PSU::db('hr')->Execute("DELETE FROM teams WHERE mentee = '$post_data'");
	}
});

//the axax post part for checklist comments
respond( '/checklist_post_comments', function( $request, $responce, $app ) {

	//PSU::db('hr')->debug=true;

	$comments = $_POST['data'][0];
	$wpid = $_POST['data'][1];
	
	//making my own auto increment field, select greatest id # then incrementing it by 1.
	$row = PSU::db('hr')->GetOne("SELECT MAX(id) FROM training_tracker_checklist_meta");
	$row +=1;
	$id = $row;

	//checking to see if the person already exists in the database
	$result = PSU::db('hr')->GetAll("SELECT * FROM training_tracker_checklist_meta WHERE wpid = ?", array($wpid));

	if (isset($result[0])){
		$result1 = PSU::db('hr')->Execute("UPDATE training_tracker_checklist_meta SET comments= ? WHERE wpid = ?",array("$comments", $wpid));
		// if person has a db entry already	
	}
	else{
		$result2 = PSU::db('hr')->Execute("INSERT INTO training_tracker_checklist_meta (comments, wpid, id) VALUES (?, ?, ?)", array ($comments,$wpid,$id));
		//if they don't, make them one
	}

});

//the axax post part for checklist check boxes 
respond( '/checklist_post_chkbox', function( $request, $responce, $app ) {
//checked is a string containing the checked boxes id's seperated by a ","

	$checked_id = $_POST['data'][0];
	$wpid = $_POST['data'][1];
	$responce = $_POST['data'][2];
	//todo fix post[2] to be responce (complete,n/a)
	$pidm = PSU::get('idmobject')->getidentifier( $wpid, "wp_id", "pidm" );
	$modified_by = $_SESSION['pidm'];

/*	//making my own auto increment field
	$row = PSU::db('hr')->GetOne("SELECT MAX(id) FROM training_tracker_checklist_meta");
	$row +=1;
	$id = $row;
 */

	$checklist_id = PSU::db('hr')->GetOne("SELECT item_id FROM person_checklists WHERE pidm=?", array($pidm));

	//check to see if checkbox exists
	$does_exist = PSU::db('hr')->GetOne("SELECT item_id from person_checklist_items WHERE item_id=?", array($checked_id));
	
	if (!$does_exist){
		$inserted = PSU::db('hr')->Execute("INSERT INTO person_checklist_items (item_id, checklist_id, responce, updated_by) VALUES (?,?,?,?)",array($checked_id, $checklist_id, $responce, $_SESSION['pidm']));
	}
	else{
		$update = PSU::db('hr')->Execute("UPDATE person_checklist_items SET responce = ? WHERE pidm = ?", array($responce, $pidm));
	}
	//update the person who modified this page
	$updated_by = PSU::db('hr')->Execute("UPDATE person_checklist_items SET updated_by = ? WHERE pidm = ?", array($modified_by, $pidm));


});
//the ajax for the done button on the checklist page
respond( '/checklist_post_done', function( $request, $responce, $app ) {
	
	$comments = $_POST['data'][0];
	$checked = $_POST['data'][1];
	$current_user = $_POST['data'][2];
	$active_user = $_POST['data'][3];
	
	//update who modified this last
	$result = PSU::db('hr')->Execute("UPDATE training_tracker_checklist_meta SET modified_by = ? WHERE wpid = ?", array($active_user, $current_user));

	//update the comments field
	$result2 = PSU::db('hr')->Execute("UPDATE training_tracker_checklist_meta SET comments = ? WHERE wpid = ?", array($comments, $current_user));

	//update the modified check boxes
	$result3 = PSU::db('hr')->Execute("UPDATE training_tracker_checklist_meta SET checkboxes = ? WHERE wpid = ?", array($checked, $current_user));

});
//the ajax fo the confirm button
respond( '/checklist_post_confirm', function( $request, $responce, $app ) {
	$current_user_wpid = $_POST['data'][0];
	$active_user_wpid = $_POST['data'][1];
	$current_user_level = $_POST['data'][2];
	$current_user_name = PSUPerson::get("$current_user_wpid")->formatname("f l");
	$active_user_name = PSUPerson::get("$active_user_wpid")->formatname("f l");
	$person = PSUPerson::get($current_user_wpid);
	$usnh_id = $person->usnh_id;

	if ($current_user_level == 'trainee'){
		$current_pay = 7.25;
		$current_user_level = "Information Desk Trainee";
	}
	else if ($current_user_level == 'sta'){
		$current_pay = 7.50;
		$current_user_level = "Information Desk Consultant";
	}
	else{
		$current_pay = 7.75;
		$current_user_level = "Senior Information Desk Consultant";
	}
	$future_pay = $current_pay + 0.25;

	$message = "$current_user_name has completed their current level of $current_user_level\nand would enjoy a pay raise from \$$current_pay to \$$future_pay.\n\nUSNH ID: $usnh_id \n\nSent by\n\t$active_user_name";

	PSU::mail("tlferm@plymouth.edu","Training Tracker - " . PSUPerson::get("$current_user_wpid")->formatname("f l") . " pay raise request","$message");
});
$app_routes = array();

foreach( $app_routes as $base ) {
	with( "/{$base}", $GLOBALS['BASE_DIR'] . "/routes/{$base}.php" );
}//end foreach

dispatch( $_SERVER['PATH_INFO'] );
