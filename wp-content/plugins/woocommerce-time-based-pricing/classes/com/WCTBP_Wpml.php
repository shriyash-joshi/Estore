<?php
class WCTBP_Wpml
{
	var $curr_lang;
	public function __construct()
	{
	}
	public function get_default_lang()
	{
		if(!class_exists('SitePress'))
			return "none" ;
		
		global $sitepress;
		return $sitepress->get_default_language();
	}
	public function is_active()
	{
		return class_exists('SitePress');
	}
	public function remove_translated_id($items_array, $post_type = "product")
	{
		if(!class_exists('SitePress'))
			return false;
		
		$filtered_items_list = array();
		foreach($items_array as $item)	
		{
			/* $result = wpml_get_language_information($item->id);
			if(!is_bool (strpos($result['locale'], ICL_LANGUAGE_CODE)))
			{
				array_push($filtered_items_list, $item);
			}*/
			$item_id = is_object($item) && method_exists($item,'get_id') ? $item->get_id() : $item->id;
			//$item_type = is_object($item) && method_exists($item,'get_type') ? $item->get_type() : $item->type;

			if(function_exists('icl_object_id'))
				$item_translated_id = icl_object_id($item_id, $post_type, false, ICL_LANGUAGE_CODE);
			else
				$item_translated_id = apply_filters( 'wpml_object_id', $item_id, $post_type, false, ICL_LANGUAGE_CODE );
			if($item_id == $item_translated_id)
				array_push($filtered_items_list, $item);
		}
		return $filtered_items_list ;
	}
	public function get_all_translation_ids($post_id, $post_type = "product")
	{
		if(!class_exists('SitePress'))
			return false;
		
		global $sitepress, $wpdb;
		$translations = array();
		$translations_result = array();
		
		//if($post_type == "product")
		{
			$trid = $sitepress->get_element_trid($post_id, 'post_'.$post_type);
			$translations = $sitepress->get_element_translations($trid, $post_type);
			//wctbp_var_dump($translations);
			foreach($translations as $language_code => $item)
			{
				if($language_code != $sitepress->get_default_language())
					$translations_result[] = $item->element_id;
			}
			//wctbp_var_dump($translations_result);
		}
		
		return !empty($translations_result) ? $translations_result:false;
	}

	public function get_original_ids($items_array, $post_type = "product")
	{
		if(!class_exists('SitePress'))
			return false;
		
		global $sitepress;
		$original_ids = array();
		foreach($items_array as $item)	
		{
			$item_id = is_object($item) && method_exists($item,'get_id') ? $item->get_id() : $item->id;
			//$item_type = is_object($item) && method_exists($item,'get_type') ? $item->get_type() : $item->type;
			
			if(function_exists('icl_object_id'))
				$item_translated_id = icl_object_id($item_id, $post_type, true, $sitepress->get_default_language());
			else
				$item_translated_id = apply_filters( 'wpml_object_id', $item_id, $post_type, true, $sitepress->get_default_language() );
			
			if(!in_array($item_translated_id, $original_ids))
				array_push($original_ids, $item_translated_id);
		}
			
		return $original_ids;
	}
	public function get_original_id($item_id, $post_type = "product", $return_original = true)
	{
		if(!class_exists('SitePress'))
			return false;
		
		global $sitepress;
		if(function_exists('icl_object_id'))
			$item_translated_id = icl_object_id($item_id, $post_type, $return_original, $sitepress->get_default_language());
		else
			$item_translated_id = apply_filters( 'wpml_object_id', $item_id, $post_type, $return_original, $sitepress->get_default_language() );
		
		return $item_translated_id;
	}
	public function is_item_a_translation($item_id, $post_type = "product")
	{
		if(!$this->is_active())
			return false;
		
		$result = $this->get_original_id($item_id, $post_type);
		if($item_id != $result)
			return true;
		
		if($post_type == "product_variation")
			$_icl_lang_duplicate_of = get_post_meta( $item_id, '_wcml_duplicate_of_variation', true ); 
		else
			$_icl_lang_duplicate_of = get_post_meta( $item_id, '_icl_lang_duplicate_of', true );
		
		return $_icl_lang_duplicate_of != false ? true : false;
	}
	public function switch_to_default_language()
	{
		if(!$this->is_active())
			return;
		global $sitepress;
		$this->curr_lang = ICL_LANGUAGE_CODE ;
		$sitepress->switch_lang($sitepress->get_default_language());
	
	}
	public function switch_to_current_language()
	{
		if(!$this->is_active())
			return;
		
		global $sitepress;
		$sitepress->switch_lang($this->curr_lang);
	}
}
?>