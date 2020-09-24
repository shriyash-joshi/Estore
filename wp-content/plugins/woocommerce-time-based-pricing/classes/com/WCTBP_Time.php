<?php 
class WCTBP_Time
{
	public function __construct(){}

	public function check_if_now_matches_rule_datetime($rule_datetime)
	{
		/* Format
		$rule_datetime =>
			array(10) {
			  ["visibility"]=>
			  string(7) "publish"
			  ["day_type"]=>
			  string(15) "day_of_the_week"
			  ["days_of_the_week"]=>
			  array(7) {
					[0]=>
					string(1) "0"
					[1]=>
					string(1) "1"
					[2]=>
					string(1) "2"
					[3]=>
					string(1) "3"
					[4]=>
					string(1) "4"
					[5]=>
					string(1) "5"
					[6]=>
					string(1) "6"
				  }
			  ["days_of_the_month"]=>
			  array(0) {
			  }
			  ["months"]=>
			  array(12) {
				[0]=>
				string(1) "1"
				[1]=>
				string(1) "2"
				[2]=>
				string(1) "3"
				[3]=>
				string(1) "4"
				[4]=>
				string(1) "5"
				[5]=>
				string(1) "6"
				[6]=>
				string(1) "7"
				[7]=>
				string(1) "8"
				[8]=>
				string(1) "9"
				[9]=>
				string(2) "10"
				[10]=>
				string(2) "11"
				[11]=>
				string(2) "12"
			  }
			  ["years"]=>
			  string(0) ""
			  ["start_hour"]=>
			  string(2) "13"
			  ["start_minute"]=>
			  string(1) "0"
			  ["end_hour"]=>
			  string(2) "17"
			  ["end_minute"]=>
			  string(2) "59"
			}
		  */
		 
		global $wctbp_option_model;
		$time_offset = $wctbp_option_model->get_option('wctbp_time_offset', 0);
		$rule_datetime['start_minute'] = $rule_datetime['start_minute'] < 10 ? "0".$rule_datetime['start_minute'] : $rule_datetime['start_minute'];
		$rule_datetime['end_minute'] = $rule_datetime['end_minute'] < 10 ? "0".$rule_datetime['end_minute'] : $rule_datetime['end_minute'];
		$rule_datetime['days_of_the_week'] = !is_array($rule_datetime['days_of_the_week']) ? array() : $rule_datetime['days_of_the_week'];
		$rule_datetime['days_of_the_month'] = !is_array($rule_datetime['days_of_the_month']) ? array() : $rule_datetime['days_of_the_month'];
		/* wctbp_var_dump((int)$rule_datetime['end_hour'] > (int)date('G',strtotime($time_offset.' minutes')) && (int)$rule_datetime['end_minute'] > (int)date('i'));  	
		wctbp_var_dump((int)$rule_datetime['start_hour']." ".(int)$rule_datetime['start_minute']);  	
		wctbp_var_dump((int)date('G',strtotime($time_offset.' minutes'))." ".(int)date('i'));  	
		 */
		
		if( (($rule_datetime['day_type'] == 'day_of_the_week' && in_array((string)date('w'), $rule_datetime['days_of_the_week'])) || ($rule_datetime['day_type'] == 'day_of_the_month' && in_array((string)date('j'), $rule_datetime['days_of_the_month'])) ) &&  //day
		    in_array((string)date('n'), $rule_datetime['months']) &&  //month
		   (!$rule_datetime['years'] || in_array((string)date('Y'), $rule_datetime['years'])) && //year
		    strtotime($rule_datetime['start_hour'].":".$rule_datetime['start_minute'].":00") < strtotime(date('H:i:00',strtotime($time_offset.' minutes'))) &&  //start hour & minute
			( $rule_datetime['use_end_time']=='no' || strtotime(date('H:i:59',strtotime($time_offset.' minutes'))) < strtotime($rule_datetime['end_hour'].":".$rule_datetime['end_minute'].":59"))  //end hour & minute 
		   ) 
		    {
			   return true;
		    } 
		 return false;
	}
}
?>