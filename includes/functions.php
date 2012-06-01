<?php


function contains($teams, $wpid){

	foreach ($teams as $team){
		
		if ($wpid == $team['mentee']){
			return $team['id'];
		}
	}
	return -1;

}



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


function get_stats($wpid){
	$checkboxes = PSU::db('hr')->GetAll("SELECT * FROM training_tracker_checklist_meta WHERE wpid=?",array($wpid));	

	$current_level = $checkboxes[0]['current_level'];
	$checked = $checkboxes[0]['checkboxes'];
	$completed = sizeof(explode(",",$checked)); 

	if (strcmp($current_level, 'trainee')==0){
		$search = array("a","b","c","d");
		if (strlen($checked) < 2){
			$progress = 0;
		}
		else{
			$progress = round((($completed/27)*100), 2);
		}
	}
	else if (strcmp($current_level,'sta')==0){
		$search = array("e","f","g","h","i","j","k","l");
		if ($completed >= 32){
					$progress = 100;
				}
				else if (strlen($checked) < 2){
					$progress = 0;
				}
				else{
					$progress = round((($completed/32)*100), 2);
				}
	}
	else{
		$search = array("m","n","o","p","q","r");
		if (strlen($checked) < 2){
			$progress = 0;
		}
		else{
			$progress = round((($completed/20)*100), 2);
		}	
	}
	$stats = array();
	foreach ($search as $item){
		$stat = substr_count($checked, "$item");
		if (strcmp($item,"a")==0){
			$stat = $stat/5;
		}
		else if (strcmp($item,"b")==0){
			$stat = $stat/9;
		}
		else if (strcmp($item,"c")==0){
			$stat = $stat/8;
		}
		else if (strcmp($item,"d")==0){
			$stat = $stat/5;
		}
		else if (strcmp($item,"e")==0){
			$stat = $stat/6;
		}
		else if (strcmp($item,"f")==0){
			$stat = $stat/4;
		}
		else if (strcmp($item,"g")==0){
			$stat = $stat/8;
		}
		else if (strcmp($item,"h")==0){
			$stat = $stat/3;
		}
		else if (strcmp($item,"i")==0){
			if ($stat > 2){
				$stat = 2;
			}
			$stat = $stat/2;
		}
		else if (strcmp($item,"j")==0){
			$stat = $stat/4;
		}
		else if (strcmp($item,"k")==0){
			if ($stat  > 1){
				$stat = 1;
			}
		}
		else if (strcmp($item,"l")==0){
			$stat = $stat/4;
		}
		else if (strcmp($item,"m")==0){
			$stat = $stat/5;
		}
		else if (strcmp($item,"n")==0){
			$stat = $stat/3;
		}
		else if (strcmp($item,"o")==0){
			$stat = $stat/2;
		}
		else if (strcmp($item,"p")==0){
			$stat = $stat/2;
		}
		else if (strcmp($item,"q")==0){
			$stat = $stat/4;
		}
		else if (strcmp($item,"r")==0){
			$stat = $stat/4;
		}
		
		$stats["$item"] = round(($stat*100), 2);
	}

	$total = 0;
	$ct = 0;
	foreach ($stats as $statistic){
		$ct++;
		$total += ($statistic);
	}
	$progress = round(($total/$ct), 2);

	$stats['progress'] = $progress;

	return $stats;
}
