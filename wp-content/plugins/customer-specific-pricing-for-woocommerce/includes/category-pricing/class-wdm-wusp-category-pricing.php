<?php

namespace cspCategoryPricing;

if (!class_exists('WdmWuspCategoryPricing')) {
	/**
	* Class for category specific pricing.
	* Enqueuing required scripts.
	* For showing the user/role/group category pricing in the tab.
	* For saving the current selection records in the database.
	*/
	class WdmWuspCategoryPricing {
	
		private static $product_categories;
		private static $discountOptions;
		private static $minusIcon;
		private static $plusIcon;
		/**
		* Icons for adding and removing a specific pricing
		* This function also adds the following actions:
		* Action for enqueuing scripts for the category tab.
		* Action for showing the user/role/group pricing records.
		* Action for saving the category-pricing records of current
		* selection in the database.
		*/
		public function __construct() {
			self::$minusIcon = plugins_url('/images/minus-icon.png', dirname(dirname(__FILE__)));
			self::$plusIcon  = plugins_url('/images/plus-icon.png', dirname(dirname(__FILE__)));
			$catArgs         = array(
				'order'      => 'ASC',
				'hide_empty' => 0,
				'posts_per_page' =>'-1'
			);

			self::$product_categories = get_terms('product_cat', $catArgs);
			self::$discountOptions    = array('-1' => __('Discount Type', 'customer-specific-pricing-for-woocommerce'),'1'=>__('Flat', 'customer-specific-pricing-for-woocommerce'), '2'=>'%');
			add_action('admin_enqueue_scripts', array($this, 'cspCategoryEnqueueScripts'), 15);
			add_action('csp_show_user_data', array($this, 'showUserPricingRecords'), 10);
			add_action('csp_show_role_data', array($this, 'showRolePricingRecords'), 10);
			add_action('csp_show_group_data', array($this, 'showGroupPricingRecords'), 10);
			add_action('admin_init', array($this, 'saveCategoryRecords'), 10);
			add_action('wp_ajax_nopriv_set_cat_pricing_status', array($this, 'changeCategoryPricingStatus'));
			add_action('wp_ajax_set_cat_pricing_status', array($this, 'changeCategoryPricingStatus'));
		}


		/**
		 * Enables/Disables the Category pricing feature
		 *
		 * @return void
		 */
		public function changeCategoryPricingStatus() {
			if (empty($_POST['cd_nonce']) || !wp_verify_nonce(sanitize_text_field($_POST['cd_nonce']), 'wdm-csp-ctd')) {
				echo 'Security Check';
				exit();
			}
			if (isset($_POST['featureStatus']) && in_array($_POST['featureStatus'], array('enable','disable'))) {
				$status = sanitize_text_field($_POST['featureStatus']);
				esc_html_e(update_option('cspCatPricingStatus', $status));
				die();
			}
		}

		/**
		* Checks whether the request is made from the category-pricing tab
		* page.
		* Nonce verification is done.
		* Save the user/group/role category records.
		*/
		public function saveCategoryRecords() {
			// page=customer_specific_pricing_single_view&tabie=category_pricing

			global $cspFunctions;
			if (!isset($_POST['save_records'])) {
				return;
			}

			if (isset($_REQUEST['page']) && 'customer_specific_pricing_single_view'!=$_REQUEST['page'] && isset($_REQUEST['tabie']) && 'category_pricing'!=$_REQUEST['tabie']) {
				return;
			}

			$nonce              = isset($_REQUEST['_save_category'])?sanitize_text_field($_REQUEST['_save_category']):'';
			$nonce_verification = wp_verify_nonce($nonce, 'csp_save_category_pricing');
			if (! $nonce_verification) {
				echo 'Security Check';
				exit;
			}

			$postArray = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

			
			$this->saveUserRecords($postArray);
			$this->saveRoleRecords($postArray);

			if ($cspFunctions->wdmIsActive('groups/groups.php')) {
				$this->saveGroupRecords($postArray);
			}
			$this->removeAllCategoryTransients();
		}


		/**
		 * Deletes all the transiednts stored in the transients array
		 * as all the category pricing pairs are updated.
		 *
		 * @return void
		 */
		public function removeAllCategoryTransients() {
			global $wpdb;
			$nonce              = isset($_REQUEST['_save_category'])?sanitize_text_field($_REQUEST['_save_category']):'';
			$nonce_verification = wp_verify_nonce($nonce, 'csp_save_category_pricing');
			if (! $nonce_verification) {
				echo 'Security Check';
				exit;
			}
			$transientsUser = '_transient_trans_user_cat_%';
			$transientsRole = '_transient_trans_role_cat_%';
			$wpdb->query($wpdb->prepare('DELETE FROM ' . $wpdb->prefix . 'options WHERE (option_name LIKE %s) OR (option_name LIKE %s)', $transientsUser, $transientsRole));
		}

		/**
		* Gets the data of user-category-pricing from the current selection.
		* If there isn't any data in current selection, delete data from DB.
		* Add the new user-category-specific-pricing in the DB.
		*/
		public function saveUserRecords( $postArray ) {
			global $addCatRecords, $deleteCatRecords;
			$userCatArray    = isset($postArray['wdm_woo_user_category']) ? $postArray['wdm_woo_user_category'] : array();
			$userIdsArray    = isset($postArray['wdm_woo_username']) ? $postArray['wdm_woo_username'] : array();
			$userPriceArray  = isset($postArray['wdm_user_value']) ? $postArray['wdm_user_value'] : array();
			$userMinQtyArray = isset($postArray['wdm_user_qty']) ? $postArray['wdm_user_qty'] : array();
			$userTypeArray   = isset($postArray['wdm_user_price_type']) ? $postArray['wdm_user_price_type'] : array();
			
			// $userRecords = $this->filterUnselectedRecords($userCatArray,)

			if (empty($userIdsArray)) {
				//delete user records
				$deleteCatRecords->deleteAllUserRecords();
			} else {
				$addCatRecords->addUserCategoryRecords($userCatArray, $userIdsArray, $userPriceArray, $userMinQtyArray, $userTypeArray);
			}
		}

		/**
		* Gets the data of role-category-pricing from the current selection.
		* If there isn't any data in current selection, delete data from DB.
		* Add the new role-category-specific-pricing in the DB.
		*/
		public function saveRoleRecords( $postArray ) {
			global $addCatRecords, $deleteCatRecords;

			$roleCatArray    = isset($postArray['wdm_woo_role_category']) ? $postArray['wdm_woo_role_category'] : array();
			$rolesArray      = isset($postArray['wdm_woo_roles']) ? $postArray['wdm_woo_roles'] : array();
			$rolePriceArray  = isset($postArray['wdm_role_value']) ? $postArray['wdm_role_value'] : array();
			$roleMinQtyArray = isset($postArray['wdm_role_qty']) ? $postArray['wdm_role_qty'] : array();
			$roleTypeArray   = isset($postArray['wdm_role_price_type']) ? $postArray['wdm_role_price_type'] : array();

			if (empty($rolesArray)) {
				//delete role records
				$deleteCatRecords->deleteAllRoleRecords();
			} else {
				$addCatRecords->addRoleCategoryRecords($roleCatArray, $rolesArray, $rolePriceArray, $roleMinQtyArray, $roleTypeArray);
			}
		}

		/**
		* Gets the data of group-category-pricing from the current selection.
		* If there isn't any data in current selection, delete data from DB.
		* Add the new group-category-specific-pricing in the DB.
		*/
		public function saveGroupRecords( $postArray ) {
			global $addCatRecords, $deleteCatRecords;
			
			$groupCatArray    = isset($postArray['wdm_woo_group_category']) ? $postArray['wdm_woo_group_category'] : array();
			$groupIdsArray    = isset($postArray['wdm_woo_groupname']) ? $postArray['wdm_woo_groupname'] : array();
			$groupPriceArray  = isset($postArray['wdm_group_value']) ? $postArray['wdm_group_value'] : array();
			$groupMinQtyArray = isset($postArray['wdm_group_qty']) ? $postArray['wdm_group_qty'] : array();
			$groupTypeArray   = isset($postArray['wdm_group_price_type']) ? $postArray['wdm_group_price_type'] : array();

			if (empty($groupIdsArray)) {
				//delete group records
				$deleteCatRecords->deleteAllGroupRecords();
			} else {
				$addCatRecords->addGroupCategoryRecords($groupCatArray, $groupIdsArray, $groupPriceArray, $groupMinQtyArray, $groupTypeArray);
			}
		}

		/**
		 * Undocumented function
		 *
		 * @param [type] $userRecords
		 * @return void
		 *
		 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
		 */
		public function removeUnselectedRecords( $userRecords) {
			return array_filter($userRecords, function ( $value, $key) {
				return '-1'!== $value;
			}, ARRAY_FILTER_USE_BOTH);
		}

		/**
		* Enqueue scripts and styles for customer specific pricing tab in CSP
		*/
		public function cspCategoryEnqueueScripts() {
			if (isset($_GET['page']) && isset($_GET['tabie']) && 'category_pricing'==$_GET['tabie']) {
				//wp_enqueue_script('jquery-ui-accordion');
				//wp_enqueue_style('jquery-ui-style');

				wp_enqueue_style('csp_bootstrap_css', plugins_url('/css/import-css/bootstrap.css', dirname(dirname(__FILE__))), array(), CSP_VERSION);

				wp_enqueue_script(
					'wdm_csp_cat_pricing_js',
					plugins_url('/js/category-js/wdm-csp-cat-pricing-script.js', dirname(dirname(__FILE__))),
					array('jquery', 'jquery-ui-accordion'),
					CSP_VERSION
				);

				wp_localize_script('wdm_csp_cat_pricing_js', 'cat_pricing_object', array(
					'nonce'    => wp_create_nonce('wdm-csp-ctd'),
					'ajax_url' => admin_url('admin-ajax.php'),
					'loading_image_path' => plugins_url('/images/loading .gif', dirname(dirname(__FILE__))),
					'add_image_path' => self::$plusIcon,
					'remove_image_path' => self::$minusIcon,
				));

				wp_enqueue_style(
					'wdm_csp_cat_pricing_css',
					plugins_url('/css/category-css/wdm-csp-cat-pricing-style.css', dirname(dirname(__FILE__))),
					array(),
					CSP_VERSION
				);
			}
		}

		/**
		* Includes the template for the category-pricing tab.
		*/
		public function cspShowCategoryPricing() {
			include_once 'category-template.php';
			new CategoryTemplate();
		}

		/**
		* Return true if it is the last record.
		 *
		* @param int $ctr 0 or 1.
		* @param int $length count of the category specific records.
		*/
		public function isLastRecord( $ctr, $length) {
			return $ctr == $length ? true : false ;
		}

		/**
		* Returns true if price-type is discount.
		 *
		* @param int $priceType Price-Type.
		* @return bool true if price-type is discount otherwise false.
		*/
		public function isPercentValue( $priceType) {
			return  2==$priceType ? true : false ;
		}

		/**
		* Gets the user category pricing pairs.
		* Displays the user-specific-pricing section in category-pricing tab.
		* For the last row add an option for adding new pricing pair.
		*/
		public function showUserPricingRecords() {
			global $getCatRecords;
			$userData = $getCatRecords->getAllUserCategoryPricingPairs();

			if (empty($userData)) {
				$this->userHtml();
				return;
			}

			$ctr    = 0;
			$length = count($userData);

			foreach ($userData as $userRecord) {
				$addbutton = $this->isLastRecord(++$ctr, $length);
				$this->userHtml($addbutton, $userRecord->user_id, $userRecord->cat_slug, $userRecord->price_type, $userRecord->min_qty, $userRecord->price);
			}
			?>

			<?php
		}

		/**
		* Gets the role category pricing pairs.
		* Displays the role-specific-pricing section in category-pricing tab.
		* For the last row add an option for adding new pricing pair.
		*/
		public function showRolePricingRecords() {
			global $getCatRecords;
			$roleData = $getCatRecords->getAllRolesCategoryPricingPairs();
			
			if (empty($roleData)) {
				$this->roleHtml();
				return;
			}

			$ctr    = 0;
			$length = count($roleData);

			foreach ($roleData as $roleRecord) {
				$addbutton = $this->isLastRecord(++$ctr, $length);
				$this->roleHtml($addbutton, $roleRecord->role, $roleRecord->cat_slug, $roleRecord->price_type, $roleRecord->min_qty, $roleRecord->price);
			}
		}

		/**
		* Gets the group category pricing pairs.
		* Displays the group-specific-pricing section in category-pricing
		* tab.
		* For the last row add an option for adding new pricing pair.
		*/
		public function showGroupPricingRecords() {
			global $getCatRecords;
			$groupData = $getCatRecords->getAllGroupCategoryPricingPairs();

			if (empty($groupData)) {
				$this->groupHtml();
				return;
			}

			$ctr    = 0;
			$length = count($groupData);

			foreach ($groupData as $groupRecord) {
				$addbutton = $this->isLastRecord(++$ctr, $length);
				$this->groupHtml($addbutton, $groupRecord->group_id, $groupRecord->cat_slug, $groupRecord->price_type, $groupRecord->min_qty, $groupRecord->price);
			}
		}

		/**
		* Displays the user-specific-section in category pricing tab.
		* Gets the price type for the entry and set the css class accordingly
		* Gets the User, category,discount options for that sections.
		* Prepare the HTML DOM for the user specific pricing section.
		 *
		* @param bool $addbutton true if last row then add the add new row
		* button
		* @param int $user user id in user-category-pricing table.
		* @param string $catSlug category slug for that user-id.
		* @param int $priceType price type for discount.
		* @param int $minQty minimum quantity.
		* @param float $value price of product.
		*/
		public function userHtml( $addbutton = true, $user = '', $catSlug = '', $priceType = '', $minQty = '', $value = '') {
			$categoryName = 'wdm_woo_user_category[]';
			$userName     = 'wdm_user_price_type[]';
			$valueName    = 'wdm_user_value[]';
			$qtyName      = 'wdm_user_qty[]';
			$valueClasses = $this->isPercentValue($priceType) ? 'csp-percent-discount wdm_price' : 'wdm_price' ;
			$typeClasses  = 'csp_wdm_action';
			$qtyClasses   = 'wdm_qty';
			?>
			<div class="category-row user-row">
				<?php $this->generateUserDropdown($user); ?>
				<select name="<?php echo esc_attr($categoryName); ?>">
				<?php $this->generateCategoryOptions($catSlug); ?>
				</select>
				<select class = "<?php echo esc_attr($typeClasses); ?>" name="<?php echo esc_attr($userName); ?>">
				<?php $this->generateDiscountOptions($priceType); ?>
				</select>
					<input type="number" min="1" class = "<?php echo esc_attr($qtyClasses); ?>" name="<?php echo esc_attr($qtyName); ?>" placeholder="Min Qty" value = "<?php echo esc_attr($minQty); ?>" />
					<input type="text" class = "<?php echo esc_attr($valueClasses); ?>" name="<?php echo esc_attr($valueName); ?>" placeholder="Value" value = "<?php echo esc_attr($value); ?>" />
					<span class = "add_remove_button">
						<img class="remove_user_row_image" alt="Remove Row" title="Remove Row" tabindex="0" src="<?php echo esc_url(self::$minusIcon); ?>" />
						<?php 
						if ($addbutton) {
							?>
							<img class='add_new_user_row_image' title="Add Row" tabindex="0" src='
								<?php
								echo esc_url(self::$plusIcon);
								?>
								' />
						<?php } ?>
					</span>
			</div>
			<?php
		}

		/**
		* Displays the role-specific-section in category pricing tab.
		* Gets the price type for the entry and set the css class accordingly
		* Gets the Role, category,discount options for that sections.
		* Prepare the HTML DOM for the role specific pricing section.
		 *
		* @param bool $addbutton true if last row then add the add new row
		* button
		* @param string $role role in role-category-pricing table.
		* @param string $catSlug category slug for that user-id.
		* @param int $priceType price type for discount.
		* @param int $minQty minimum quantity.
		* @param float $value price of product.
		*/
		public function roleHtml( $addbutton = true, $role = '', $catSlug = '', $priceType = '', $minQty = '', $value = '') {
			$valueClasses = $this->isPercentValue($priceType) ? 'csp-percent-discount wdm_price' : 'wdm_price' ;
			$typeClasses  = 'csp_wdm_action';
			$qtyClasses   = 'wdm_qty';
			?>
			<div class="category-row role-row">
				<?php $this->generateRoleDropdown($role); ?>
				<select name='wdm_woo_role_category[]'>
				<?php $this->generateCategoryOptions($catSlug); ?>
				</select>
				<select class = "<?php echo esc_attr($typeClasses); ?>" name='wdm_role_price_type[]'>
				<?php $this->generateDiscountOptions($priceType); ?>
				</select>
					<input type="number" min="1" class = "<?php echo esc_attr($qtyClasses); ?>" name="wdm_role_qty[]" placeholder="Min Qty" value = "<?php echo esc_attr($minQty); ?>" />
					<input type="text" class = "<?php echo esc_attr($valueClasses); ?>" name="wdm_role_value[]" placeholder="Value" value = "<?php echo esc_attr($value); ?>" />
					<span class = "add_remove_button">
						<img class="remove_role_row_image" tabindex="0" alt="Remove Row" title="Remove Row" src="<?php echo esc_url(self::$minusIcon); ?>" />
						<?php 
						if ($addbutton) {
							?>
							<img class="add_new_role_row_image" tabindex="0" src="<?php echo esc_url(self::$plusIcon); ?>" />
						<?php } ?>
					</span>
			</div>
			<?php
		}

		/**
		* Displays the group-specific-section in category pricing tab.
		* Gets the price type for the entry and set the css class accordingly
		* Gets the Group, category,discount options for that sections.
		* Prepare the HTML DOM for the group specific pricing section.
		 *
		* @param bool $addbutton true if last row then add the add new row
		* button
		* @param int $groupId group id in group-category-pricing table.
		* @param string $catSlug category slug for that user-id.
		* @param int $priceType price type for discount.
		* @param int $minQty minimum quantity.
		* @param float $value price of product.
		*/
		public function groupHtml( $addbutton = true, $groupId = '', $catSlug = '', $priceType = '', $minQty = '', $value = '') {
			$valueClasses = $this->isPercentValue($priceType) ? 'csp-percent-discount wdm_price' : 'wdm_price' ;
			$typeClasses  = 'csp_wdm_action';
			$qtyClasses   = 'wdm_qty';
			?>
			<div class="category-row group-row">
				<?php $this->generateGroupDropdown($groupId); ?>
				<select name='wdm_woo_group_category[]'>
				<?php $this->generateCategoryOptions($catSlug); ?>
				</select>
				<select class = "<?php echo esc_attr($typeClasses); ?>" name='wdm_group_price_type[]'>
				<?php $this->generateDiscountOptions($priceType); ?>
				</select>
					<input type="number" min="1" class = "<?php echo esc_attr($qtyClasses); ?>" name="wdm_group_qty[]" placeholder="Min Qty" value = "<?php echo esc_attr($minQty); ?>" />
					<input type="text" class = "<?php echo esc_attr($valueClasses); ?>" name="wdm_group_value[]" placeholder="Value" value = "<?php echo esc_attr($value); ?>" />
					<span class = "add_remove_button">
						<img class="remove_group_row_image" tabindex="0" alt="Remove Row" title="Remove Row" src="<?php echo esc_url(self::$minusIcon); ?>">
						<?php 
						if ($addbutton) {
							?>
							<img class="add_new_group_row_image" tabindex="0" src="<?php echo esc_url(self::$plusIcon); ?>" />
						<?php } ?>
					</span>
			</div>
			<?php
		}

		/**
		* Displays the user dropdown for category-user-specific dropdown.
		 *
		* @param string $user user-id in DB of the user dropdown in category * specific pricing.
		*/
		public function generateUserDropdown( $user = '') {
			global $wp_version;

			// Fall back for WordPress version below 4.5.0
			$show_user = 'user_login';

			if ($wp_version >= '4.5.0') {
				$show_user = 'display_name_with_login';
			}

			$userArgs = apply_filters('wdm_usp_user_dropdown_params', array(
				'show_option_all'        => null, // string
				'show_option_none'       => 'Select User', // string
				'hide_if_only_one_author'    => null, // string
				'orderby'            => 'display_name',
				'order'              => 'ASC',
				'include'            => null, // string
				'exclude'            => null, // string
				'multi'              => false,
				'show'               => $show_user,
				'echo'               => false,
				'selected'           => $user,
				'include_selected'       => false,
				'name'               => 'wdm_woo_username[]', // string
				'id'                 => null, // integer
				'class'              => 'chosen-select cat-select', // string
				'blog_id'            => $GLOBALS['blog_id'],
				'who'                => null, // string
				));

			echo wp_dropdown_users($userArgs);
		}

		/**
		* Displays the role dropdown for category-role-specific dropdown.
		 *
		* @param string $role selected role in the role dropdown in category * specific pricing.
		*
		* @SuppressWarnings(PHPMD.UnusedLocalVariable)
		*/
		public function generateRoleDropdown( $role = '') {
			$allowedHtml = array(
				'select'=> array(
								'name'=>true,
								'class'=>true
								),
				'option'=> array(
								'value'=>true,
								'selected'=>true,
								),
				);
			?>
			<select id = "wdm_woo_roles" name = 'wdm_woo_roles[]' class='cat-select'>
				<option value="-1"><?php esc_html_e('Select Role', 'customer-specific-pricing-for-woocommerce'); ?></option>
			<?php
				ob_start();
				$wdm_dropdown_content = wp_dropdown_roles($role);
				$wdm_roles_dropdown   = ob_get_contents();
				ob_end_clean();
				echo wp_kses($wdm_roles_dropdown, $allowedHtml);
			?>
			</select>
			<?php
		}

		/**
		* Displays the group dropdown for category-group-specific dropdown.
		 *
		* @param string $groupId group-id in DB of the group dropdown in
		* category specific pricing.
		*/
		public function generateGroupDropdown( $groupId) {
			global $wpdb;
			if ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->prefix . 'groups_group'))) {
				$array_of_groupid_name_pair = $wpdb->get_results('SELECT group_id, name FROM ' . $wpdb->prefix . 'groups_group');
			} else {
				$array_of_groupid_name_pair = array();
			}
			?>
			<select id = "wdm_woo_groups" name='wdm_woo_groupname[]' class='chosen-select cat-select'>
					<option value="-1"><?php esc_html_e('Select Group', 'customer-specific-pricing-for-woocommerce'); ?></option>
			<?php
			foreach ($array_of_groupid_name_pair as $single_groupid_name_pair) {
				if ($groupId == $single_groupid_name_pair->group_id) {
					echo '<option value=' . esc_attr($single_groupid_name_pair->group_id) . ' selected>' . esc_html($single_groupid_name_pair->name) . '</option>';
				} else {
					echo '<option value=' . esc_attr($single_groupid_name_pair->group_id) . ' >' . esc_html($single_groupid_name_pair->name) . '</option>';
				}
			}
			?>
			</select>
			<?php
		}

		/**
		* Set the options for the discount types for that entity.
		 *
		* @param int $discountType price-discount type 1 for flat, 2 for %.
		*/
		public function generateDiscountOptions( $discountType = -1) {
			foreach (self::$discountOptions as $key => $value) {
				if ($discountType == $key) {
					echo "<option value = '" . esc_attr($key) . "' selected>" . esc_html($value) . '</option>';
				} else {
					echo "<option value = '" . esc_attr($key) . "'>" . esc_html($value) . '</option>';
				}
			}
		}

		/**
		* Set the options for the categories of product for that entity.
		 *
		* @param string $catSlug category-slug for that row-id
		*/
		public function generateCategoryOptions( $catSlug = null) {
			?>
			<option value="-1">Select Category</option>
			<?php
			foreach (self::$product_categories as $category) {
				if ($catSlug == $category->slug) {
					echo "<option value = '" . esc_attr($category->slug) . "' selected>" . esc_html($category->name) . '</option>';
				} else {
					echo "<option value = '" . esc_attr($category->slug) . "'>" . esc_html($category->name) . '</option>';
				}
			}
		}
	}
}
