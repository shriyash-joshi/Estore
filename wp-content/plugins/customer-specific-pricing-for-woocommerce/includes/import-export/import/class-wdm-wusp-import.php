<?php

namespace cspImportExport\cspImport;

/**
 * Check if class already exists
 *
 *  @author WisdmLabs
 */
if (!class_exists('WdmWuspImport')) {
	/**
	 * Provides import option for importing customer specific,role specific and group specific csv files
	 */
	class WdmWuspImport {
	

		//  @var array save the optins value pairs
		private $_class_value_pairs = array();

		/**
		 * Call function for display import form
		 */
		public function __construct() {
			add_action('show_import', array($this, 'wdmShowImportOptions'));
		}

		/**
		 * Set the option value pairs
		 *
		 * @param array $class_value_pairs
		 */
		public function setOptionValuesPair( $class_value_pairs) {
			$this->_class_value_pairs = $class_value_pairs;
		}

		/**
		* Enqueue scripts and styles for imports actions.
		* Prepare the data for localization.
		*/
		private function enqueueScripts() {
			wp_enqueue_style('wdm_csp_import_css', plugins_url('/css/import-css/wdm-csp-import.css', dirname(dirname(dirname(__FILE__)))), array(), CSP_VERSION);
			wp_enqueue_style('bootstrap_fileinput_css', plugins_url('/css/import-css/fileinput.min.css', dirname(dirname(dirname(__FILE__)))), array(), CSP_VERSION);
			wp_enqueue_script('bootstrap_fileinput_js', plugins_url('/js/import-js/fileinput.js', dirname(dirname(dirname(__FILE__)))), array('jquery'), CSP_VERSION, true);
			wp_enqueue_style('bootstrap_css', plugins_url('/css/import-css/bootstrap.css', dirname(dirname(dirname(__FILE__)))), array(), CSP_VERSION);
			wp_enqueue_script('bootstrap_js', plugins_url('/js/import-js/bootstrap.min.js', dirname(dirname(dirname(__FILE__)))), array('jquery'), CSP_VERSION, true);

			wp_localize_script(
				'bootstrap_fileinput_js',
				'wdm_csp_translation',
				array(
					'removeLabel' => __('Remove', 'customer-specific-pricing-for-woocommerce'),
					'removeTitle' => __('Clear selected files', 'customer-specific-pricing-for-woocommerce'),
					/* translators: browsLabel*/
					'browseLabel' => sprintf(__('Browse %s', 'customer-specific-pricing-for-woocommerce'), '&hellip;'),
					'uploadLabel' => __('Upload', 'customer-specific-pricing-for-woocommerce'),
					'uploadTitle' => __('Upload selected files', 'customer-specific-pricing-for-woocommerce'),
					/* translators: msgSizeTooLarge*/
					'msgSizeTooLarge' => sprintf(__('File "%1$s" %2$s exceeds maximum allowed upload size of %3$s. Please retry your upload!', 'customer-specific-pricing-for-woocommerce'), '{name}', '(<b>{size} KB</b>)', '<b>{maxSize} KB</b>'),
					/* translators: msgFilesTooLess*/
					'msgFilesTooLess' => sprintf(__('You must select at least %s to upload. Please retry your upload!', 'customer-specific-pricing-for-woocommerce'), '<b>{n}</b> {files}'),
					/* translators: msgFileNotFound*/
					'msgFileNotFound' => sprintf(__('File "%s" not found!', 'customer-specific-pricing-for-woocommerce'), '{name}'),
					/* translators: msgFileSecured*/
					'msgFileSecured' => sprintf(__('Security restrictions prevent reading the file "%s".', 'customer-specific-pricing-for-woocommerce'), '{name}'),
					/* translators: msgFileNotReadable*/
					'msgFileNotReadable' => sprintf(__('File "%s" is not readable.', 'customer-specific-pricing-for-woocommerce'), '{name}'),
					/* translators: msgInvalidFileType*/
					'msgInvalidFileType' => sprintf(__('Invalid type for file "%1$s". Only "%2$s" files are supported.', 'customer-specific-pricing-for-woocommerce'), '{name}', '{types}'),
					/* translators: msgInvalidFileExtension*/
					'msgInvalidFileExtension'=> sprintf(__('Invalid extension for file "%1$s". Only "%2$s" files are supported.', 'customer-specific-pricing-for-woocommerce'), '{name}', '{extensions}'),
					/* translators: msgValidationError*/
					'msgValidationError' => __('File Upload Error', 'customer-specific-pricing-for-woocommerce'),
					/* translators: dropZoneTitle*/
					'dropZoneTitle' => sprintf(__('Drag & drop files here %s', 'customer-specific-pricing-for-woocommerce'), '&hellip;'),
					/* translators: msgSelected*/
					'msgSelected' => sprintf(__('%s selected', 'customer-specific-pricing-for-woocommerce'), '{n} {files}'),
				)
			);
		}

		/**
		 * Display import form
		 *
		 * @global type $post
		 */
		public function wdmShowImportOptions() {
			$this->enqueueScripts();

			?>
			<div class="wrap"><h3 class="import-export-header import-header"><?php esc_html_e('Import Pricing Rules', 'customer-specific-pricing-for-woocommerce'); ?></h3>
				<div id='wdm_message' class='updated hidePrev'><p class="wdm_message_p"></p></div>
			</div>

			<div id='wdm_import_form'>
				<form name="import_form" class="wdm_import_form" method="POST" enctype="multipart/form-data">
				<div class="row">
					<div class="col-md-6">
				<?php
					wp_nonce_field('import_upload_nonce');
					$this->showImportByfields();
				?>
					<div class="wdm-input-group">
					<label for="dd_show_import_options"><?php esc_html_e('Import Type', 'customer-specific-pricing-for-woocomerce'); ?> </label>
								<select name="dd_show_import_options" id="dd_show_import_options">
									<?php
									foreach ($this->_class_value_pairs as $key => $val) {
										echo '<option value=' . esc_attr($key) . '>' . esc_html($val) . '</option>';
									}
									?>
								</select>
								<a class='sample-csv-import-template-link' target="_blank" href="<?php echo esc_url(plugins_url('/templates/user_specific_pricing_sample.csv', dirname(dirname(dirname(__FILE__))))); ?>"><?php esc_html_e('Sample File', 'customer-specific-pricing-for-woocomerce'); ?></a>
					</div>
					<input type="file" name="csv" id="csv" class="file" accept=".csv" data-show-preview="false" data-show-upload="false" required title="<?php esc_attr_e('Select File', 'customer-specific-pricing-for-woocommerce'); ?>">
					<div class="wdm-input-group">
						<input type="submit" id="wdm_import" name="wdm_import_csp" class="button button-primary" value="<?php esc_attr_e('Import', 'customer-specific-pricing-for-woocommerce'); ?>">
					</div>
					<div class="wdm-input-group import-message-info">
						<br>
						<i>
							<?php echo esc_html__('If the customer specific price already exists, the existing values will be overwritten by the new values. While importing using SKU please make sure that all products have SKUs before import', 'customer-specific-pricing-for-woocommerce'); ?>
						</i>
					</div>
					</div> <!--BS row column 1 end-->
					
					<?php
						$BatchSize           =get_option('dd_import_batch_size')?get_option('dd_import_batch_size'):'1000';
						$SimultaneousBatches =get_option('dd_simultaneous_threads')?get_option('dd_simultaneous_threads'):'2';
						$helpInfo            = sprintf('<a id="import-help-text" href="#" data-placement="auto" data-trigger="hover" data-toggle="popover" data-html="true" title="%s" >%s</a>', __('Recommended Settings for Importing Large Files', 'customer-specific-pricing-for-woocommerce'), __('Help ?', 'customer-specific-pricing-for-woocommerce'));
						$allowedHtml         = array('a'=>array(
							'href'=>array(),
							'data-placement'=>array(), 'data-trigger'=>array(),
							'data-toggle'=>array(),
							'data-html'=>array(),
							'title'=>array(),
							'id'=>array(),
							)
						);
						?>
						<div class="wdm-input-group col-md-6 wdm-import-notes">
							<div style="text-align: justify;">
							<b><?php esc_html_e('Note:', 'customer-specific-pricing-for-woocommerce'); ?></b>
							<br>
							<?php 
								esc_html_e('For a large CSV file, kindly consider splitting the entries by deciding the number of splits (simultaneous batches) and capacity of each split (records in a batch).', 'customer-specific-pricing-for-woocommerce'); 
								echo ' [' . wp_kses($helpInfo, $allowedHtml) . ']';	
							?>
							</div>
							<br>
							<label for="dd_import_batch_size"><?php esc_html_e('Records In a Batch ', 'customer-specific-pricing-for-woocommerce'); ?> </label>
							<input type="number" min="100" name="dd_import_batch_size" id="dd_import_batch_size" placeholder=" 1000 (Recommended)" value="<?php echo esc_attr($BatchSize); ?>">
									<br/><br/>
							<label for="dd_simultaneous_threads"><?php esc_html_e('Simultaneous Batches ', 'customer-specific-pricing-for-woocommerce'); ?> </label>
							<input type="number" min="1" max="5" name="dd_simultaneous_threads" id="dd_simultaneous_threads" placeholder="2 (Recommended)" value="<?php echo esc_attr($SimultaneousBatches); ?>">
					</div>
					</div><!-- BS row closed -->
				</form>
			</div>
			<div id="wdm_import_data">
				<?php
				$postArray = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
				if (! empty($postArray)) {
					$this->createBatches($postArray);
				}
				?>
			</div>
			<?php
		}

		public function showImportByfields() {
			?>
			<div class="wdm-input-group">
			<label for="dd_show_import_options">
			<?php
			esc_html_e('Import Using', 'customer-specific-pricing-for-woocommerce');
			?>
			</label>                
				<fieldset class = "wdm_csp_fieldset">
					<legend class="screen-reader-text">
					</legend>
					<ul class = "wdm_csp_ul">
						<li>
							<label>
								<input type="radio" class="wdm_csp_import_using" name="wdm_csp_import_using" value="product id" class="" checked="checked">
								<span class="wdm_span_label">Product Id</span>
							</label>
						</li>
						<li>
							<label>
								<input type="radio" class="wdm_csp_import_using" name="wdm_csp_import_using" value="sku" class="">
								<span class="wdm_span_label">SKU</span>
							</label>
						</li>
					</ul>
				</fieldset>
			</div>
			<?php
		}
		/**
		 * Divides CSV in small csv files i.e. batches and uploads them in uploads/importCSV folder. After creating a batch, it triggers
		 * its processing by calling the javascript function senddata().
		 *
		 */
		private function createBatches( $post) {
			wp_suspend_cache_addition(true);
			$fileType           = '';
			$nonce_verification = check_admin_referer('import_upload_nonce', '_wpnonce');
			//Override nonce verification for extending import functionality in any third party extension
			$nonce_verification = apply_filters('csp_import_upload_nonce_verification', $nonce_verification);
			if (! $nonce_verification) {
				echo 'Security Check';
				exit;
			}

			if (isset($post['wdm_import_csp'])) {
				//Allow only admin to import csv files
				$capabilityToUpload = apply_filters('csp_import_allowed_user_upload', 'manage_options');
				$canUserUpload      = apply_filters('csp_can_user_upload_csv', current_user_can($capabilityToUpload));
				if (!$canUserUpload) {
					echo 'Security Check';
					exit;
				}

				$csvFile = $this->wdmGetFileIfValid();

				$uniqueId = isset($post['wdm_csp_import_using']) ? $post['wdm_csp_import_using'] : 'product id';
				//$csv = array();
				$fptr      = fopen($csvFile, 'r');
				$firstLine = fgets($fptr); //get first line of csv file
				fclose($fptr);
				$foundHeaders = str_getcsv(trim($firstLine), ',', '"'); //parse to array
				//Remove unwanted spaces from header
				$foundHeaders   = array_map('trim', $foundHeaders);
				$fileUploadType = $post['dd_show_import_options'];
				$filePointer    = file($csvFile);
				$noOfRecords    = count($filePointer) - 1;

				//get and save import options
				$BatchSize           = $post['dd_import_batch_size'];
				$SimultaneousBatches =$post['dd_simultaneous_threads'];
				if (!empty($BatchSize)) {
					update_option('dd_import_batch_size', $BatchSize);
				}
				if (!empty($SimultaneousBatches)) {
					update_option('dd_simultaneous_threads', $SimultaneousBatches);
				}
				
				//for checking selected file is valid or not.
				$fileType = $this->checkFileType($fileUploadType, $foundHeaders, $uniqueId);
				if (!$fileType) {
					return;
				}
				$this->displayImportProcessStatus($fileType, $uniqueId, $noOfRecords);

				//delete batch column if already exists
				$cspAjaxObject = new \cspAjax\WdmWuspAjax();
				$cspAjaxObject->deleteBatchColumn($fileType);
			
				//Add batch column in table for importing records in the sequence they occur
				$this->addBatchColumn($fileType);

				$this->wdmCreateAndStoreBatchesFromMainImportFile($csvFile, $uniqueId, $fileType);
			}
		}


		/**
		 * This method splits up single large csv import file to 'N' batches
		 * where each batch contains max. number of records specified by $batchsize
		 * & hooked to 'csp_import_batch_size' filter.
		 * all method stores all these files to the  directory specified by $batchDir
		 * stores list of batches in an array $batchJsCallArray which is used to make
		 * an ajax import call from frontend javascript.
		 *
		 * @param [type] $csvFile - File to be imported
		 * @param [type] $uniqueId
		 * @param [type] $fileType
		 * @return void
		 */
		private function wdmCreateAndStoreBatchesFromMainImportFile( $csvFile, $uniqueId, $fileType) {
			global $cspFunctions;
			$batchDir         = $this->wdmGetOrCreateUploadDirectory('importCsv');
			$batchDir         = $this->wdmClearDirectory($batchDir);
			$batchsize        = $this->wdmGetBatchSize();
			$batchJsCallArray =array();
			$recordsToStore   =array();
			$handle           = fopen($csvFile, 'r');
			if ( false!==$handle) {
				$firstLineHeader   = fgetcsv($handle, 0, ',');
				$firstLineHeader[] ='Import Status';
				$recordsToStore[]  = $firstLineHeader;
				set_time_limit(0);
				$row         = 0;
				$batchNumber = 0;
				$file        = null;
				while (( $data = fgetcsv($handle) ) !== false) {
					$data = array_map('trim', $data);
					//splitting of CSV file :
					if (0 == $row % $batchsize) {
						//closing the previous file handler
						if (null!= $file ) {
							fclose($file);
						}
						$newBatch = "minpoints$row.csv";
						$fileName = $batchDir . '/' . $newBatch;
						$file     = fopen($fileName, 'w');
					}
					$productId        = isset($data[0]) ? $data[0] : '';
					$applicableEntity = isset($data[1]) ? $data[1] : '';
					$minQty           = isset($data[2]) ? $data[2] : '';
					
					$flatPrice    = isset($data[3]) ? $data[3] : '';
					$percentPrice = isset($data[4]) ? $data[4] : '';
					$json         = "$productId, $applicableEntity, $minQty, $flatPrice, $percentPrice";
					fwrite($file, $json . PHP_EOL);
					//sending the splitted CSV files, batch by batch...
					if (0 == $row % $batchsize) {
						$batchNumber++;
						array_push($batchJsCallArray, array('batchName'=>"$newBatch",
															'fileType'=>"$fileType",
															'batchNo'=>$batchNumber,
															'uniqueId'=>"$uniqueId"));
					}
					$row++;
				}

				
				$reportsDir = $this->wdmGetOrCreateUploadDirectory('cspReports');
				$reportsDir = $this->wdmClearDirectory($reportsDir);
				$this->wdmcallImportAjaxRequests($batchJsCallArray);
				$cspFunctions->wdmSaveCSV('batch0', $recordsToStore, 'cspReports');
				
				unset($firstLineHeader);
				fclose($handle);
			}
		}


		/**
		 * Returns Value of the batch size.
		 */
		private function wdmGetBatchSize() {
			$batchsize = apply_filters('csp_import_batch_size', 1000);
			//split huge CSV file by BatchSize we can modify this based on the need
			$batchsize =get_option('dd_import_batch_size')?get_option('dd_import_batch_size'):$batchsize;
			return $batchsize;
		}


		private function wdmcallImportAjaxRequests( $batchJsCallArray) {
			$noOfSimultaneousBatchImport =2;
			$noOfSimultaneousBatchImport =apply_filters('wdm_no_of_import_threads', $noOfSimultaneousBatchImport);
			$noOfSimultaneousBatchImport =get_option('dd_simultaneous_threads')?get_option('dd_simultaneous_threads'):$noOfSimultaneousBatchImport;
			echo '<script> var batchImportList=' . json_encode($batchJsCallArray) . ';
                        confirmationBeforeLeavingPage();    
                        batchDataSender();
                        </script>';
 
			if ($noOfSimultaneousBatchImport>0) {
				while (0<( --$noOfSimultaneousBatchImport )) {
					echo '<script> batchDataSender();</script>';
				}
			}
		}

		/**
		 * This method checks if uploaded file is valid,
		 * in case of invalid file displays message & exists
		 * returns the file object in case of the valid file.
		 *
		 * @return void
		 */
		private function wdmGetFileIfValid() {
			$fileTypeCheck = apply_filters('wdm_csp_enable_import_file_type_check', true);
			$fileName = '';
			
			if (empty($_FILES['csv']['error'])) {
				$fileName = isset($_FILES['csv']['tmp_name'])?sanitize_text_field($_FILES['csv']['tmp_name']):'';
			}

			if ($fileTypeCheck) {
				$mimetype = mime_content_type($fileName);
				if (!in_array($mimetype, array('csv', 'text/plain'))) {
					echo '<div class="wdm_message_p error"><p>' . esc_html__('Please Select valid file type', 'customer-specific-pricing-for-woocommerce') . '</p></div>';
					exit();
				}
			}
			return $fileName;
		}

		/**
		 * This method generated directory path for the csv upload directory
		 * Checks if that directory exists if it does not exists create the directory
		 * if it's exists unlink all the previous files in the directory & returns
		 * the directory path.
		 *
		 */
		private function wdmGetOrCreateUploadDirectory( $dirName) {
			// generate upload dir path
			$upload   = wp_upload_dir();
			$batchDir = $upload['basedir'] . "/$dirName";

			/*Creating importCsv dir if not exist
			in uploads dir to save batch/chunks files*/
			if (!file_exists($batchDir)) {
				wp_mkdir_p($batchDir);
			}
		
			return $batchDir;
		}

		/**
		 * Removes all the existing files in the directory
		 * specified by the parameter
		 *
		 * @since 4.3.0
		 * @param [type] $batchDir - path of the directory to be cleared up
		 * @return string directory path which is cleared
		 */
		private function wdmClearDirectory( $batchDir) {
			$all_files = glob($batchDir . '/*.csv');
			if ($all_files) {
				foreach ($all_files as $file) {
					unlink($file);
				}
			}
			return $batchDir;
		}


		/**
		 * Creates column in tables to hold batch number temporarily
		 *
		 * @param string $fileType Type of import
		 */
		public function addBatchColumn( $fileType) {
			global $wpdb;
			$cspTable = '';


			switch ($fileType) {
				case 'user':
					$cspTable = $wpdb->prefix . 'wusp_user_pricing_mapping';
					// Get the columns of the table
					$existingColumn = $wpdb->get_var($wpdb->prepare('SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = %s AND column_name = "batch_numbers"', $cspTable));
					if (empty($existingColumn)) {
						$wpdb->query('ALTER TABLE ' . $wpdb->prefix . 'wusp_user_pricing_mapping ADD batch_numbers TINYINT(1) UNSIGNED NOT NULL DEFAULT 0');
					}
					break;
				case 'role':
					$cspTable = $wpdb->prefix . 'wusp_role_pricing_mapping';
					// Get the columns of the table
					$existingColumn = $wpdb->get_var($wpdb->prepare('SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = %s AND column_name = "batch_numbers"', $cspTable));
					if (empty($existingColumn)) {
						$wpdb->query('ALTER TABLE ' . $wpdb->prefix . 'wusp_role_pricing_mapping ADD batch_numbers TINYINT(1) UNSIGNED NOT NULL DEFAULT 0');
					}
					break;
				case 'group':
					$cspTable = $wpdb->prefix . 'wusp_group_product_price_mapping';
					// Get the columns of the table
					$existingColumn = $wpdb->get_var($wpdb->prepare('SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = %s AND column_name = "batch_numbers"', $cspTable));
					if (empty($existingColumn)) {
						$wpdb->query('ALTER TABLE ' . $wpdb->prefix . 'wusp_group_product_price_mapping ADD batch_numbers TINYINT(1) UNSIGNED NOT NULL DEFAULT 0');
					}
					break;
			}
		}


		/**
		* Checks Whether the file selected is valid file or not, on the basis of file headers
		 *
		* @param string $foundHeaders Headers of uploaded file.
		* @param array $requiredHeaders2 Headers for the entity with pricing.
		* @param array $requiredHeaders1 Headers for the entity with pricing type.
		* @return bool true if valid
		*/
		public function checkFileHeaders( $foundHeaders, $requiredHeaders2, $requiredHeaders1) {
			if ($foundHeaders !== $requiredHeaders2 && $foundHeaders !== $requiredHeaders1) {
				echo '<div class="wdm_message_p error"><p>' . esc_html__('Please Select valid file', 'customer-specific-pricing-for-woocommerce') . '</p></div>';
				return false;
			}

			return true;
		}

		/**
		* Print the table headers for the entity pricing selected.
		 *
		* @param string $importType entity type
		* @param int $uniqueId "ProductID"|"SKU" type of the unique identifier
		* @return string $noOfRecords Total number of records in the file
		*/
		private function displayImportProcessStatus( $importType, $uniqueId, $noOfRecords) {
			?>
				<div class="wdmcsp-progress-status-container">
				<div class="space-top "></div>

				<div class="import-status">
					<div class="row">
						<div class="col-md-3"></div>
						<div class="col-md-6 text-center">
						<h3><label><?php esc_html_e('Processed :', 'customer-specific-pricing-for-woocommerce'); ?></label>
							<span id="total-processed" current_val="0">0</span>
							<span class="seperator">/</span>
							<span id="total-records"><?php echo esc_html($noOfRecords); ?></span>
						</h3>
						</div>
						<div class="col-md-3"></div>
					</div>

					<div class="row">
						<div class="col-md-12">
							<div class="progress">
								<div class="progress-bar progress-bar-striped active" 
								role="progressbar" aria-valuenow="40" aria-valuemin="0"
								aria-valuemax="100" id="import-progress-bar">
								<span id="percent-progress">40%</span>
								</div>
							</div>
						</div>
					</div>
					
					<div class="row">
						<div class="col-md-12 text-center">
								<button type="button" class="btn btn-primary csp-download-report" 
									disabled>
									<?php esc_html_e('Download Report', 'customer-specific-pricing-for-woocommerce'); ?>
								</button>
							</div>
					</div>

					<div class="row text-center status-details">
						<div class="col-md-4">
							<span><?php esc_html_e('Inserted:', 'customer-specific-pricing-for-woocommerce'); ?> </span>
							<span id="inserted">0</span>
						</div>
						<div class="col-md-4">
							<span><?php esc_html_e('Updated:', 'customer-specific-pricing-for-woocommerce'); ?> </span>
							<span id="updated">0</span>
						</div>
						<div class="col-md-4">
							<span><?php esc_html_e('Skipped:', 'customer-specific-pricing-for-woocommerce'); ?> </span>
							<span id="skipped">0</span>
						</div>
					</div>
				</div>

				<div class="space-bottom"></div>

				</div>
				 
			<?php
		}

		/**
		* Checks the file type valid or not and the print the table headers for
		* pricing.
		 *
		* @param string $fileUploadType entity specific pricing import.
		* @param string $foundHeaders Headers of uploads file.
		* @param int $noOfRecords count of records in file.
		* @return mixed html for headers if file-type valid otherwise false.
		*/
		public function checkFileType( $fileUploadType, $foundHeaders, $uniqueId) {
			global $cspFunctions;

			$userHeaders1  = array( $uniqueId, 'user', 'min qty', 'price' );
			$userHeaders2  = array( $uniqueId, 'user', 'min qty', 'flat', '%' );
			$roleHeaders1  = array( $uniqueId, 'role', 'min qty', 'price' );
			$roleHeaders2  = array( $uniqueId, 'role', 'min qty', 'flat', '%' );
			$groupHeaders1 = array( $uniqueId, 'group name', 'min qty', 'price');
			$groupHeaders2 = array( $uniqueId, 'group name', 'min qty', 'flat', '%');
			$foundHeaders  = array_map('strtolower', $foundHeaders);

			switch ($fileUploadType) {
				case 'Wdm_User_Specific_Pricing_Import':
					$correctHeader = $this->checkFileHeaders($foundHeaders, $userHeaders1, $userHeaders2);
					if (!$correctHeader) {
							return false;
					}
					return 'user';

				case 'Wdm_Role_Specific_Pricing_Import':
					$correctHeader = $this->checkFileHeaders($foundHeaders, $roleHeaders1, $roleHeaders2);
					if (!$correctHeader) {
							return false;
					}
					return 'role';

				case 'Wdm_Group_Specific_Pricing_Import':
					$correctHeader = $this->checkFileHeaders($foundHeaders, $groupHeaders1, $groupHeaders2);
					if (!$correctHeader) {
							return false;
					}
					if ($cspFunctions->wdmIsActive('groups/groups.php')) {
						return 'group';
					}
					echo '<div class="wdm_message_p error"><p>' . esc_html__('Please Activate the Groups Plugin ', 'customer-specific-pricing-for-woocommerce') . '</p></div>';
					return false;
			}
		}
	}
}

/**
 * Include all batch processing files
 */
require_once 'process-import/class-wdm-process-user-specific-csv-batches.php';
require_once 'process-import/class-wdm-process-role-specific-csv-batches.php';
require_once 'process-import/class-wdm-process-group-specific-csv-batches.php';
