<?php

namespace TrainingTracker;

class Staff extends \PSU_DataObject {
	function person(){
		$person = \PSUPerson::get("$i->username");
		$return_array['wpid'] = $person->wpid;
		$return_array['name'] = $person->formatName('f l');
		return $return_array;
	}


}//end class



