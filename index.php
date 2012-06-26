<?php
// TODO: global suggestion:  Move all SQL in routes to API/method calls
// TODO: move all SQL out of the parameters of ADOdb methods and into $sql variables
// TODO: Remove commented code where it isn't needed.
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

	// get the logged in user
	$app->user = PSUPerson::get( $_SESSION['wp_id'] ); 

	$memcache = new \PSUMemcache('training-tracker_teams');
	if ( ! ($cached_results = $memcache->get('is_admin'))){

		// TODO: move $current_user junk to the catch-all.
		$current_user_parameters["wpid"] = $app->user->wpid;

		// TODO: move to catch-all
		$current_user = new TrainingTracker\Staff($current_user_parameters);

		$staff_collection = new TrainingTracker\StaffCollection();
		$staff_collection->load(); 
		$valid_users = $staff_collection->valid_users();

		$is_valid = false;
		$is_mentor = false;
		$is_admin = false;
		foreach ($valid_users as $user){
			if ($app->user->wpid == $user->wpid){
				$is_valid = true;
			}
		}	

		if (!$is_valid){
			die("You do not have access to this app.");
		}


		 //Make me a LLC Manager in callog
		//$result = PSU::db('calllog')->Execute("UPDATE call_log_employee SET user_privileges = 'manager' WHERE user_name='tlferm'");

		$teams_data = TrainingTracker::get_teams();

		$has_team = false;
		$wpid = $app->user->wpid;
		if (isset($teams_data["$wpid"])){
			$has_team = true;
		}

		$admins = $staff_collection->admins();
		$mentors = $staff_collection->mentors();

		foreach ($admins as $admin){
			if ($app->user->wpid == $admin->wpid){
				$is_admin = true;
				$is_mentor = true;
			}
		}
		if (!$is_mentor){
			foreach ($mentors as $mentor){
				if ($app->user->wpid == $teacher->wpid){
					$is_mentor = true;
				}
			}
		}

		$active_user_parameters["wpid"] = $wpid;
		$active_user = new TrainingTracker\Staff($active_user_parameters);


		$memcache->set( 'active_user', $active_user, MEMCACHE_COMPRESSED, 60 * 5);
		$memcache->set( 'has_team', $has_team, MEMCACHE_COMPRESSED, 60 * 5);
		$memcache->set( 'is_admin', $is_admin, MEMCACHE_COMPRESSED, 60 * 5);
		$memcache->set( 'is_mentor', $is_mentor, MEMCACHE_COMPRESSED, 60 * 5);
		$memcache->set( 'is_valid', $is_valid, MEMCACHE_COMPRESSED, 60 * 5);

	}
	else{

		$active_user = $memcache->get('active_user');
		$has_team = $memcache->get('has_team');
		$is_admin = $memcache->get('is_admin');
		$is_mentor = $memcache->get('is_mentor');
		$is_valid = $memcache->get('is_mentor');
	}

	if (!$is_valid){
		die("You do not have access to this app.");
	}


	$app->active_user = $active_user;
	$app->is_admin = $is_admin;
	$app->is_mentor = $is_mentor;
	// initialize the template
	$app->tpl = new PSUTemplate;

	// assign user to template
	$app->tpl->assign('active_user', $active_user);
	$app->tpl->assign('user', $app->user);
	$app->tpl->assign('has_team', $has_team);
	$app->tpl->assign('wpid', $wpid);
	$app->tpl->assign('is_admin', $is_admin);
	$app->tpl->assign('is_mentor', $is_mentor);
});

// the person select page
respond( '/?', function( $request, $response, $app ) {

	if ($app->is_mentor){
		$staff_collection = new TrainingTracker\StaffCollection();
		$staff_collection->load();

		$staff = $staff_collection->staff();
		foreach ($staff as $person){
			$pidm = $person->person()->pidm;

			if (!TrainingTracker::checklist_exists($pidm)){
				//get tybe based off of a persons privileges
				$type = TrainingTracker::checklist_type($person->privileges);
				//insert new checklist (pidm, type)
				TrainingTracker::checklist_insert($pidm, $type);

			}
		}
	}
	else{
		$current_user_parameter["wpid"] = $app->user->wpid;
		$staff = new TrainingTracker\Staff($current_user_parameter);
	}
	$app->tpl->assign('staff', $staff);
	$app->tpl->display('index.tpl');
});
$app_routes = array(
	 'staff', 
	 'team' 
);

foreach( $app_routes as $base ) {
	with( "/{$base}", $GLOBALS['BASE_DIR'] . "/routes/{$base}.php" );
}//end foreach

dispatch( $_SERVER['PATH_INFO'] );
