<?php

class acf_field_unique_id extends acf_field {

	/*
	*  __construct
	*
	*  This function will setup the field type data
	*
	*  @type	function
	*  @date	5/03/2014
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/

	function __construct() {

		/*
		*  name (string) Single word, no spaces. Underscores allowed
		*/

		$this->name = 'unique_id';


		/*
		*  label (string) Multiple words, can include spaces, visible when selecting a field type
		*/

		$this->label = __('Unique ID', 'acf-unique_id');


		/*
		*  category (string) basic | content | choice | relational | jquery | layout | CUSTOM GROUP NAME
		*/

		$this->category = 'layout';


		/*
		*  l10n (array) Array of strings that are used in JavaScript. This allows JS strings to be translated in PHP and loaded via:
		*  var message = acf._e('unique_id', 'error');
		*/

		$this->l10n = array(
		);


		// do not delete!
    	parent::__construct();
    
	}


	function render_field( $field ) {
		?>
		<input type="text" readonly="readonly" name="<?php echo esc_attr($field['name']) ?>" value="<?php echo esc_attr($field['value']) ?>" />
		<?php
	}


	function update_value( $value, $post_id, $field ) {
		if (!$value) {
			$value = uniqid();
		}
		return $value;
	}


	function validate_value( $valid, $value, $field, $input ){
		return true;
	}
}


// create field
new acf_field_unique_id();
