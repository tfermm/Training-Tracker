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

//Catch all
respond( function( $request, $response, $app ) {
	
	$wpid = $_SESSION['wp_id'];

	$staff_collection = new TrainingTracker\StaffCollection();
	$staff_collection->load(); 
	//Make me a LLC Manager in callog
	//$result = PSU::db('calllog')->Execute("UPDATE call_log_employee SET user_privileges = 'manager' WHERE user_name='tlferm'");
	$teams_data = PSU::db('hr')->GetAll("SELECT * FROM teams WHERE mentor=?", array($wpid));
	$has_team = false;
	if (isset($teams_data[0])){
		$has_team = true;
	}

	$valid_users = $staff_collection->valid_users();
	$admin = $staff_collection->admins();
	$mentor = $staff_collection->mentors();

	$is_valid = false;
	$is_mentor = false;
	$is_admin = false;

	foreach ($admin as $teacher){
		if ($wpid == $teacher->wpid){
			$is_admin = true;
			$is_mentor = true;
		}
	}

	foreach ($mentor as $teacher){
		if ($wpid == $teacher->wpid){
			$is_mentor = true;
		}
	}

	foreach ($valid_users as $user){
		if ($wpid == $user->wpid){
			$is_valid = true;
		}
	}	

	if (!$is_valid){
		die("You do not have access to this page.");
	}

	$app->is_admin = $is_admin;
	// initialize the template
	$app->tpl = new PSUTemplate;
	// get the logged in user
	$app->user = PSUPerson::get( $_SESSION['wp_id'] ); 
	// assign user to template
	$app->tpl->assign( 'user', $app->user );
	$app->tpl->assign('has_team', $has_team);
	$app->tpl->assign('wpid', $wpid);
	$app->tpl->assign('is_admin', $is_admin);
	$app->tpl->assign('is_mentor', $is_mentor);
});

// the person select page
respond( '/?', function( $request, $response, $app ) {

	$wpid = $_SESSION['wp_id'];

	$current_user_parameters["wpid"] = $wpid;
	$current_user_parameters["name"] = PSUPerson::get($wpid)->formatname("f l");
	$current_user = new TrainingTracker\Staff($current_user_parameters);
	$staff_collection = new TrainingTracker\StaffCollection();
	$staff_collection->load(); 

	$staff = $staff_collection->staff();
	foreach ($staff as $person){
		$pidm = $person->person()->pidm;
		$result = PSU::db('hr')->GetOne("SELECT PIDM FROM person_checklists WHERE PIDM=? AND closed=?", array($pidm, 0));
	
		if (!$result){
			$type = PSU::db('hr')->GetOne("SELECT type FROM checklist WHERE slug=?",array($person->privileges));
			$inserted = PSU::db('hr')->Execute("INSERT INTO person_checklists (type, pidm, closed) VALUES (?, ?, ?)", array($type, $pidm, 0));
		}
	}

	if (!$is_admin){
		foreach ($staff as $person){
			if ($person->wpid == $current_user->wpid){
				$current_user->progress = $person->progress;
			}
		}
	}

	$app->tpl->assign('current_user', $current_user);
	$app->tpl->assign('staff', $staff);
	$app->tpl->display('index.tpl');
});


