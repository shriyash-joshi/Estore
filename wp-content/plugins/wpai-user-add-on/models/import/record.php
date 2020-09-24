<?php

class PMUI_Import_Record extends PMUI_Model_Record {		

	/**
	 * Associative array of data which will be automatically available as variables when template is rendered
	 * @var array
	 */
	public $data = array();

	public $parsing_data = array();

	/**
	 * Initialize model instance
	 * @param array[optional] $data Array of record data to initialize object with
	 */
	public function __construct($data = array()) { 
		parent::__construct($data);
		$this->setTable(PMXI_Plugin::getInstance()->getTablePrefix() . 'imports');
	}	
	
	/**
	 * Perform import operation
	 * @param string $xml XML string to import
	 * @param callback[optional] $logger Method where progress messages are submmitted
	 * @return PMUI_Import_Record
	 * @chainable
	 */
	public function parse($parsing_data = array()) { //$import, $count, $xml, $logger = NULL, $chunk = false, $xpath_prefix = ""
	
		if ( !empty($parsing_data['import']->options['pmui']['import_users']) ) {

			add_filter('user_has_cap', array($this, '_filter_has_cap_unfiltered_html')); kses_init(); // do not perform special filtering for imported content			

			$cxpath = $parsing_data['xpath_prefix'] . $parsing_data['import']->xpath;

			$this->data = array();
			$records    = array();
			$tmp_files  = array();

			$parsing_data['chunk'] == 1 and $parsing_data['logger'] and call_user_func($parsing_data['logger'], __('Composing users...', 'wp_all_import_user_add_on'));
			
			$xml = $parsing_data['xml'];

			if ( ! empty($parsing_data['import']->options['pmui']['login']) ) {
				$this->data['pmui_logins'] = XmlImportParser::factory($xml, $cxpath, $parsing_data['import']->options['pmui']['login'], $file)->parse($records); $tmp_files[] = $file;				
			}
			else {
				$parsing_data['count'] and $this->data['pmui_logins'] = array_fill(0, $parsing_data['count'], '');
			}

			if ( ! empty($parsing_data['import']->options['pmui']['pass']) ) {
				$this->data['pmui_pass'] = XmlImportParser::factory($xml, $cxpath, $parsing_data['import']->options['pmui']['pass'], $file)->parse($records); $tmp_files[] = $file;				
			}
			else {
				$parsing_data['count'] and $this->data['pmui_pass'] = array_fill(0, $parsing_data['count'], '');
			}
			
			if ( ! empty($parsing_data['import']->options['is_hashed_wordpress_password']) ) {
				$this->data['is_hashed_wordpress_password'] = XmlImportParser::factory($xml, $cxpath, (string)$parsing_data['import']->options['is_hashed_wordpress_password'], $file)->parse($records); $tmp_files[] = $file;				
			}
			else {
				$parsing_data['count'] and $this->data['is_hashed_wordpress_password'] = array_fill(0, $parsing_data['count'], '');
			}
			
			if ( ! empty($parsing_data['import']->options['pmui']['nicename']) ) {
				$this->data['pmui_nicename'] = XmlImportParser::factory($xml, $cxpath, $parsing_data['import']->options['pmui']['nicename'], $file)->parse($records); $tmp_files[] = $file;				
			}
			else {
				$parsing_data['count'] and $this->data['pmui_nicename'] = array_fill(0, $parsing_data['count'], '');
			}

			if ( ! empty($parsing_data['import']->options['pmui']['email']) ) {
				$this->data['pmui_email'] = XmlImportParser::factory($xml, $cxpath, $parsing_data['import']->options['pmui']['email'], $file)->parse($records); $tmp_files[] = $file;				
			}
			else {
				$parsing_data['count'] and $this->data['pmui_email'] = array_fill(0, $parsing_data['count'], '');
			}

			if ( ! empty($parsing_data['import']->options['pmui']['registered']) ) {
				$this->data['pmui_registered'] = XmlImportParser::factory($xml, $cxpath, $parsing_data['import']->options['pmui']['registered'], $file)->parse($records); $tmp_files[] = $file;				
				$warned = array(); // used to prevent the same notice displaying several times
				foreach ($this->data['pmui_registered'] as $i => $d) {
					if ($d == 'now') $d = current_time('mysql'); // Replace 'now' with the WordPress local time to account for timezone offsets (WordPress references its local time during publishing rather than the server’s time so it should use that)
					$time = strtotime($d);
					if (FALSE === $time) {
						in_array($d, $warned) or $parsing_data['logger'] and call_user_func($parsing_data['logger'], sprintf(__('<b>WARNING</b>: unrecognized date format `%s`, assigning current date', 'wp_all_import_user_add_on'), $warned[] = $d));
						$time = time();
					}
					$this->data['pmui_registered'][$i] = date('Y-m-d H:i:s', $time);
				}
			}
			else {
				$parsing_data['count'] and $this->data['pmui_registered'] = array_fill(0, $parsing_data['count'], '');
			}

			if ( ! empty($parsing_data['import']->options['pmui']['display_name']) ) {
				$this->data['pmui_display_name'] = XmlImportParser::factory($xml, $cxpath, $parsing_data['import']->options['pmui']['display_name'], $file)->parse($records); $tmp_files[] = $file;				
			}
			else {
				$parsing_data['count'] and $this->data['pmui_display_name'] = array_fill(0, $parsing_data['count'], '');
			}

			if ( ! empty($parsing_data['import']->options['pmui']['url']) ) {
				$this->data['pmui_url'] = XmlImportParser::factory($xml, $cxpath, $parsing_data['import']->options['pmui']['url'], $file)->parse($records); $tmp_files[] = $file;				
			}
			else {
				$parsing_data['count'] and $this->data['pmui_url'] = array_fill(0, $parsing_data['count'], '');
			}

			if ( ! empty($parsing_data['import']->options['pmui']['first_name']) ) {
				$this->data['pmui_first_name'] = XmlImportParser::factory($xml, $cxpath, $parsing_data['import']->options['pmui']['first_name'], $file)->parse($records); $tmp_files[] = $file;				
			}
			else {
				$parsing_data['count'] and $this->data['pmui_first_name'] = array_fill(0, $parsing_data['count'], '');
			}

			if ( ! empty($parsing_data['import']->options['pmui']['last_name']) ) {
				$this->data['pmui_last_name'] = XmlImportParser::factory($xml, $cxpath, $parsing_data['import']->options['pmui']['last_name'], $file)->parse($records); $tmp_files[] = $file;				
			}
			else {
				$parsing_data['count'] and $this->data['pmui_last_name'] = array_fill(0, $parsing_data['count'], '');
			}

			if ( ! empty($parsing_data['import']->options['pmui']['description']) ) {
				$this->data['pmui_description'] = XmlImportParser::factory($xml, $cxpath, $parsing_data['import']->options['pmui']['description'], $file)->parse($records); $tmp_files[] = $file;				
			}
			else {
				$parsing_data['count'] and $this->data['pmui_description'] = array_fill(0, $parsing_data['count'], '');
			}

			if ( ! empty($parsing_data['import']->options['pmui']['nickname']) ) {
				$this->data['pmui_nickname'] = XmlImportParser::factory($xml, $cxpath, $parsing_data['import']->options['pmui']['nickname'], $file)->parse($records); $tmp_files[] = $file;				
			}
			else {
				$parsing_data['count'] and $this->data['pmui_nickname'] = array_fill(0, $parsing_data['count'], '');
			}

			if ( ! empty($parsing_data['import']->options['pmui']['role']) ) {
				if ( $parsing_data['import']->options['pmui']['role'] == 'xpath' ) {
				    if( ! empty($parsing_data['import']->options['pmui']['role_xpath']) ) {
                        $this->data['pmui_role'] = XmlImportParser::factory($xml, $cxpath, $parsing_data['import']->options['pmui']['role_xpath'], $file)->parse($records);
                        $tmp_files[] = $file;
                    }else{
                        $parsing_data['count'] and $this->data['pmui_role'] = array_fill(0, $parsing_data['count'], '');
                    }
				} else {
					$this->data['pmui_role'] = XmlImportParser::factory($xml, $cxpath, $parsing_data['import']->options['pmui']['role'], $file)->parse($records); $tmp_files[] = $file;
				}
			}
			else {
				$parsing_data['count'] and $this->data['pmui_role'] = array_fill(0, $parsing_data['count'], '');
			}
				
			remove_filter('user_has_cap', array($this, '_filter_has_cap_unfiltered_html')); kses_init(); // return any filtering rules back if they has been disabled for import procedure					

			foreach ($tmp_files as $file) { // remove all temporary files created
				@unlink($file);
			}
			
			return $this->data;
		
		} elseif ( !empty($parsing_data['import']->options['pmsci_customer']['import_customers']) ) {
		
			add_filter('user_has_cap', array($this, '_filter_has_cap_unfiltered_html')); kses_init(); // do not perform special filtering for imported content			

			$cxpath = $parsing_data['xpath_prefix'] . $parsing_data['import']->xpath;

			$this->data = array();
			$records    = array();
			$tmp_files  = array();

			$parsing_data['chunk'] == 1 and $parsing_data['logger'] and call_user_func($parsing_data['logger'], __('Composing customers...', 'wp_all_import_user_add_on'));
			
			$xml = $parsing_data['xml'];

			if ( ! empty($parsing_data['import']->options['pmsci_customer']['login']) ) {
				$this->data['pmsci_customer_logins'] = XmlImportParser::factory($xml, $cxpath, $parsing_data['import']->options['pmsci_customer']['login'], $file)->parse($records); $tmp_files[] = $file;				
			}
			else {
				$parsing_data['count'] and $this->data['pmsci_customer_logins'] = array_fill(0, $parsing_data['count'], '');
			}

			if ( ! empty($parsing_data['import']->options['pmsci_customer']['pass']) ) {
				$this->data['pmsci_customer_pass'] = XmlImportParser::factory($xml, $cxpath, $parsing_data['import']->options['pmsci_customer']['pass'], $file)->parse($records); $tmp_files[] = $file;				
			}
			else {
				$parsing_data['count'] and $this->data['pmsci_customer_pass'] = array_fill(0, $parsing_data['count'], '');
			}
			
			if ( ! empty($parsing_data['import']->options['is_hashed_wordpress_password']) ) {
				$this->data['is_hashed_wordpress_password'] = XmlImportParser::factory($xml, $cxpath, (string)$parsing_data['import']->options['is_hashed_wordpress_password'], $file)->parse($records); $tmp_files[] = $file;				
			}
			else {
				$parsing_data['count'] and $this->data['pmsci_customer']['is_hashed_wordpress_password'] = array_fill(0, $parsing_data['count'], '');
			}
			
			if ( ! empty($parsing_data['import']->options['pmsci_customer']['nicename']) ) {
				$this->data['pmsci_customer_nicename'] = XmlImportParser::factory($xml, $cxpath, $parsing_data['import']->options['pmsci_customer']['nicename'], $file)->parse($records); $tmp_files[] = $file;				
			}
			else {
				$parsing_data['count'] and $this->data['pmsci_customer_nicename'] = array_fill(0, $parsing_data['count'], '');
			}

			if ( ! empty($parsing_data['import']->options['pmsci_customer']['email']) ) {
				$this->data['pmsci_customer_email'] = XmlImportParser::factory($xml, $cxpath, $parsing_data['import']->options['pmsci_customer']['email'], $file)->parse($records); $tmp_files[] = $file;				
			}
			else {
				$parsing_data['count'] and $this->data['pmsci_customer_email'] = array_fill(0, $parsing_data['count'], '');
			}

			if ( ! empty($parsing_data['import']->options['pmsci_customer']['registered']) ) {
				$this->data['pmsci_customer_registered'] = XmlImportParser::factory($xml, $cxpath, $parsing_data['import']->options['pmsci_customer']['registered'], $file)->parse($records); $tmp_files[] = $file;				
				$warned = array(); // used to prevent the same notice displaying several times
				foreach ($this->data['pmsci_customer_registered'] as $i => $d) {
					if ($d == 'now') $d = current_time('mysql'); // Replace 'now' with the WordPress local time to account for timezone offsets (WordPress references its local time during publishing rather than the server’s time so it should use that)
					$time = strtotime($d);
					if (FALSE === $time) {
						in_array($d, $warned) or $logger and call_user_func($logger, sprintf(__('<b>WARNING</b>: unrecognized date format `%s`, assigning current date', 'wp_all_import_user_add_on'), $warned[] = $d));
						$time = time();
					}
					$this->data['pmsci_customer_registered'][$i] = date('Y-m-d H:i:s', $time);
				}
			}
			else {
				$parsing_data['count'] and $this->data['pmsci_customer_registered'] = array_fill(0, $parsing_data['count'], '');
			}

			if ( ! empty($parsing_data['import']->options['pmsci_customer']['display_name']) ) {
				$this->data['pmsci_customer_display_name'] = XmlImportParser::factory($xml, $cxpath, $parsing_data['import']->options['pmsci_customer']['display_name'], $file)->parse($records); $tmp_files[] = $file;				
			}
			else {
				$parsing_data['count'] and $this->data['pmsci_customer_display_name'] = array_fill(0, $parsing_data['count'], '');
			}

			if ( ! empty($parsing_data['import']->options['pmsci_customer']['url']) ) {
				$this->data['pmsci_customer_url'] = XmlImportParser::factory($xml, $cxpath, $parsing_data['import']->options['pmsci_customer']['url'], $file)->parse($records); $tmp_files[] = $file;				
			}
			else {
				$parsing_data['count'] and $this->data['pmsci_customer_url'] = array_fill(0, $parsing_data['count'], '');
			}

			if ( ! empty($parsing_data['import']->options['pmsci_customer']['first_name']) ) {
				$this->data['pmsci_customer_first_name'] = XmlImportParser::factory($xml, $cxpath, $parsing_data['import']->options['pmsci_customer']['first_name'], $file)->parse($records); $tmp_files[] = $file;				
			}
			else {
				$parsing_data['count'] and $this->data['pmsci_customer_first_name'] = array_fill(0, $parsing_data['count'], '');
			}

			if ( ! empty($parsing_data['import']->options['pmsci_customer']['last_name']) ) {
				$this->data['pmsci_customer_last_name'] = XmlImportParser::factory($xml, $cxpath, $parsing_data['import']->options['pmsci_customer']['last_name'], $file)->parse($records); $tmp_files[] = $file;				
			}
			else {
				$parsing_data['count'] and $this->data['pmsci_customer_last_name'] = array_fill(0, $parsing_data['count'], '');
			}

			if ( ! empty($parsing_data['import']->options['pmsci_customer']['description']) ) {
				$this->data['pmsci_customer_description'] = XmlImportParser::factory($xml, $cxpath, $parsing_data['import']->options['pmsci_customer']['description'], $file)->parse($records); $tmp_files[] = $file;				
			}
			else {
				$parsing_data['count'] and $this->data['pmsci_customer_description'] = array_fill(0, $parsing_data['count'], '');
			}

			if ( ! empty($parsing_data['import']->options['pmsci_customer']['nickname']) ) {
				$this->data['pmsci_customer_nickname'] = XmlImportParser::factory($xml, $cxpath, $parsing_data['import']->options['pmsci_customer']['nickname'], $file)->parse($records); $tmp_files[] = $file;				
			}
			else {
				$parsing_data['count'] and $this->data['pmsci_customer_nickname'] = array_fill(0, $parsing_data['count'], '');
			}

			if ( ! empty($parsing_data['import']->options['pmsci_customer']['role']) ) {
				$this->data['pmsci_customer_role'] = XmlImportParser::factory($xml, $cxpath, $parsing_data['import']->options['pmsci_customer']['role'], $file)->parse($records); $tmp_files[] = $file;				
			}
			else {
				$parsing_data['count'] and $this->data['pmsci_customer_role'] = array_fill(0, $parsing_data['count'], '');
			}
			
			// Billing Fields
			
			// Billing First Name
			if ( ! empty($parsing_data['import']->options['pmsci_customer']['billing_first_name']) ) {
				$this->data['pmsci_customer_billing_first_name'] = XmlImportParser::factory($xml, $cxpath, $parsing_data['import']->options['pmsci_customer']['billing_first_name'], $file)->parse($records); $tmp_files[] = $file;
			}
			else {
				$parsing_data['count'] and $this->data['pmsci_customer_billing_first_name'] = array_fill(0, $parsing_data['count'], '');
			}
			
			// Billing Last Name
			if ( ! empty($parsing_data['import']->options['pmsci_customer']['billing_last_name']) ) {
				$this->data['pmsci_customer_billing_last_name'] = XmlImportParser::factory($xml, $cxpath, $parsing_data['import']->options['pmsci_customer']['billing_last_name'], $file)->parse($records); $tmp_files[] = $file;
			}
			else {
				$parsing_data['count'] and $this->data['pmsci_customer_billing_last_name'] = array_fill(0, $parsing_data['count'], '');
			}
			
			// Billing Company
			if ( ! empty($parsing_data['import']->options['pmsci_customer']['billing_company']) ) {
				$this->data['pmsci_customer_billing_company'] = XmlImportParser::factory($xml, $cxpath, $parsing_data['import']->options['pmsci_customer']['billing_company'], $file)->parse($records); $tmp_files[] = $file;
			}
			else {
				$parsing_data['count'] and $this->data['pmsci_customer_billing_company'] = array_fill(0, $parsing_data['count'], '');
			}
			
			// Billing Address 1
			if ( ! empty($parsing_data['import']->options['pmsci_customer']['billing_address_1']) ) {
				$this->data['pmsci_customer_billing_address_1'] = XmlImportParser::factory($xml, $cxpath, $parsing_data['import']->options['pmsci_customer']['billing_address_1'], $file)->parse($records); $tmp_files[] = $file;
			}
			else {
				$parsing_data['count'] and $this->data['pmsci_customer_billing_address_1'] = array_fill(0, $parsing_data['count'], '');
			}
			
			// Billing Address 2
			if ( ! empty($parsing_data['import']->options['pmsci_customer']['billing_address_2']) ) {
				$this->data['pmsci_customer_billing_address_2'] = XmlImportParser::factory($xml, $cxpath, $parsing_data['import']->options['pmsci_customer']['billing_address_2'], $file)->parse($records); $tmp_files[] = $file;
			}
			else {
				$parsing_data['count'] and $this->data['pmsci_customer_billing_address_2'] = array_fill(0, $parsing_data['count'], '');
			}
			
			// Billing City
			if ( ! empty($parsing_data['import']->options['pmsci_customer']['billing_city']) ) {
				$this->data['pmsci_customer_billing_city'] = XmlImportParser::factory($xml, $cxpath, $parsing_data['import']->options['pmsci_customer']['billing_city'], $file)->parse($records); $tmp_files[] = $file;
			}
			else {
				$parsing_data['count'] and $this->data['pmsci_customer_billing_city'] = array_fill(0, $parsing_data['count'], '');
			}
			
			// Billing Postcode
			if ( ! empty($parsing_data['import']->options['pmsci_customer']['billing_postcode']) ) {
				$this->data['pmsci_customer_billing_postcode'] = XmlImportParser::factory($xml, $cxpath, $parsing_data['import']->options['pmsci_customer']['billing_postcode'], $file)->parse($records); $tmp_files[] = $file;
			}
			else {
				$parsing_data['count'] and $this->data['pmsci_customer_billing_postcode'] = array_fill(0, $parsing_data['count'], '');
			}
			
			// Billing Country
			if ( ! empty($parsing_data['import']->options['pmsci_customer']['billing_country']) ) {
				$this->data['pmsci_customer_billing_country'] = XmlImportParser::factory($xml, $cxpath, $parsing_data['import']->options['pmsci_customer']['billing_country'], $file)->parse($records); $tmp_files[] = $file;
			}
			else {
				$parsing_data['count'] and $this->data['pmsci_customer_billing_country'] = array_fill(0, $parsing_data['count'], '');
			}
			
			// Billing State
			if ( ! empty($parsing_data['import']->options['pmsci_customer']['billing_state']) ) {
				$this->data['pmsci_customer_billing_state'] = XmlImportParser::factory($xml, $cxpath, $parsing_data['import']->options['pmsci_customer']['billing_state'], $file)->parse($records); $tmp_files[] = $file;
			}
			else {
				$parsing_data['count'] and $this->data['pmsci_customer_billing_state'] = array_fill(0, $parsing_data['count'], '');
			}
			
			// Billing Phone
			if ( ! empty($parsing_data['import']->options['pmsci_customer']['billing_phone']) ) {
				$this->data['pmsci_customer_billing_phone'] = XmlImportParser::factory($xml, $cxpath, $parsing_data['import']->options['pmsci_customer']['billing_phone'], $file)->parse($records); $tmp_files[] = $file;
			}
			else {
				$parsing_data['count'] and $this->data['pmsci_customer_billing_phone'] = array_fill(0, $parsing_data['count'], '');
			}
			
			// Billing Email
			if ( ! empty($parsing_data['import']->options['pmsci_customer']['billing_email']) ) {
				$this->data['pmsci_customer_billing_email'] = XmlImportParser::factory($xml, $cxpath, $parsing_data['import']->options['pmsci_customer']['billing_email'], $file)->parse($records); $tmp_files[] = $file;
			}
			else {
				$parsing_data['count'] and $this->data['pmsci_customer_billing_email'] = array_fill(0, $parsing_data['count'], '');
			}
			
			// Shipping Fields
			
			// Shipping First Name
			if ( ! empty($parsing_data['import']->options['pmsci_customer']['shipping_first_name']) ) {
				$this->data['pmsci_customer_shipping_first_name'] = XmlImportParser::factory($xml, $cxpath, $parsing_data['import']->options['pmsci_customer']['shipping_first_name'], $file)->parse($records); $tmp_files[] = $file;
			}
			else {
				$parsing_data['count'] and $this->data['pmsci_customer_shipping_first_name'] = array_fill(0, $parsing_data['count'], '');
			}
			
			// Shipping Last Name
			if ( ! empty($parsing_data['import']->options['pmsci_customer']['shipping_last_name']) ) {
				$this->data['pmsci_customer_shipping_last_name'] = XmlImportParser::factory($xml, $cxpath, $parsing_data['import']->options['pmsci_customer']['shipping_last_name'], $file)->parse($records); $tmp_files[] = $file;
			}
			else {
				$parsing_data['count'] and $this->data['pmsci_customer_shipping_last_name'] = array_fill(0, $parsing_data['count'], '');
			}
			
			// Shipping Company
			if ( ! empty($parsing_data['import']->options['pmsci_customer']['shipping_company']) ) {
				$this->data['pmsci_customer_shipping_company'] = XmlImportParser::factory($xml, $cxpath, $parsing_data['import']->options['pmsci_customer']['shipping_company'], $file)->parse($records); $tmp_files[] = $file;
			}
			else {
				$parsing_data['count'] and $this->data['pmsci_customer_shipping_company'] = array_fill(0, $parsing_data['count'], '');
			}
			
			// Shipping Address 1
			if ( ! empty($parsing_data['import']->options['pmsci_customer']['shipping_address_1']) ) {
				$this->data['pmsci_customer_shipping_address_1'] = XmlImportParser::factory($xml, $cxpath, $parsing_data['import']->options['pmsci_customer']['shipping_address_1'], $file)->parse($records); $tmp_files[] = $file;
			}
			else {
				$parsing_data['count'] and $this->data['pmsci_customer_shipping_address_1'] = array_fill(0, $parsing_data['count'], '');
			}
			
			// Shipping Address 2
			if ( ! empty($parsing_data['import']->options['pmsci_customer']['shipping_address_2']) ) {
				$this->data['pmsci_customer_shipping_address_2'] = XmlImportParser::factory($xml, $cxpath, $parsing_data['import']->options['pmsci_customer']['shipping_address_2'], $file)->parse($records); $tmp_files[] = $file;
			}
			else {
				$parsing_data['count'] and $this->data['pmsci_customer_shipping_address_2'] = array_fill(0, $parsing_data['count'], '');
			}
			
			// Shipping City
			if ( ! empty($parsing_data['import']->options['pmsci_customer']['shipping_city']) ) {
				$this->data['pmsci_customer_shipping_city'] = XmlImportParser::factory($xml, $cxpath, $parsing_data['import']->options['pmsci_customer']['shipping_city'], $file)->parse($records); $tmp_files[] = $file;
			}
			else {
				$parsing_data['count'] and $this->data['pmsci_customer_shipping_city'] = array_fill(0, $parsing_data['count'], '');
			}
			
			// Shipping Postcode
			if ( ! empty($parsing_data['import']->options['pmsci_customer']['shipping_postcode']) ) {
				$this->data['pmsci_customer_shipping_postcode'] = XmlImportParser::factory($xml, $cxpath, $parsing_data['import']->options['pmsci_customer']['shipping_postcode'], $file)->parse($records); $tmp_files[] = $file;
			}
			else {
				$parsing_data['count'] and $this->data['pmsci_customer_shipping_postcode'] = array_fill(0, $parsing_data['count'], '');
			}
			
			// Shipping Country
			if ( ! empty($parsing_data['import']->options['pmsci_customer']['shipping_country']) ) {
				$this->data['pmsci_customer_shipping_country'] = XmlImportParser::factory($xml, $cxpath, $parsing_data['import']->options['pmsci_customer']['shipping_country'], $file)->parse($records); $tmp_files[] = $file;
			}
			else {
				$parsing_data['count'] and $this->data['pmsci_customer_shipping_country'] = array_fill(0, $parsing_data['count'], '');
			}
			
			// Shipping State
			if ( ! empty($parsing_data['import']->options['pmsci_customer']['shipping_state']) ) {
				$this->data['pmsci_customer_shipping_state'] = XmlImportParser::factory($xml, $cxpath, $parsing_data['import']->options['pmsci_customer']['shipping_state'], $file)->parse($records); $tmp_files[] = $file;
			}
			else {
				$parsing_data['count'] and $this->data['pmsci_customer_shipping_state'] = array_fill(0, $parsing_data['count'], '');
			}
			

			remove_filter('user_has_cap', array($this, '_filter_has_cap_unfiltered_html')); kses_init(); // return any filtering rules back if they has been disabled for import procedure					

			foreach ($tmp_files as $file) { // remove all temporary files created
				@unlink($file);
			}
			
			return $this->data;
		
		} else {
			
			return;
			
		}
		
	}

