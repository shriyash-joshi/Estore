<?php

class acf_field_product_variant_selector extends acf_field 
{



	function __construct() {

		$this->name     = 'product_variant_selector';
		$this->label    = __( 'Product Variant Selector', 'acf-product-variant-selector-field' );
		$this->category = __( 'Choice', 'acf' );
		$this->defaults = array(
			'return_value' => 'name',
			'field_type'   => 'checkbox',
			'allowed_variants'   => '',
		);

		parent::__construct();

	}

	function render_field_settings( $field ) {

		acf_render_field_setting( $field, array(
			'label'			=> __('Return Format','acf-product-variant-selector-field'),
			'instructions'	=> __('Specify the returned value type','acf-product-variant-selector-field'),
			'type'			=> 'radio',
			'name'			=> 'return_value',
			'layout'  =>  'horizontal',
			'choices' =>  array(
				'id'   => __( 'Product id', 'acf-product-variant-selector-field' ),
				/* 'object' => __( 'Role Object', 'acf-product-variant-selector-field' ), */
			)
		));

		/* global $wp_roles;
		acf_render_field_setting( $field, array(
			'label'			=> __('Allowed Roles','acf-product-variant-selector-field'),
			'type'			=> 'select',
			'name'			=> 'allowed_roles',
			'multiple'      => true,
			'instructions'   => __( 'To allow all roles, select none or all of the options to the right', 'acf-product-variant-selector-field' ),
			'choices' => $wp_roles->role_names
		)); */

		acf_render_field_setting( $field, array(
			'label'			=> __('Field Type','acf-product-variant-selector-field'),
			'type'			=> 'select',
			'name'			=> 'field_type',
			'choices' => array(
				__( 'Multiple Values', 'acf-product-variant-selector-field' ) => array(
					'select' => __( 'Select', 'acf-product-variant-selector-field' ),
					'multi_select' => __( 'Multi Select', 'acf-product-variant-selector-field' )
				),
				__( 'Single Value', 'acf-product-variant-selector-field' ) => array(
					'radio' => __( 'Radio Buttons', 'acf-product-variant-selector-field' ),
					'select' => __( 'Select', 'acf-product-variant-selector-field' )
				)
			)
		));



	}
	private function get_variations($product_id)
	{
		global $wpdb;
		
		//WPML
		if(class_exists('SitePress'))
		{
			global $sitepress;
			if(function_exists('icl_object_id'))
				$product_id = icl_object_id($product_id, 'product', true, $sitepress->get_default_language());
			else
				$product_id = apply_filters( 'wpml_object_id', $product_id, 'product', true, $sitepress->get_default_language() );
		}
		$product = wc_get_product( $product_id );
		if($product->get_type( ) == 'simple') //is simple product
			return array();
			
		 $query = "SELECT products.ID
		           FROM {$wpdb->posts} AS products 
				   WHERE products.post_parent = {$product_id} AND products.post_type = 'product_variation' "; //_regular_price
		 $result =  $wpdb->get_results($query); 
		 //wcpst_var_dump($result);
		 return isset($result) ? $result : array();		
	}
	function update_value($field_value, $post_id, $field )
	{
		$field_value = array_filter($field_value, function($value) { return $value !== ''; });
		return $field_value;
	} 
	function render_field( $field ) 
	{
		
		$product_id = get_the_ID();
		if(!$product_id)
			return;
		
		$variations = $this->get_variations($product_id);
		if(empty($variations) || !isset($variations))
		{
			echo "<strong><i>".__('Ignore this field, product is not a variable product.','acf-product-variant-selector-field')."</i></strong>";
			return;
		}
		
		
		// Select and multiselect fields
	    //if( $field['field_type'] == 'select' || $field['field_type'] == 'multi_select' ) :
	    	//$multiple = ( $field['field_type'] == 'multi_select' ) ? 'multiple="multiple"' : '';
		?>

			
				<?php
				//echo '<ul class="acf-'.$field['field_type'].'-list '.$field['field_type'].' ">';
					foreach( $variations as $variation ) :
						$checked = ( !empty( $field['value'] ) && in_array( $variation->ID, $field['value'] ) ) ? 'checked="checked"' : '';
				?>
				<label><input <?php echo $checked ?> type="checkbox" name="<?php echo $field['name'] ?>[]" value="<?php echo $variation->ID; ?>"><?php echo $variation->ID; ?></label>
				<?php
					endforeach;

					echo '<input type="hidden" name="' .  $field['name'] . '[]" value="" />';

					//echo '</ul>';
	}


	/*function format_value($value, $post_id, $field) {
		 if( $field['return_value'] == 'id' )
		{
			foreach( $value as $key => $id ) {
				$value[$key] = $id;
			}
		} 
		return $value;
	}*/


	
	/*function load_value($value, $post_id, $field) {

		 if( $field['return_value'] == 'id' )
		{
			foreach( $value as $key => $id ) {
				$value[$key] = $id;
			}
		} 
		
		return $value;
	}*/

}

new acf_field_product_variant_selector();

?>
