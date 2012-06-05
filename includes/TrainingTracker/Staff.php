<?php

namespace TrainingTracker;

class Staff extends \PSU_DataObject {
	function person(){
		$person = \PSUPerson::get($this->username);
		return $person;
	}

	public function stats($parameter = null){
		$wpid = $this->wpid;
		$person = \PSUPerson::get($wpid);
		$pidm = $person->pidm;
		$username = $person->username;

		$checklist_id = \PSU::db('hr')->GetOne("SELECT id FROM person_checklists WHERE pidm=?",array($pidm));	

		$checkboxes = \PSU::db('hr')->GetAll("SELECT * FROM person_checklist_items WHERE checklist_id=? AND response=?", array($checklist_id, "complete"));

		$current_level = \PSU::db('calllog')->GetOne("SELECT user_privileges FROM call_log_employee WHERE user_name=?", array($username));
		$completed = sizeof($checkboxes); 


		if (strcmp($current_level, 'trainee')==0){
			$search = array("16","17","18","19");
		}
		else if (strcmp($current_level,'sta')==0){
			$search = array("20","21","22","23","24","25","26","27");
		}
		else{
			$search = array("28","29","30","31","32","33");
		}
		$stats = array();
		foreach ($search as $item){

			$stat = \PSU::db('hr')->GetAll("SELECT items.item_id	FROM person_checklist_items items 
																													JOIN person_checklists checklist 
																													ON items.checklist_id = checklist.id 
																													JOIN checklist_item_categories categories 
																													ON categories.type = checklist.type
																													JOIN checklist_items checklist_items
																													ON checklist_items.id = items.item_id
																													WHERE items.checklist_id = checklist.id 
																													AND checklist.type = categories.type
																													AND categories.id=?
																													AND items.response=?
																													AND checklist_items.category_id = categories.id
																													AND checklist.pidm=?", array($item,"complete", $pidm)); 
		
		
		
			$stat = sizeof($stat);
			if ($item == 16){
				$stat = $stat/5;
			}
			else if ($item == 17){
				$stat = $stat/9;
			}
			else if ($item == 18){
				$stat = $stat/8;
			}
			else if ($item == 19){
				$stat = $stat/5;
			}
			else if ($item == 20){
				$stat = $stat/6;
			}
			else if ($item == 21){
				$stat = $stat/4;
			}
			else if ($item == 22){
				$stat = $stat/8;
			}
			else if ($item == 23){
				$stat = $stat/3;
			}
			else if ($item == 24){
				if ($stat > 2){
					$stat = 2;
				}
				$stat = $stat/2;
			}
			else if ($item == 25){
				$stat = $stat/4;
			}
			else if ($item == 26){
				if ($stat  > 1){
					$stat = 1;
				}
			}
			else if ($item == 27){
				$stat = $stat/4;
			}
			else if ($item == 28){
				$stat = $stat/5;
			}
			else if ($item == 29){
				$stat = $stat/3;
			}
			else if ($item == 30){
				$stat = $stat/2;
			}
			else if ($item == 31){
				$stat = $stat/2;
			}
			else if ($item == 32){
				$stat = $stat/4;
			}
			else if ($item == 33){
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

		//PSU::dbug($stats);
		//die();
		return (isset($parameter)?$stats["$parameter"]:$stats);

		
	
	//get stats
		//todo move get stats logic to here
		//$stats = get_stats($this->wpid);
		//return $stats;
	}//end stats

}//end class