//teams creation page
respond( '/teams', function( $request, $response, $app ) {

	$wpid = $app->user->wpid;

	$staff_collection = new TrainingTracker\StaffCollection();
	$staff_collection->load();

	if (!$app->is_admin){
		die("You do not have access to this page.");
	}
	//getting all the mentors and mentees at the help desk.
	$mentors = $staff_collection->mentors();
	$mentees = $staff_collection->mentees();
	$teams['unassigned'] = array();
	foreach ($mentees as $mentee){
		$mentee->team = $mentee->team();
		if (isset($mentee->team)){
			$mentor = $mentee->team['mentor'];
			$mentee_team["wpid"] = $mentee->wpid;
			$mentee_team["team_leader_name"] = $mentee->team['mentor_name'];
			$mentee_team["team_leader_wpid"] = $mentee->team['mentor'];
			$mentee_team["name"] = $mentee->name;
			$teams["$mentor"]["$mentee->wpid"] = $mentee_team; 
		}
		else{
			$mentee_team["wpid"] = $mentee->wpid;
			$mentee_team["team_leader_name"] = $mentee->team['mentor_name'];
			$mentee_team["team_leader_wpid"] = $mentee->team['mentor'];
			$mentee_team["name"] = $mentee->name;
			//$mentee_team[""] = $mentee->;
			$teams["unassigned"]["$mentee->wpid"] = $mentee_team;
		}
	}
	$teams = json_encode($teams);

//	die;
	/*
	//check to see if the mentors have a team. Are they mentoring anybody at the moment.
	foreach ($mentors as $mentor){ 
		$mentor->team = (boolean)PSU::db('hr')->GetOne("SELECT 1 FROM teams WHERE mentor = ?", array($wpid));
	}
	
	//check to see if the mentee has a team
	foreach ($mentees as $mentee){
		$wpid = $student->wpid;

		//if they have a team $team will be true, else it will be false
		$team = PSU::db('hr')->GetAll("SELECT * FROM teams WHERE mentee = ?", array($wpid));

		//if they have a team set their mentor.
		if ($team){
			$mentee->team = true;
			$mentee->team_leader = $team[0]['mentor'];
		}
		else{
			//if they don't have a team set the team leader to mentee, the default channel for no team.
			$mentee->team_leader = 'mentee';
			$mentee->team = false;
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
	 */
//	$app->tpl->assign('mentor_string_li', $mentor_string_li); //assigning php variables to smarty
//	$app->tpl->assign('mentor_string', $mentor_string);
//	$app->tpl->assign('checklist_item_categories', $result);
	$app->tpl->assign('teams', $teams);
	$app->tpl->assign('mentees', $mentees);
	$app->tpl->assign('mentors', $mentors);
	$app->tpl->display('teams.tpl'); //go go gadget show page
});
  