	public function import($importData = array()){ //$pid, $i, $import, $articleData, $xml, $is_cron = false, $xpath_prefix = ""

		if ( !in_array($importData['import']->options['custom_type'], array('import_users', 'shop_customer')) ) return;

		if (empty($importData['articleData']['ID']) and empty($importData['import']->options['do_not_send_password_notification']))
		{
			// Welcome Email

			global $wp_version;

			if (version_compare($wp_version, '4.3.1') < 0)
			{
				wp_new_user_notification( $importData['pid'], 'both' );
			}
			else
			{
				wp_new_user_notification( $importData['pid'], null, 'both' );
			}
		}
		
		if ($importData['import']->options['custom_type'] == 'import_users') {
			// import multiple user roles
			if (
				// This is a new user
				(empty($importData['articleData']['ID'])
				and
				!empty($importData['articleData']['role'])
				)
				or
				// This is an existing user, and we can update the role
				($importData['import']->options['is_update_role'] == '1'
				and
				!empty($importData['articleData']['role']) // no need to run as default will already be set
				)
			){
				
				$roles_to_import = explode("|", $importData['articleData']['role']);
				$roles_array = array();
				
				foreach($roles_to_import as $key => $value)
				{
					$roles_array[trim($value)] = true;
				}
				
				update_user_meta($importData['pid'], $this->wpdb->prefix . 'capabilities', $roles_array);
				
			}
		}
		
		if (
			// This is a new user
	    	(empty($importData['articleData']['ID'])
			// The 'is_hashed_wordpress_password' variable is present
	    	and isset($importData['import']->options['is_hashed_wordpress_password'])
			// The 'is_hashed_wordpress_password' option is enabled
			and $importData['import']->options['is_hashed_wordpress_password'] == '1')
			or
			// This is an existing user, and we can update the password
			($importData['import']->options['is_update_password'] == '1'
			// The 'is_hashed_wordpress_password' variable is present
			and isset($importData['import']->options['is_hashed_wordpress_password'])
			// The 'is_hashed_wordpress_password' option is enabled
			and $importData['import']->options['is_hashed_wordpress_password'] == '1')
    	){

			$user_pass_hash = $importData['articleData']['user_pass'];
		
			global $wpdb;

			$table = $wpdb->base_prefix . 'users';
			$wpdb->query( $wpdb->prepare(
				"
				UPDATE `" . $table . "`
				SET `user_pass` = %s
				WHERE `ID` = %d
				",
				$user_pass_hash,
				$importData['pid']
			) );
		
		}

	}

	public function _filter_has_cap_unfiltered_html($caps)
	{
		$caps['unfiltered_html'] = true;
		return $caps;
	}
	
	public function filtering($var){
		return ("" == $var) ? false : true;
	}		
}
