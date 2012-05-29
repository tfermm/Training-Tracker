<?php
//http://kathack.com/
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

// klein catch-all
respond( '/?', function( $request, $response, $app ) {
	// this catch-all that may not be necessary for a fully developed app. In this example,
	// we are displaying the index.tpl which probably won't happen in a catch-all in a 
	// fully realized app.
	$wpid = $_SESSION['wp_id'];
	$current_user['name'] = (PSUPerson::get($wpid)->formatname("f l"));
	$current_user['wpid'] = $wpid;
	$staff_collection = new TrainingTracker\StaffCollection();
	$staff_collection->load(); 
	$mentor = $staff_collection->mentors();
	$result = $staff_collection->populate_meta();

	$is_mentor = false;
	foreach ($mentor as $teacher){
		if ($wpid == $teacher['wpid']){
			$is_mentor = true;
		}
	}
	$staff = $staff_collection->staff();

	$app->tpl->assign('current_user', $current_user);
	$app->tpl->assign('is_mentor', $is_mentor);
	$app->tpl->assign('staff', $staff);
	$app->tpl->display('index.tpl');
});


	//teams creation page
respond( '/teams', function( $request, $response, $app ) {
	$staff_collection = new TrainingTracker\StaffCollection();
	$staff_collection->load(); 
	$mentor = $staff_collection->mentors();
	$mentee = $staff_collection->mentees();
	//die();
 foreach ($mentor as &$teacher){ //check to see if they have a team
		$team = PSU::db('hr')->GetRow("SELECT * FROM teams WHERE mentor = ?", array($wpid));//do this every where
		//PSU::dbug( $team );
		if ($team){//if team
			$teacher['team'] = true;
	//		PSU::dbug( $teacher );
		}
		else{
			$student['team'] = false;
		}
	}



	$result = PSU::db('hr')->GetAll("SELECT * FROM checklist_item_categories WHERE 1");

	foreach ($mentee as &$student){ //get first and last name based off user name

		$wpid = $student['wpid'];
		$team = PSU::db('hr')->GetAll("SELECT * FROM teams WHERE mentee = ?", array($wpid));
	//	PSU::dbug( $team );
		if ($team){
			$student['team'] = true;
			$student['team_leader'] = $team[0]['mentor'];
		//	PSU::dbug( $student );
		}
		else{
			$student['team_leader'] = 'mentee';
			$student['team'] = false;
			//PSU::dbug( $student );
		}
	}
	$count = 0;
	$mentor_string = "";

	for ($i = 0; $i < sizeOf($mentor); $i++){ //prepairing a string to use for javascript #mentor0, #mentor1... etc.
		$mentor_string .= ('#mentor-' . $i . ", ");
		$count = $i;
	}//end for
	$count = 0;
	$mentor_string_li = "";
	
	for ($i = 0; $i < sizeOf($mentor); $i++){ //same string as before but with li at the end, #mentor0 li, #mentor1 li... etc.
		$mentor_string_li .= ('#mentor-' . $i . " li, ");
		$count = $i;
	}//end for

	$mentor_string_li .= "#mentor" . ($count+1 . " li"); //add final case to the string
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
	$current_user['name'] = PSUPerson::get($wpid)->formatname("f l");
	$current_user['wpid'] = $wpid;

	$checklist_checked = PSU::db('hr')->GetOne("SELECT checkboxes FROM training_tracker_checklist_meta WHERE wpid = ?",array($wpid));
	
	$current_user_level = PSU::db('hr')->GetOne("SELECT current_level FROM training_tracker_checklist_meta WHERE wpid = ?", array($wpid));
	$active_user_level = PSU::db('hr')->GetOne("SELECT current_level FROM training_tracker_checklist_meta WHERE wpid = ?", array($active_user['wpid']));

	$last_modified = PSU::db('hr')->GetAll("SELECT * FROM training_tracker_checklist_meta WHERE wpid = ?", array($current_user['wpid']));
	
	if (strlen($last_modified[0]['modified_by'])>2){
		$title = $current_user['name'] . " - Last modified by " . PSUPerson::get($last_modified[0]['modified_by'])->formatname("f l") . " on " . $last_modified[0]['time'];
	}
	else{
		$title = $current_user['name']; 
	}

	$comments = PSU::db('hr')->GetOne("SELECT comments FROM training_tracker_checklist_meta WHERE wpid = ?", array($wpid));
	if (!$comments){ //if there are no saved comments this is set default
		$comments = "Comments go here";
	}
	
	$staff_collection = new TrainingTracker\StaffCollection(); //get the people that work at the helpdesk
	$staff_collection->load(); 
	$mentor = $staff_collection->mentors();
	$mentee = $staff_collection->mentees();
   
	$checklist_items = get_checklist_items($current_user_level);
	$checklist_item_sub_cat = get_checklist_sub_cat($current_user_level);
	$checklist_item_cat = get_checklist_item_categories($current_user_level);

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

//view teams
respond( '/viewteams', function( $request, $responce, $app ) {

	$teams = PSU::db('hr')->GetAll("SELECT * FROM teams");
	$mentors = PSU::db('hr')->GetAll("SELECT DISTINCT mentor FROM teams");
	$i=0;
	foreach ($mentors as $mentor){
		$teamarray[PSUPerson::get($mentor['mentor'])->formatname("f l")]['mentees'] = array();
		$teamarray[PSUPerson::get($mentor['mentor'])->formatname("f l")]['mentor'] = PSUPerson::get($mentor['mentor'])->formatname("f l");
		$i++;
	}
	
	foreach ($teams as $team){
		array_push($teamarray[PSUPerson::get($team["mentor"])->formatname("f l")]['mentees'], PSUPerson::get($team["mentee"])->formatname("f l"));
	}

	$app->tpl->assign('team_array', $teamarray);
	$app->tpl->display('viewteams.tpl');
});

//the axax post part for teams
respond( '/teams_post', function( $request, $responce, $app ) {

	$post_data = $_POST['data'][0];
	
	$mentor = $_POST['data'][1];

	//PSU::db('hr')->debug=true;
	//$result = PSU::db('hr')->Execute("DELETE mentor FROM teams WHERE mentee = '$post_data'"); //if mentee is in a team, remove that team	
	$log_data = "DELETE * FROM teams WHERE mentee = '$post_data'";
	$result = PSU::db('hr')->GetAll("SELECT * FROM teams WHERE mentee = '$post_data'");
	//create new team for mentee

	if (isset($result[0])){
		$result1 = PSU::db('hr')->Execute("UPDATE teams SET mentor='$mentor' WHERE mentee = '$post_data'");
		// if team exists with that mentee replace mentor	
	}
	else{
		$result2 = PSU::db('hr')->Execute("INSERT INTO teams (mentor, mentee) VALUES ('$mentor', '$post_data')");
		//if the mentee isn't in a team make them a team
	}
	if ($mentor == NULL){
		$result3 = PSU::db('hr')->Execute("DELETE FROM teams WHERE mentee = '$post_data'");
	}
});

//the axax post part for checklist comments
respond( '/checklist_post_comments', function( $request, $responce, $app ) {

	//PSU::db('hr')->debug=true;

	$comments = $_POST['data'][0];
	
	$wpid = $_POST['data'][1];
	
	//making my own auto increment field
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

	$checked = $_POST['data'][0];
	$wpid = $_POST['data'][1];
	$modified_by = $_POST['data'][2];
//	$wpid = "p6jaeowpq";

	//making my own auto increment field
	$row = PSU::db('hr')->GetOne("SELECT MAX(id) FROM training_tracker_checklist_meta");
	$row +=1;
	$id = $row;

	//checking to see if the person already exists in the database
	$result = PSU::db('hr')->GetAll("SELECT * FROM training_tracker_checklist_meta WHERE wpid = ?", array($wpid));

	if (isset($result[0])){
		$result1 = PSU::db('hr')->Execute("UPDATE training_tracker_checklist_meta SET checkboxes = ? WHERE wpid = ?", array($checked, $wpid));
		// if person has a db entry already	
	}
	else{
		$result2 = PSU::db('hr')->Execute("INSERT INTO training_tracker_checklist_meta (checkboxes, wpid, id) VALUES (?, ?, ?)",array($checked, $wpid, $id));
		//if they don't, make them one
	}

		$result3 = PSU::db('hr')->Execute("UPDATE training_tracker_checklist_meta SET modified_by = ? WHERE wpid = ?", array($modified_by, $wpid));


});
//the ajax for the done button on the checklist page
respond( '/checklist_post_done', function( $request, $responce, $app ) {
	
	$comments = $_POST['data'][0];
	$checked = $_POST['data'][1];
	$current_user = $_POST['data'][2];
	$active_user = $_POST['data'][3];
	

	$result = PSU::db('hr')->Execute("UPDATE training_tracker_checklist_meta SET modified_by = ? WHERE wpid = ?", array($active_user, $current_user));
	$result2 = PSU::db('hr')->Execute("UPDATE training_tracker_checklist_meta SET comments = ? WHERE wpid = ?", array($comments, $current_user));
	$result3 = PSU::db('hr')->Execute("UPDATE training_tracker_checklist_meta SET checkboxes = ? WHERE wpid = ?", array($checked, $current_user));

});
$app_routes = array();

foreach( $app_routes as $base ) {
	with( "/{$base}", $GLOBALS['BASE_DIR'] . "/routes/{$base}.php" );
}//end foreach

dispatch( $_SERVER['PATH_INFO'] );