// checklist page
respond( '/checklist/[:wpid]?', function( $request, $responce, $app ) {

	$active_user['wpid'] = $_SESSION['wp_id'];
	$active_user['name'] = PSUPerson::get($active_user['wpid'])->formatname("f l");
	$wpid = $request->wpid;
	$current_user_parameters["wpid"] = $wpid;
	$current_user_parameters["name"] = PSUPerson::get($wpid)->formatname("f l");
	$current_user = new TrainingTracker\Staff($current_user_parameters);
	$current_user_level = PSU::db('calllog')->GetOne("SELECT user_privileges FROM call_log_employee WHERE user_name=?",array($current_user->person()->username));
	$active_user_level = PSU::db('calllog')->GetOne("SELECT user_privileges FROM call_log_employee WHERE user_name=?",array($_SESSION['username']));
	
	$checklist_id = PSU::db('hr')->GetOne("SELECT id FROM person_checklists WHERE pidm=? AND closed=?", array($current_user->person()->pidm, "0"));

	if (strlen($checklist_id) > 2){
		//get the data for which check boxes are checked
		$checklist_checked = PSU::db('hr')->GetAll("SELECT * FROM person_checklist_items WHERE checklist_id=? AND response=?",array($checklist_id, "complete"));

		//tooltip is for displaying who last modified an item and when displayed as a tooltip
		for ($i = 0; $i < sizeof($checklist_checked); $i++){
			$item_id = $checklist_checked[$i]["item_id"];
			$tooltip["$item_id"]["item_id"] = $item_id;
			$tooltip["$item_id"]["updated_by"] = PSUPerson::get($checklist_checked[$i]["updated_by"])->formatname("f l");
			$tooltip["$item_id"]["updated_time"] = $checklist_checked[$i]["activity_date"];
			$checklist_checked[$i] = $checklist_checked[$i]["item_id"];
		}		

		$last_modified = PSU::db('hr')->GetAll("SELECT max(activity_date) FROM person_checklist_items WHERE checklist_id=?", array($checklist_id));

		$modified_by = PSU::db('hr')->GetOne("SELECT updated_by FROM person_checklist_items WHERE activity_date=? AND checklist_id=?",array($last_modified[0]['max(activity_date)'], $checklist_id));
	}

	//the title is the title name in the box.
	if (strlen($modified_by)>2){
		$title = $current_user->name . " - Last modified by " . PSUPerson::get($modified_by)->formatname("f l") . " on " . $last_modified[0]['max(activity_date)'];
	}
	else{
		$title = $current_user->name; 
	}

	//getting comments from database
	$comments = PSU::db('hr')->GetOne("SELECT notes FROM person_checklist_items WHERE response=? AND checklist_id=?", array("notes", $checklist_id));
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

	//adding the tooltip data to the checklist_items
	foreach ($checklist_items as &$checklist_item){
		$item_id = $checklist_item['id'];
		$checklist_item['updated_by'] = $tooltip["$item_id"]["updated_by"];
		$checklist_item['updated_time'] = $tooltip["$item_id"]["updated_time"];
	}

	$stats = get_stats($current_user->wpid);
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
	$current_user_parameters["wpid"] = $wpid;
	$current_user_parameters["name"] = PSUPerson::get($wpid)->formatname("f l");
	$current_user = new TrainingTracker\Staff($current_user_parameters);
	$current_user_level = PSU::db('calllog')->GetOne("SELECT user_privileges FROM call_log_employee WHERE user_name=?",array($current_user->person()->username));
	$active_user_level = PSU::db('calllog')->GetOne("SELECT user_privileges FROM call_log_employee WHERE user_name=?",array($_SESSION['username']));
	
	$checklist_id = PSU::db('hr')->GetOne("SELECT id FROM person_checklists WHERE pidm=? AND closed=?", array($current_user->person()->pidm, "0"));

	if (strlen($checklist_id) > 2){
		//get the data for which check boxes are checked
		$checklist_checked = PSU::db('hr')->GetAll("SELECT * FROM person_checklist_items WHERE checklist_id=? AND response=?",array($checklist_id, "complete"));
		
		for ($i = 0; $i < sizeof($checklist_checked); $i++){
			$item_id = $checklist_checked[$i]["item_id"];
			$tooltip["$item_id"]["item_id"] = $item_id;
			$tooltip["$item_id"]["updated_by"] = PSUPerson::get($checklist_checked[$i]["updated_by"])->formatname("f l");
			$tooltip["$item_id"]["updated_time"] = $checklist_checked[$i]["activity_date"];
			$checklist_checked[$i] = $checklist_checked[$i]["item_id"];
		}

		$last_modified = PSU::db('hr')->GetAll("SELECT max(activity_date) FROM person_checklist_items WHERE checklist_id=?", array($checklist_id));

		$modified_by = PSU::db('hr')->GetOne("SELECT updated_by FROM person_checklist_items WHERE activity_date=? AND checklist_id=?",array($last_modified[0]['max(activity_date)'], $checklist_id));
	}

	//the title is the title name in the box.
	if (strlen($modified_by)>2){
		$title = $current_user->name . " - Last modified by " . PSUPerson::get($modified_by)->formatname("f l") . " on " . $last_modified[0]['max(activity_date)'];
	}
	else{
		$title = $current_user->name; 
	}

	//getting comments from database
	$comments = PSU::db('hr')->GetOne("SELECT notes FROM person_checklist_items WHERE response=? AND checklist_id=?", array("notes", $checklist_id));
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

	//adding the tooltip data to the checklist_items
	foreach ($checklist_items as &$checklist_item){
		$item_id = $checklist_item['id'];
		$checklist_item['updated_by'] = $tooltip["$item_id"]["updated_by"];
		$checklist_item['updated_time'] = $tooltip["$item_id"]["updated_time"];
	}

	$stats = $current_user->stats();
	$progress = $current_user->stats("progress");

	foreach ($checklist_item_sub_cat as &$sub_cat){
		$id = $sub_cat['id'];
		$sub_cat['stat'] = $stats["$id"];
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

//admin page
respond( '/admin', function( $request, $response, $app ) {
	
	$staff_collection = new TrainingTracker\StaffCollection();
	$staff_collection->load(); 

	$staff = $staff_collection->staff();
	foreach ($staff as $person){
		$permission	= PSU::db('calllog')->GetOne("SELECT user_privileges FROM call_log_employee WHERE user_name=?",array($person->person()->username));
		if ($permission == 'trainee'){
			$person->permission = "Trainee";
			$person->permission_slug = $permission;
		}
		else if ($permission == 'sta'){
				$person->permission = "Consultant";
				$person->permission_slug = $permission;
		}
		else if ($permission == 'shift_leader'){
				$person->permission = "Senior Consultant";
				$person->permission_slug = $permission;
		}
	}
	$app->tpl->assign('staff', $staff);
	$app->tpl->display('admin.tpl');
});
//admin promote page
respond( '/admin/promote', function( $request, $response, $app ) {

	PSU::db('hr')->debug=true;

	$permission = $_POST['data'][0];
	$wpid = $_POST['data'][1];
	$pidm = PSUPerson::get($wpid)->pidm;

	$sql = "UPDATE call_log_employee SET user_privileges=? WHERE user_name=?";
	PSU::db('calllog')->Execute($sql, array($permission, PSUPerson::get($wpid)->username));

	$type = PSU::db('hr')->GetOne("SELECT type FROM checklist WHERE slug=?",array($permission));
	$result = PSU::db('hr')->GetOne("SELECT PIDM FROM person_checklists WHERE PIDM=? AND type=?", array($pidm, $type));

	if (!$result){
		$updated = PSU::db('hr')->Execute("UPDATE person_checklists SET closed = ? WHERE closed = ? AND pidm = ?", array(1, 0, $pidm));
		$inserted = PSU::db('hr')->Execute("INSERT INTO person_checklists (type, pidm, closed) VALUES (?, ?, ?)", array($type, $pidm, 0));
	}
	else{
		$updated = PSU::db('hr')->Execute("UPDATE person_checklists SET closed = ? WHERE closed = ? AND pidm = ?", array(1, 0, $pidm));
		$updated = PSU::db('hr')->Execute("UPDATE person_checklists SET closed = ? WHERE type = ? AND pidm = ?", array(0, $type, $pidm));
	}
	/*
PSU::db('calllog')->debug=true;

	PSU::db('hr')->debug=true;
	$permission = $_POST['data'][0];
	$wpid = $_POST['data'][1];
	$pidm = PSUPerson::get($wpid)->pidm;

	$sql = "UPDATE call_log_employee SET user_privileges=? WHERE user_name=?";
	PSU::db("calllog")->Execute($sql, array($permission, PSUPerson::get($wpid)->username));

	$sql = "UPDATE person_checklists SET closed = ? WHERE pidm = ? AND closed = ?";
	PSU::db('hr')->Execute($sql, array(1, $pidm, 0));

	$sql = "INSERT person_checklists (type, pidm, closed) VALUES (?, ?, ?)";
	PSU::db('hr')->Execute($sql, array($type, $pidm, 0));

	 */
});
//demote ajax page
respond( '/admin/demote', function( $request, $response, $app ) {

	PSU::db('hr')->debug=true;

	$permission = $_POST['data'][0];
	$wpid = $_POST['data'][1];
	$pidm = PSUPerson::get($wpid)->pidm;

	$sql = "UPDATE call_log_employee SET user_privileges=? WHERE user_name=?";
	PSU::db('calllog')->Execute($sql, array($permission, PSUPerson::get($wpid)->username));

	$type = PSU::db('hr')->GetOne("SELECT type FROM checklist WHERE slug=?",array($permission));
	$result = PSU::db('hr')->GetOne("SELECT PIDM FROM person_checklists WHERE PIDM=? AND type=?", array($pidm, $type));

	if (!$result){
		$updated = PSU::db('hr')->Execute("UPDATE person_checklists SET closed = ? WHERE closed = ? AND pidm = ?", array(1, 0, $pidm));
		$inserted = PSU::db('hr')->Execute("INSERT INTO person_checklists (type, pidm, closed) VALUES (?, ?, ?)", array($type, $pidm, 0));
	}
	else{
		$updated = PSU::db('hr')->Execute("UPDATE person_checklists SET closed = ? WHERE closed = ? AND pidm = ?", array(1, 0, $pidm));
		$updated = PSU::db('hr')->Execute("UPDATE person_checklists SET closed = ? WHERE type = ? AND pidm = ?", array(0, $type, $pidm));
	}
});
//the axax post part for teams
respond( '/teams_post', function( $request, $responce, $app ) {

	//get the data to variables from the posted data
	$mentee_wpid = $_POST['data'][0];
	$mentor_wpid = $_POST['data'][1];

	PSU::db('hr')->debug=true;
	$result = PSU::db('hr')->GetAll("SELECT * FROM teams WHERE mentee = ?", array($mentee_wpid));
	//try to select a team with that mentee

	if (isset($result[0])){
		$result1 = PSU::db('hr')->Execute("UPDATE teams SET mentor=? WHERE mentee = ?", array($mentor_wpid, $mentee_wpid));
		// if team exists with that mentee replace mentor	
	}
	else{
		$result2 = PSU::db('hr')->Execute("INSERT INTO teams (mentor, mentee) VALUES ( ?, ?)", array($mentor_wpid, $mentee_wpid));
		//if the mentee isn't in a team make them a team
	}
	if ($mentor_wpid == "unassigned"){
		//if you move the mentee back to the mentee category in the team builder, it removes their database entry.
		$result3 = PSU::db('hr')->Execute("DELETE FROM teams WHERE mentee = ?", array($mentee_wpid));
	}
});

//the axax post part for checklist comments
respond( '/checklist_post_comments/[:wpid]?', function( $request, $responce, $app ) {

	$comments = $_POST['comments'];
	$wpid = $request->wpid;
	$pidm = PSUPerson::get($wpid)->pidm;
	$modified_by = $_SESSION['pidm'];
	$comments = stripslashes($comments);
	$comments = trim($comments);
	
	//PSU::dbug($comments);	

	$checklist_id = PSU::db('hr')->GetOne("SELECT id FROM person_checklists WHERE pidm=?", array($pidm));

	//checking to see if the person already exists in the database
	$result = PSU::db('hr')->GetAll("SELECT * FROM person_checklist_items WHERE checklist_id=? AND response=?", array($checklist_id, "notes"));

	if (isset($result[0])){
		$result1 = PSU::db('hr')->Execute("UPDATE person_checklist_items SET notes=? WHERE checklist_id=? AND response=?",array($comments, $checklist_id, "notes"));
		// if person has a db entry already	
	}
	else{
		$result2 = PSU::db('hr')->Execute("INSERT INTO person_checklist_items (checklist_id, item_id, response, notes, updated_by) VALUES (?, ?, ?, ?, ?)", array ($checklist_id, "007", "notes", $comments, $modified_by));
		//if they don't, make them one
	}
	$location = $GLOBALS['BASE_URL'];
	header("Location: $location");
});

//the axax post part for checklist check boxes 
respond( '/checklist_post_chkbox', function( $request, $responce, $app ) {
//checked is a string containing the checked boxes id's seperated by a ","

	PSU::db('hr')->debug=true;

	$checked_id = $_POST['data'][0];
	$wpid = $_POST['data'][1];
	$response = $_POST['data'][2];
	$person = PSUPerson::get($wpid);
	$pidm=$person->pidm;
	$modified_by = $_SESSION['pidm'];

	$checklist_id = PSU::db('hr')->GetOne("SELECT id FROM person_checklists WHERE pidm=? AND closed = ?", array($pidm, 0));

	//check to see if checkbox exists
	$does_exist = PSU::db('hr')->GetOne("SELECT item_id FROM person_checklist_items WHERE item_id=? AND checklist_id=?", array($checked_id, $checklist_id));
	
	if (!$does_exist){
		$inserted = PSU::db('hr')->Execute("INSERT INTO person_checklist_items (item_id, checklist_id, response, updated_by) VALUES (?,?,?,?)",array($checked_id, $checklist_id, $response, $_SESSION['pidm']));
	}
	else{
		$update = PSU::db('hr')->Execute("UPDATE person_checklist_items SET response = ? WHERE item_id=? AND checklist_id=?", array($response, $checked_id, $checklist_id));
	}
	//update the person who modified this page
	$updated_by = PSU::db('hr')->Execute("UPDATE person_checklist_items SET updated_by = ? WHERE item_id=? AND checklist_id=?", array($modified_by, $checked_id, $checklist_id));

	
});
//the ajax for the done button on the checklist page
respond( '/checklist_post_done', function( $request, $responce, $app ) {
	
	$comments = $_POST['data'][0];
	$wpid = $_POST['data'][1];
	$pidm = PSUPerson::get($wpid)->pidm;
	$modified_by = $_SESSION['pidm'];

	$checklist_id = PSU::db('hr')->GetOne("SELECT id FROM person_checklists WHERE pidm=?", array($pidm));

	//checking to see if the person already exists in the database
	$result = PSU::db('hr')->GetAll("SELECT * FROM person_checklist_items WHERE checklist_id=? AND response=?", array($checklist_id, "notes"));

	if (isset($result[0])){
		$result1 = PSU::db('hr')->Execute("UPDATE person_checklist_items SET notes=? WHERE checklist_id=? AND response=?",array($comments, $checklist_id, "notes"));
		// if person has a db entry already	
	}
	else{
		$result2 = PSU::db('hr')->Execute("INSERT INTO person_checklist_items (checklist_id, item_id, response, notes, updated_by) VALUES (?, ?, ?, ?, ?)", array ($checklist_id, "007", "notes", $comments, $modified_by));
		//if they don't, make them one
	}

	
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

	PSU::mail("tfermm@gmail.com","Training Tracker - " . PSUPerson::get("$current_user_wpid")->formatname("f l") . " pay raise request","$message");
});
$app_routes = array();

foreach( $app_routes as $base ) {
	with( "/{$base}", $GLOBALS['BASE_DIR'] . "/routes/{$base}.php" );
}//end foreach

dispatch( $_SERVER['PATH_INFO'] );
