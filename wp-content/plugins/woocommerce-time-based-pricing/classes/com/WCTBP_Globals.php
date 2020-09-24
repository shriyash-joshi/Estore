<?php 
function wctbp_round($n)
{
	$num_of_decimals = wc_get_price_decimals();
	/* $x = 5;
	while($num_of_decimals > 0)
	{
		$n *= 10;
		$num_of_decimals--;
	}  */
	$result =  $num_of_decimals > 0 ? round($n,$num_of_decimals-1) : $n;  /*(round($n)%$x === 0) ? round($n) : round(($n+$x/2)/$x)*$x*/;
	
	$num_of_decimals = wc_get_price_decimals();
	/* while($num_of_decimals > 0)
	{
		$result /= 10;
		$num_of_decimals--;
	} */
	
	return $result;
}
$wctbp_result = get_option("_".$wctbp_id);
$wctbp_notice = !$wctbp_result || $wctbp_result != md5($_SERVER['SERVER_NAME']);
$wctbp_notice = false;
/* if($wctbp_notice)
	remove_action( 'plugins_loaded', 'wctbp_setup'); */
if(!$wctbp_notice)
	wctbp_setup();
function wctbp_get_value_if_set($data, $nested_indexes, $default)
{
	if(!isset($data))
		return $default;
	
	$nested_indexes = is_array($nested_indexes) ? $nested_indexes : array($nested_indexes);
	//$current_value = null;
	foreach($nested_indexes as $index)
	{
		if(!isset($data[$index]))
			return $default;
		
		$data = $data[$index];
		//$current_value = $data[$index];
	}
	
	return $data;
}
?>