<?php 
class WCTBP_User
{
	public function __construct() 
	{
	}
	function belongs_to_roles_rule_or_to_selected_users($roles, $selected_users, $strategy)
	{
		if((!$roles || empty($roles) || $roles[0] == "") && empty($selected_users))
			return true;
		
		if(!is_user_logged_in())
			return false;
		
		$current_user = wp_get_current_user();
		$current_user_roles = $current_user->roles;
		$current_user_id = $current_user->ID;
		$selected_user_ids = array();
		//wctbp_var_dump( $selected_users);
		foreach((array)$selected_users as $temp_user)
			$selected_user_ids[] = $temp_user['ID'];
		
		$result = false;
		if(!empty($selected_users))
			$result =  ($strategy == "all" && in_array($current_user_id,$selected_user_ids)) || ($strategy == "except" && !in_array($current_user_id,$selected_user_ids)) ? true : false;
		
		if((!$result && $strategy == "all")||($result && $strategy == "except"))
			$result = /* current_user_can( 'manage_options' ) || */ ($strategy == "all" && array_intersect($roles,$current_user_roles)) || ($strategy == "except" && !array_intersect($roles,$current_user_roles)) ? true : false;
		
		return $result;
	}
}
?>