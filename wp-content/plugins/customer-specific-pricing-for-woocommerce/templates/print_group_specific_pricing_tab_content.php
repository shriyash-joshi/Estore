<?php
/**
 * Print the content of the tab
 * Saving of these custom fields is handled by function process_product_meta_group_specific_pricing_tab of WdmWuspSimpleProductsGsp class.
 */
global $post, $product;

wp_enqueue_script('jquery');
$remove_image_path = plugins_url('/images/minus-icon.png', dirname(__FILE__));
$add_image_path    = plugins_url('/images/plus-icon.png', dirname(__FILE__));

/**
 * Fetch WordPress groups. And creating a Dropdown List
 */
global $wpdb;
//Get the Group Id name pair from database.
$array_of_groupid_name_pair = $wpdb->get_results('SELECT group_id, name FROM ' . $wpdb->prefix . 'groups_group');
$html                       = '<select name="wdm_woo_groupname[]" class="chosen-select">';
//Insert each pair as options for the dropdown
foreach ($array_of_groupid_name_pair as $single_groupid_name_pair) {
	$html .= '<option value=' . esc_attr($single_groupid_name_pair->group_id) . ' >' . esc_html($single_groupid_name_pair->name) . '</option>';
}
$html               .= '</select>';
$wdm_groups_dropdown = $html;

$discountOptions               = array('1'=>__('Flat', 'customer-specific-pricing-for-woocommerce'), '2'=>'%');
$array_of_groupname_price_pair = array();
// Retrieve pricing from db and arrange it in associative array where key is group_id
// and value is price.
$array_of_groupname_price_pair = \WdmCSP\WdmWuspGetData::getAllGroupPricesForSingleProduct($post->ID);

if (empty($array_of_groupname_price_pair)) {
	$wdm_first_groupname      = '';
	$wdm_first_price_of_group = '';
	$wdm_first_qty_of_group   = '';
	$wdm_first_price_type     = 1;
} else {
	//Retrieve value of first group saved in db for corresponding product
	// $list_of_all_groups = array_keys($array_of_groupname_price_pair);

	// Push value of first group-price to variable. This variable would be
	// passed to JS file.
	$wdm_first_groupname      = $array_of_groupname_price_pair[0]->group_id;
	$wdm_first_price_of_group = wc_format_localized_price($array_of_groupname_price_pair[0]->price);
	$wdm_first_qty_of_group   = $array_of_groupname_price_pair[0]->min_qty;
	$wdm_first_price_type     = $array_of_groupname_price_pair[0]->price_type;
}

//Flag to track if more than one rows are available
$more_than_one_row = false;
$allowedHtml       = array(
					'select'=> array(
									'name'=>true,
									'class'=>true
									),
					'option'=> array(
									'value'=>true,
									'selected'=>true,
									),
					);
