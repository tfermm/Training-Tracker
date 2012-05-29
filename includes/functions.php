<?php


function contains($teams, $wpid){

	foreach ($teams as $team){
		
		if ($wpid == $team['mentee']){
			return $team['id'];
		}
	}
	return -1;

}



//join with phonebook.phonebook where username = email
//sort users
function sort_users($filter){
	$client = \PSU::api('backend'); //load API
	$users = $client->get('support/users'); //get ALL the people that work at the help desk
	//$user = \PSUperson::get($users[0][1]);
	//print_r($users);
	$special_users = array();
	$j = 0;
	foreach ($users as $i){	 //convert the object we get back from the API to an array
		if ($filter == $i->privileges){ //if the privilage we pass in is equal to the persons privilage
			$special_users[$j]['username'] = $i->username;
			$special_users[$j]['privileges'] = 	$i->privileges;

			$j++;
		}
	}	//end for each
	return $special_users;
}


//get item categories
function get_checklist_item_categories($slug){
	$result = PSU::db('hr')->GetAll("SELECT * 
						FROM  `checklist_item_categories` 
						WHERE slug = ?", array($slug));
	return $result;
}


function get_checklist_items($current_user_level){
	if ($current_user_level == 'trainee'){
		$cat_ID = 11;
	}
	else if ($current_user_level == 'sta'){
		$cat_ID = 12;
	}
	else if ($current_user_level == 'shift_leader'){
		$cat_ID = 13;
	}
	else if ($current_user_level == 'supervisor'){
		$cat_ID = 14;
	}
	$result = PSU::db('hr')->GetAll("SELECT * FROM checklist_items WHERE category_id = ?", array($cat_ID));
	return $result;
}


function get_checklist_sub_cat($category){
//	\PSU::db('hr')->debug=true;
	$result = PSU::db('hr')->GetAll("SELECT * FROM checklist_item_sub_categories WHERE slug = ?", array($category));
	return $result;
}