if (is_array($array_of_groupname_price_pair) && count($array_of_groupname_price_pair) > 0 && false != $array_of_groupname_price_pair) {
	echo "<script type='text/javascript'>var groupScntDiv = jQuery('#wdm_group_specific_pricing_tbody'); var wdm_temp_select_holder = null; var wdm_temp_html_holder = null;</script>";

	// Javascript is added here because it is going to show all combinations
	// and hence it needs looping. To solve the purpose of looping, javascript
	// is being added. This javascript shows only combinations saved in db.
	for ($j = 1; $j < count($array_of_groupname_price_pair); $j ++) {
		$more_than_one_row = true;
		?>
	<script type="text/javascript">
		jQuery( function () {  //Print all combinations saved in the database except first combination.
			wdm_temp_select_holder = jQuery( '<?php echo wp_kses(str_replace("\n", '', $wdm_groups_dropdown), $allowedHtml); ?>' );
			wdm_temp_select_holder.find( 'option[value="<?php echo esc_attr($array_of_groupname_price_pair[ $j ]->group_id); ?>"]' ).attr( 'selected', true );

			//Start new row
			start_row = "<tr>";
			//Show Groupname dropdown
			select_holder = "<td class='wdm_left_td' ><select name='wdm_woo_groupname[]' class='chosen-select'>" + wdm_temp_select_holder.html() + "</select></td>";

			//show price type dropdown
			type_holder = "<td class = 'wdm_left_td discount_options'><select name='wdm_group_price_type[]' class='chosen-select csp_wdm_action'>";
			<?php
			for ($i = 1; $i <= count($discountOptions); $i++) {
				?>
				var i = "<?php echo esc_html($i); ?>";
				<?php
				if ($array_of_groupname_price_pair[ $j ]->price_type == $i) {
					?>
					type_holder += "<option value ='"+i+"' selected>"+wdm_group_pricing_object.discountOptions[i]+"</option>";
					<?php
				} else {
					?>
					type_holder += "<option value ='"+i+"'>"+wdm_group_pricing_object.discountOptions[i]+"</option>";
					<?php
				}
			}
			?>
			type_holder += "</select></td>";

			//Show Quantity Textbox
			qty_textbox = "<td class='wdm_left_td'><input type='number' min = '1' name='wdm_woo_group_qty[]' class='wdm_qty' value='<?php echo esc_attr($array_of_groupname_price_pair[ $j ]->min_qty); ?>' /></td>";
		<?php
		if ( 2 == $array_of_groupname_price_pair[ $j ]->price_type ) {
			//Show Price's Textbox
			?>
	price_textbox = "<td colspan=3 class='wdm_left_td'><input type='text' name='wdm_woo_group_price[]' class='wdm_price csp-percent-discount' value='<?php echo esc_attr(wc_format_localized_price($array_of_groupname_price_pair[ $j ]->price)); ?>' /></td><td><a class='wdm_remove_pair_link' href='#' id='group_remScnt'  >";
			<?php
		} else {
			//Show Price's Textbox
			?>
	price_textbox = "<td colspan=3 class='wdm_left_td'><input type='text' name='wdm_woo_group_price[]' class='wdm_price' value='<?php echo esc_attr(wc_format_localized_price($array_of_groupname_price_pair[ $j ]->price)); ?>' /></td><td><a class='wdm_remove_pair_link' href='#' id='group_remScnt'  >";
			<?php
		}
		?>
			//Show Price's Textbox
			// price_textbox = "<td colspan=3 class='wdm_left_td'><input type='text' name='wdm_woo_group_price[]' class='wdm_price' value='<?php //echo $array_of_groupname_price_pair[ $j ]->price; ?>' /></td><td><a class='wdm_remove_pair_link' href='#' id='group_remScnt'  >";

			//Show Remove row button
			remove_row_button = "<img alt='Remove Pair' title='Remove Pair' class='remove_group_price_pair_row_image' src='<?php echo esc_url($remove_image_path); ?>'/></a>";

			add_new_row = '';
		<?php if ( count($array_of_groupname_price_pair)-1 == $j ) { ?>
				//Add new pair button
				add_new_row = "<a class='wdm_add_group_pair_link' href='#' id='wdm_add_new_group_price_pair'><img class='add_new_row_image' src='<?php echo esc_url($add_image_path); ?>' /></a>";
		<?php } ?>
			//end row
			end_row = "</td></tr>";

// Div for the Group Pricing tab
			groupScntDiv.append(
				start_row +
				select_holder +
				type_holder +
				qty_textbox +
				price_textbox +
				remove_row_button +
				add_new_row +
				end_row
				);
			wdm_temp_select_holder = null;
			if ( typeof chosen === "function" ) {
				jQuery( ".chosen-select" ).chosen( { 'width': '200px' } );
			}
		} );
	</script>
		<?php
	}
}

$array_of_values_to_be_passed_to_js = array(
	'wdm_groups_dropdown_html'   => str_replace("\n", '', $wdm_groups_dropdown),
	'wdm_first_groupname'        => $wdm_first_groupname,
	'wdm_first_price_of_group'   => $wdm_first_price_of_group,
	'wdm_first_qty_of_group'     => $wdm_first_qty_of_group,
	'wdm_first_price_type'   => $wdm_first_price_type,
	'discountOptions'        => $discountOptions,
	'remove_image_path'      => $remove_image_path,
	'add_image_path'         => $add_image_path,
	'more_than_one_row'      => $more_than_one_row,
	'add_new_pair'           => __('Add New Pair', 'customer-specific-pricing-for-woocommerce'),
	'add_new_group_text'  => __('Add New Group-Price Pair', 'customer-specific-pricing-for-woocommerce'),
);

//adding The Group Pricing js file
wp_enqueue_script('wdm_group_pricing_tab_js', plugins_url('/js/simple-products/customer-specific-pricing-tab/wdm-group-specific-pricing.js', dirname(__FILE__)), 'jquery', CSP_VERSION, true);

//localizing the values which is to be passed to the Group-Pricing js file
wp_localize_script('wdm_group_pricing_tab_js', 'wdm_group_pricing_object', $array_of_values_to_be_passed_to_js);
