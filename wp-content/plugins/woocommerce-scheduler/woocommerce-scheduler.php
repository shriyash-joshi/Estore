<?php
/**
 * @package   woocommerce-scheduler
 * @author    Wisdmlabs <helpdesk@wisdmlabs.com>
 * @link      https://wisdmlabs.com/
 *
 * @wordpress-plugin
 * Plugin Name:       Scheduler for WooCommerce
 * Plugin URI:        http://wisdmlabs.com/woocommerce-scheduler-plugin-for-product-availability
 * Description:       This extension plugin allows you to schedule product purchase availability in your WooCommerce store.
 * Version:           2.3.5
 * Author:            Wisdmlabs
 * Author URI:        helpdesk@wisdmlabs.com
 * Text Domain:       woocommerce_scheduler
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * WC tested up to: 4.0.1
 */

if (! defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

/**
 * Set the plugin slug as default text domain.
 */
define('WDM_WOO_SCHED_TXT_DOMAIN', 'woocommerce_scheduler');
if (! defined('WS_VERSION')) {
	define('WS_VERSION', '2.3.5');
}

if (!defined('WDMWS_PLUGIN_PATH')) {
	define('WDMWS_PLUGIN_PATH', untrailingslashit(plugin_dir_path(__FILE__)));
}


/**
 * Function wdmldgroupLoadTextDomain() to load plugins text domain.
 */
function wdmwooschedLoadTextDomain() {
	load_plugin_textdomain(WDM_WOO_SCHED_TXT_DOMAIN, false, plugin_basename(dirname(__FILE__)) . '/languages');
}
	include_once 'includes/admin/class-wdmws-settings.php';
	include_once 'includes/class-wdm-scheduler-install.php';
	$scheduler_install = new SchedulerInstall();
		register_activation_hook(__FILE__, array( $scheduler_install, 'wdmProductExpiration' ));
		register_activation_hook(__FILE__, array( $scheduler_install, 'wooScheduleCreateTables' ));
		register_activation_hook(__FILE__, array( $scheduler_install, 'schedulerOnActivation' ));
		add_action('upgrader_process_complete', 'wdmSchedulerAfterUpgrade', 10, 2);

add_action('plugins_loaded', 'wsPluginsLoaded', 5);
function wsPluginsLoaded() {
	/*
	 * Check woocommerce plugin is active or not
	 */
	if (defined('WC_VERSION')) {
		wdmwooschedLoadTextDomain();
		schedulerLoadLicense();
		add_action('admin_init', 'schedulerAdminInit', 99);
		add_action('admin_menu', 'schedulerLoadSettings', 99);
		
		add_action('init', 'schedulerBootstrap');
		include_once 'includes/woo-schedule-functions.php';
		include_once 'includes/class-wdmws-notify-cron.php';

		// Include WisdmAdPackage
		if (is_admin() || defined('DOING_CRON')) {
			include_once 'WisdmAdPackage.php';
			$WisdmAdPackage = WisdmAd\WisdmAdPackage::getInstance();
		}
	} else {
		add_action('admin_notices', 'schedularBasePluginInactiveNotice');
	}
}

function schedulerBootstrap() {
	 global $wdmPluginDataScheduler;
	$getDataFromDb = \Licensing\WdmLicense::checkLicenseAvailiblity($wdmPluginDataScheduler['pluginSlug']);

	// if ($getDataFromDb != 'available') {
	// return;
	// }

	include_once 'includes/woo-schedule-ajax.php';
	new WooScheduleAjax\WooScheduleAjax($wdmPluginDataScheduler['pluginSlug'], $wdmPluginDataScheduler['pluginVersion']);
	include_once 'includes/class-scheduler-admin.php';
	include_once 'includes/class-scheduler-frontend.php';
	add_action('admin_enqueue_scripts', 'wdmSchedulerEnqueueScripts', 20, 1);

	include_once 'includes/woo-schedule-single-view.php';

	$woo_single_view = new WooScheduleSingleView\WooScheduleSingleView($wdmPluginDataScheduler['pluginSlug'], $wdmPluginDataScheduler['pluginVersion']);

	add_action('admin_enqueue_scripts', 'registerAdminScripts');
	add_action('wp_enqueue_scripts', 'enqueueFrontendScripts');
	add_action('admin_menu', array( $woo_single_view, 'registerSingleViewSubmenuPage' ));
	add_action('pre_get_posts', 'wdmShopFilter');
	add_filter('woocommerce_get_children', 'wdmHideVariation', 10, 2);

	includeNotificationFeatureFiles();
}

/**
 * Include files required for notification feature.
 */
function includeNotificationFeatureFiles() {
	$wdmwsSettings = \Includes\AdminSettings\WdmWsSettings::getSettings();

	/**
	 * To avoid the conflict with "WooCommerce Order Status Manager"s
	 * email editor feature
	 */
	if (! empty($_GET['page']) && ! empty($_GET['tab'])) {
		if ('wc-settings' == $_GET['page'] && 'email' == $_GET['tab']) {
			return false;
		}
	}
	include_once 'includes/emails/class-scheduler-email.php';
	include_once 'includes/emails/class-scheduler-enrollment-email.php';
	include_once 'includes/emails/class-scheduler-notification-email.php';

	if (is_admin()) {
		include_once 'includes/class-scheduler-privacy.php';
	}

	if (is_admin()) {
		include_once 'includes/class-scheduler-admin-notify-enrolled-users.php';
		include_once 'includes/class-scheduler-enrolled-users-export.php';
		//include_once 'includes/class-scheduler-admin-notify-email-settings.php';
	}
	if (isset($wdmwsSettings['wdmws_enable_notify']) && '1' == $wdmwsSettings['wdmws_enable_notify']) {
		include_once 'includes/class-scheduler-handle-unsubscription.php';
		include_once 'includes/woo-schedule-shortcode.php';
	}
}

function wdmShopFilter( $query) {
	global $wpdb;
	if (! is_admin() && $query->is_main_query()) {
		$postMetaTable = $wpdb->prefix . 'postmeta';
		$sqlQuery      = "SELECT post_id FROM 
            (SELECT post_id FROM $postMetaTable 
                WHERE (meta_key='wdm_show_cat_product' AND meta_value='no') 
                    OR (meta_key='wdm_show_product' AND meta_value='no')) 
                        AS all_hidden 
            WHERE all_hidden.post_id NOT IN 
                (SELECT post_id FROM $postMetaTable 
                    WHERE meta_key='wdm_show_product' AND meta_value='yes')";

		$productsToHide = $wpdb->get_col($sqlQuery, 0);
		$productsToHide = array_unique($productsToHide);
		$query->set('post__not_in', $productsToHide);
	}
}

function wdmHideVariation( $visible_children, $obj) {
	$variable_id = $obj->get_id();
	if (get_post_meta($variable_id, '_hide_if_unavailable', true) == 'yes') {
		foreach ($visible_children as $key => $variant_id) {
			$to_show = get_post_meta($variant_id, 'wdm_show_product', true);
			if ($to_show == 'no') {
				unset($visible_children[ $key ]);
			}
		}
	}
	return $visible_children;
}

/**
 * This function is used to register all the 
 *
 * @return void
 */
function schedulerAdminInit() {
	//Register all the plugin settings
	include_once 'includes/admin/class-scheduler-admin-settings.php';
	new Includes\AdminSettings\schedulerAdminSettings();

	include_once 'includes/admin/class-notify-admin-settings.php';
	new Includes\AdminSettings\schedulerNotifyAdminSettings();
}

/**
 * This function will load the Scheduler Admin setting pages
 *
 * @since 3.0.0
 * @return void
 */
function schedulerLoadSettings() {
	// Add submenu pages
	$mainMenuTitle					= esc_html__('Scheduler for WooCommerce', 'woocommerce_scheduler');
	$bulkSchedulePageTitle			= esc_html__('Bulk Schedule', 'woocommerce_scheduler');
	$enrolledUsersPageTitle			= esc_html__('Enrolled Users', 'woocommerce_scheduler');
	$globalSettingsPageTitle		= esc_html__('Settings - Scheduler For WooCommerce', 'woocommerce_scheduler');
	$extensionsPageTitle			= esc_html__('Extensions from WisdmLabs', 'woocommerce_scheduler');
	$feedbackPageTitle				= esc_html__('Feedback', 'woocommerce_scheduler');
	$capability						= apply_filters('wdm_scheduler_setting_pages_capability', 'manage_options');
	
	$settingsMenuName				= esc_html__('Settings', 'woocommerce_scheduler');

	//Menu Page
	add_menu_page($globalSettingsPageTitle, $mainMenuTitle, $capability, 'wdmws_settings', 'wdmSchedulerGlobalSettingsPage', 'dashicons-backup', 56);
	//Submenu Pages
	add_submenu_page( 'wdmws_settings', $globalSettingsPageTitle, $settingsMenuName, 'manage_options', 'wdmws_settings');
	add_submenu_page( 'wdmws_settings', $bulkSchedulePageTitle, $bulkSchedulePageTitle, 'manage_options', 'wdmws_settings_bulk_schedule', 'wdmBulkSchedulePage');
	add_submenu_page( 'wdmws_settings', $enrolledUsersPageTitle, $enrolledUsersPageTitle, 'manage_options', 'wdmws_settings_enrolled_users', 'wdmEnrolledUsersPage');
	add_submenu_page( 'wdmws_settings', $extensionsPageTitle, 'Extensions', 'manage_options', 'wdmws_extensions', 'wdmWsOtherExtensions');
	add_submenu_page( 'wdmws_settings', $feedbackPageTitle, 'Feedback', 'manage_options', 'wdmws_feedback', 'wdmFeedbackPage');
}


function wdmSchedulerGlobalSettingsPage() {
	$capability						= apply_filters('wdm_scheduler_setting_pages_capability', 'manage_options');
	$active_tab						= isset($_GET[ 'tab' ])?$_GET[ 'tab' ]:'general_settings';
	?>
	<div class="wrap">
			<style>	
				.wdmws-page-title {
                font-size:28px;
                font-weight:400;
				color:#000000;
				line-height:1.2rem;
            }
    		</style>
			<h1 class="wdmws-page-title"><?php esc_html_e('General Settings', 'woocommerce_scheduler'); ?></h1>
			<h2 class="nav-tab-wrapper">  
                <a href="?page=wdmws_settings&tab=general_settings" class="nav-tab <?php echo $active_tab == 'general_settings' ? 'nav-tab-active' : ''; ?>">General</a>  
                <a href="?page=wdmws_settings&tab=notification_settings" class="nav-tab <?php echo $active_tab == 'notification_settings' ? 'nav-tab-active' : ''; ?>">Notifications</a>  
            </h2>  
		<?php
		switch ($active_tab) {
			case 'general_settings':
				settings_errors(); ?>
				<form action="options.php" method="post">
				<?php
				settings_fields('wdmws_global_settings');
				do_settings_sections('wdmws_global_settings');
				submit_button('Save Settings');
				?>
				</form> 
				<?php
			break;
			case 'notification_settings':
				$emailSettingsPage = isset($_GET['flow_page']) && 'notify_user_email'==$_GET['flow_page'];
				?>
				<ul style="font-weight:bold;">
					<li class="nav-tab-sub" style="display:inline;">
						<?php if (!$emailSettingsPage) {
							esc_html_e('Global', 'woocommerce-scheduler');
						} else { ?>
						<a href="?page=wdmws_settings&tab=notification_settings&flow_page=notify_user_global" ><?php esc_html_e('Global', 'woocommerce-scheduler'); ?></a>
						<?php
						}
						?>  |
					</li>
					<li class="nav-tab-sub" style="display:inline;">
					<?php if ($emailSettingsPage) {
							esc_html_e('Email', 'woocommerce-scheduler');
						} else { ?>
						<a href="?page=wdmws_settings&tab=notification_settings&flow_page=notify_user_email" class="current"><?php esc_html_e('Email', 'woocommerce-scheduler'); ?></a> 
						<?php
						}
						?>	
					</li>
				</ul>
				<?php
				if ($emailSettingsPage) {
					settings_errors();
					include_once 'includes/admin/class-notify-email-settings.php';
					$schedulerNotifyEmailSettings = new Includes\AdminSettings\SchedulerNotifyEmailSettings();				
					// wdmws_product_availability_email
					if (isset($_GET['message'])) {
					    $emailType = $_GET['message'];
					    if ('wdmws_user_enrollment_email' == $emailType) {
							wp_enqueue_script('notify_me_settings_js');
					        $schedulerNotifyEmailSettings->showUserEnrollmentEmailSettings();
					    } elseif ('wdmws_product_availability_email' == $emailType) {
							wp_enqueue_script('notify_me_settings_js');
					        $schedulerNotifyEmailSettings->showProductAvailabiltiyEmailSettings();
					    }
					} else {
					    $schedulerNotifyEmailSettings->showNotificationEmailOptions();
					}
				} else {
					wdmWsNotificationSettings();
				}
			break;

			default:
				# code...
				break;
		}
		?>
	</div>
	<?php
}


function wdmEnrolledUsersPage() {
	include_once 'includes/admin/enrolled-users.php';
}

function wdmBulkSchedulePage() {
	include_once 'includes/admin/class-bulk-schedule-page.php';
	new Includes\AdminSettings\schedulerAdminBulkSchedules();
}

function wdmFeedbackPage() {
	include_once 'includes/admin/feedback.php';
}

function wdmWsNotificationSettings() {
	$capability						= apply_filters('wdm_scheduler_setting_pages_capability', 'manage_options');
	if (isset($_GET['page'])) {
		?>
			<?php settings_errors(); ?>
			  <form action="options.php" method="post">
			<?php
			settings_fields('wdmws_notification_settings');
			do_settings_sections('wdmws_notification_settings');
			submit_button('Save Settings');
			wp_enqueue_script('global_settings_js');
			?>
			</form>
		<?php
	}
}

function wdmWsOtherExtensions() {
	include_once 'includes/admin/extensions.php';
}


// Create Menu for Settings Page
function piklistPluginSettingPages( $pages) {
	 $pages[] = array(
		 'page_title' => __('Settings'),
		 'menu_title' => __('Scheduler for WooCommerce', WDM_WOO_SCHED_TXT_DOMAIN),
		 'capability' => 'manage_options',
		 'menu_slug' => 'wdmws_settings',
		 'setting' => 'wdmws_settings',
		 'single_line' => true,
		 'default_tab' => 'General',
		 'save_text' => 'Save Settings',
	 );

	 $pages[] = array(
		 'page_title' => __('Bulk Schedule'),
		 'menu_title' => __('Bulk Schedule', WDM_WOO_SCHED_TXT_DOMAIN),
		 'sub_menu' => 'wdmws_settings',
		 'capability' => 'manage_options',
		 'menu_slug' => 'wdmws_settings_bulk_schedule',
		 'setting' => 'wdmws_settings_bulk_schedule',
		 'single_line' => true,
		 'save_text' => 'Save Settings',
	 );

	 $wdmwsSettings = \Includes\AdminSettings\WdmWsSettings::getSettings();
	 if (isset($wdmwsSettings['wdmws_enable_notify']) && '1' == $wdmwsSettings['wdmws_enable_notify']) {
		 $pages[] = array(
			 'page_title' => __('Enrolled Users and List Users'),
			 'menu_title' => __('Enrolled Users', WDM_WOO_SCHED_TXT_DOMAIN),
			 'sub_menu' => 'wdmws_settings',
			 'capability' => 'manage_options',
			 'menu_slug' => 'wdmws_settings_enrolled_users',
			 'setting' => 'wdmws_settings_enrolled_users',
			 'single_line' => true,
			 'save_text' => 'Save Settings',
		 );
	 }

	 return $pages;
}

/**
 * Check date validation.
 *
 * @param [type] $product_id   product ID
 * @param string $product_type Product type
 *
 * @return [type] [description]
 */
function wdmCheckDateValidation( $product_id, $product_type = 'simple') {
	$schedule  = get_post_meta($product_id, 'wdm_schedule_settings', true);
	$curr_time = current_time('H:i:s');
	$curr_date = current_time('m/d/Y');
	$curr_day  = current_time('l');

	if (isset($schedule['type'])) {
		// product is scheduled
		return wdmWsValidateAvailability($product_id, $curr_date, $curr_time, $curr_day, $schedule);
	} else {
		// product not scheduled hence check the schedule at category level
		if (in_array($product_type, array( 'course', 'simple' ))) {
			return woo_schedule_check_category_availability($product_id);
		} else {
			$parent_id = wp_get_post_parent_id($product_id);
			return woo_schedule_check_category_availability($parent_id);
		}
	}
}

function getConcatenatedTime( $hour, $min, $type) {
	$default = '23:59';
	if ('start'==$type) {
		$default = '00:00';
	}
	
	if (! empty($hour) && ! empty($min)) {
		$time = $hour . ':' . $min;
	} else {
		$time = $default;
	}

	return $time;
}

function validateTime( $product_id, $subtype, $wdm_start_date, $wdm_end_date, $curr_date, $curr_time, $curr_day) {
	// For Start time and End time
	$wdm_start_time_hr  = get_post_meta($product_id, 'wdm_start_time_hr', true);
	$wdm_start_time_min = get_post_meta($product_id, 'wdm_start_time_min', true);
	$wdm_start_time     = getConcatenatedTime($wdm_start_time_hr, $wdm_start_time_min, 'start');
	$wdm_end_time_hr    = get_post_meta($product_id, 'wdm_end_time_hr', true);
	$wdm_end_time_min   = get_post_meta($product_id, 'wdm_end_time_min', true);
	$wdm_end_time       = getConcatenatedTime($wdm_end_time_hr, $wdm_end_time_min, 'end');

	if ($subtype == 'per_day' || $subtype == 'specificTime') {
		return isAvailablePerDay($product_id, $curr_day, $curr_date, $curr_time, $wdm_start_date, $wdm_start_time, $wdm_end_date, $wdm_end_time);
	} else {
		return isBetweenDateTime($curr_date, $curr_time, $wdm_start_date, $wdm_end_date, $wdm_start_time, $wdm_end_time);
	}
}

function isAvailablePerDay( $product_id, $curr_day, $curr_date, $curr_time, $wdm_start_date, $wdm_start_time, $wdm_end_date, $wdm_end_time) {
	$options = get_post_meta($product_id, 'wdm_days_selected', true);
	if (( ( strtotime($curr_date) >= strtotime($wdm_start_date) ) && ( strtotime($curr_date) <= strtotime($wdm_end_date) ) ) && ( ( strtotime($curr_time) >= strtotime($wdm_start_time) ) && ( strtotime($curr_time) <= strtotime($wdm_end_time) ) ) &&
	 ( isset($options) && isset($options[ $curr_day ]) )) {
		return true;
	} else {
		return false;
	}
}

function isBetweenDateTime( $curr_date, $curr_time, $wdm_start_date, $wdm_end_date, $wdm_start_time, $wdm_end_time) {
	if (( strtotime($curr_date . ' ' . $curr_time) >= strtotime($wdm_start_date . " $wdm_start_time") ) && ( strtotime($curr_date . ' ' . $curr_time) <= strtotime($wdm_end_date . " $wdm_end_time") )) {
		return true;
	} else {
		return false;
	}
}

function isAvailableAfterDateTime( $curr_date, $curr_time, $wdm_start_date, $wdm_start_time) {
	if (( strtotime($curr_date . ' ' . $curr_time) >= strtotime($wdm_start_date . " $wdm_start_time") )) {
		return true;
	} else {
		return false;
	}
}

// function to check the availability in case of whole day schedule
function isAvailableToday( $productId, $dateNow, $timeNow, $startDate, $startTime, $endDate, $endTime) {
	$dateTimeNow   = strtotime($dateNow . ' ' . $timeNow);
	$dateTimeStart = strtotime($startDate . ' ' . $startTime);
	$dateTimeEnd   = strtotime($endDate . ' ' . $endTime);
	if ($dateTimeNow < $dateTimeStart || $dateTimeNow >= $dateTimeEnd) {
		return false;
	}
	$availabilityPairs = get_post_meta($productId, 'availability_pairs', true);
	if (! empty($availabilityPairs['makeAvailable'])) {
		$pairCount = sizeof($availabilityPairs['makeAvailable']);
		for ($i = 0; $i < $pairCount; $i++) {
			if ($dateTimeNow >= $availabilityPairs['makeAvailable'][ $i ] && $dateTimeNow < $availabilityPairs['makeUnAvailable'][ $i ]) {
				return true;
			}
		}
		return false;
	}
	if (empty($dateTimeEnd) && $dateTimeNow > $dateTimeStart) {
		return true;
	}
}

// The Function to check the availability of the specific time schedule
function isAvailableTodayForDuration( $productId, $dateNow, $timeNow) {
	$dateTimeNow       = strtotime($dateNow . ' ' . $timeNow);
	$availabilityPairs = get_post_meta($productId, 'availability_pairs', true);
	if (! empty($availabilityPairs['makeAvailable'])) {
		$pairCount = sizeof($availabilityPairs['makeAvailable']);

		for ($i = 0; $i < $pairCount; $i++) {
			if ($dateTimeNow >= $availabilityPairs['makeAvailable'][ $i ] && $dateTimeNow < $availabilityPairs['makeUnAvailable'][ $i ]) {
				return true;
			}
		}
	}
	return false;
}

/**
 * enqueue admin side scripts.
 *
 * @return [type] [description]
 */
function enqueueFrontendScripts() {
	if (is_product()) {
		global $post, $wdmPluginDataScheduler;
		$product = wc_get_product($post->ID);

		$product_id = wooSchedulerProductId($product);

		$wdm_start_date = get_post_meta($product_id, 'wdm_start_date', true);

		$wdmwsSettings       = \Includes\AdminSettings\WdmWsSettings::getSettings();
		$wdmwsFontColor      = isset($wdmwsSettings) && isset($wdmwsSettings['wdmws_font_color']) ? $wdmwsSettings['wdmws_font_color'] : '#dd3333';
		$wdmwsBgCircleColor  = isset($wdmwsSettings) && isset($wdmwsSettings['wdmws_background_color']) ? $wdmwsSettings['wdmws_background_color'] : '#EEEEEE';
		$wdmwsFrCirlcleColor = isset($wdmwsSettings) && isset($wdmwsSettings['wdmws_front_color']) ? $wdmwsSettings['wdmws_front_color'] : '#CCCCCC';

		$expirationMsg   = isset($wdmwsSettings) && isset($wdmwsSettings['wdmws_custom_product_expiration']) ? $wdmwsSettings['wdmws_custom_product_expiration'] : 'Currently Unavailable';
		$schedule_type   = isset($wdmwsSettings) && isset($wdmwsSettings['wdmws_custom_product_expiration_type'][0]) ? $wdmwsSettings['wdmws_custom_product_expiration_type'][0] : 'per_day';
		$catDates        = getCategoryDates($product_id);
		$timerFieldTexts = array(
			'seconds' => __('Secs', WDM_WOO_SCHED_TXT_DOMAIN),
			'minutes' => __('Mins', WDM_WOO_SCHED_TXT_DOMAIN),
			'hours' => __('Hrs', WDM_WOO_SCHED_TXT_DOMAIN),
			'days' => __('Days', WDM_WOO_SCHED_TXT_DOMAIN),
		);
		wp_register_script('wdmws_time_circles', plugins_url('/js/TimeCircles.js', __FILE__), array( 'jquery' ));
		wp_enqueue_script('wdmws_time_circles');

		wp_register_script('wdmws_display_timer', plugins_url('/js/wdmws-display-timer.js', __FILE__), array( 'jquery' ), $wdmPluginDataScheduler['pluginVersion']);
		wp_register_script('wdmws_display_variation_timer', plugins_url('/js/wdmws-display-variation-timer.js', __FILE__), array( 'jquery' ));

		registerSimpleScripts($product);

		wp_register_style('wdmws_timer_css', plugins_url('/css/TimeCircles.css', __FILE__));
		wp_enqueue_style('wdmws_timer_css');

		wp_register_style('wdm-message-css', plugins_url('/css/message.css', __FILE__));

		wp_enqueue_style('wdm-message-css');

		if (isset($wdmwsSettings['wdmws_enable_notify']) && '1' == $wdmwsSettings['wdmws_enable_notify']) {
			// register modal js
			wp_register_script('wdmws_modal_js', plugins_url('/js/bootstrap-modal.js', __FILE__), array( 'jquery' ));

			// register notify me js
			wp_register_script('wdmws_notify_me_js', plugins_url('/js/notify-me.js', __FILE__), array( 'jquery', 'wdmws_modal_js' ));

			// register modal css
			wp_register_style('wdmws_modal_css', plugins_url('/css/bootstrap-modal.css', __FILE__));

			// register notify me css
			wp_register_style('wdmws_notify_me_css', plugins_url('/css/notify-me.css', __FILE__));
		}

		wp_localize_script(
			'wdmws_display_timer',
			'wdmws_timer_object',
			array(
				'admin_ajax'                => admin_url('admin-ajax.php'),
				'wdmws_timer_nonce'         => wp_create_nonce('wdmws_timer_nonce'),
				'product_id'                => $product_id,
				'is_available'              => wdmGetProductAvailability($product_id),
				'is_product_cat_available'  => getwdmProductCategorySchedule($product_id),
				'wdmws_font_color'          => $wdmwsFontColor,
				'wdmws_background_color'    => $wdmwsBgCircleColor,
				'wdmws_front_color'         => $wdmwsFrCirlcleColor,
				'wdmws_is_scheduled'        => ( empty($wdm_start_date) && ! empty($catDates) ) || ( ! empty($wdm_start_date) ),
				'expirationMsg'             => $expirationMsg,
				'timerFieldTexts'           => $timerFieldTexts,
			)
		);

		registerVariableScripts($post, $product, $schedule_type, $wdmwsFontColor, $wdmwsBgCircleColor, $wdmwsFrCirlcleColor, $expirationMsg);
	}

	if (isset($_GET['wdmws_unsubscribe'])) {
		wp_register_script('wdmws_unsubscribe_js', plugins_url('/js/unsubscribe-notification.js', __FILE__), array( 'jquery' ));
		wp_register_style('wdmws_unsubscribe_css', plugins_url('/css/unsubscribe-notification.css', __FILE__));
	}
}

/**
 * If the product is scheduled at product level this function returns ""
 * returns "no" if any of the category which product
 * belongs to is scheduled & not available.
 * returns "yes" if,
 *  - product belongs to no category.
 *  - product belongs to category which is not scheduled
 *  - & any of product category schedule     which is currently available
 *
 * @return bool "To display product according to categories or not"
 */
function getwdmProductCategorySchedule( $product_id) {
	if (! empty(get_post_meta($product_id, 'wdm_schedule_settings', true))) {
		return '';
	}
	if (woo_schedule_check_category_availability($product_id)) {
		return 'yes';
	} else {
		return 'no';
	}
}

/**
 * This function returns yes when product is available
 * returns no when product is unavailable
 *
 * @param int $product_id
 * @return string Yes/No Availability Of the product
 */
function wdmGetProductAvailability( $product_id) {
	return wdmCheckDateValidation($product_id) ? 'yes' : 'no';
}

/**
 * A function which returns array of category ID's in which given product
 * is listed
 *
 * @param $product_id "Product ID"
 * @return array list of category ids
 */
function getwdmProductCategories( $product_id) {
	$args         = array();
	$defaults     = array( 'fields' => 'ids' );
	$args         = wp_parse_args($args, $defaults);
	$category_ids = wp_get_object_terms($product_id, 'product_cat', $args);
	return $category_ids;
}

function registerSimpleScripts( $product) {
	if (in_array($product->get_type(), array( 'course', 'simple' ))) {
		wp_enqueue_script('wdmws_display_timer');
	}
}

function registerVariableScripts( $post, $product, $schedule_type, $wdmwsFontColor, $wdmwsBgCircleColor, $wdmwsFrCirlcleColor, $expirationMsg) {
	if ($product->get_type() == 'variable' || $product->get_type() == 'variable-subscription') {
		$parent_id = $post->ID;

		$variation_data  = getVariationData($parent_id);
		$timerFieldTexts = array(
			'seconds' => __('Secs', WDM_WOO_SCHED_TXT_DOMAIN),
			'minutes' => __('Mins', WDM_WOO_SCHED_TXT_DOMAIN),
			'hours' => __('Hrs', WDM_WOO_SCHED_TXT_DOMAIN),
			'days' => __('Days', WDM_WOO_SCHED_TXT_DOMAIN),
		);
		if (empty($variation_data)) {
			return;
		}

		wp_enqueue_script('wdmws_display_variation_timer');
		wp_localize_script(
			'wdmws_display_variation_timer',
			'wdmws_variation_timer_object',
			array(
				'admin_ajax'        => admin_url('admin-ajax.php'),
				'wdmws_timer_nonce' => wp_create_nonce('wdmws_timer_nonce'),
				'product_id'        => $post->ID,
				'product_url'       => get_permalink($post->ID),
				'schedule_type'     => $schedule_type,
				'variation_data'    => $variation_data,
				'wdmws_font_color'  => $wdmwsFontColor,
				'wdmws_background_color'  => $wdmwsBgCircleColor,
				'wdmws_front_color'  => $wdmwsFrCirlcleColor,
				'wdmws_expiration_message'  => $expirationMsg,
				'timerFieldTexts'   => $timerFieldTexts,
			)
		);
	}
}

function registerAdminScripts() {
	global $post;
	$product_id = isset($_GET['post']) ? $_GET['post'] : '';
	$product    = wc_get_product($product_id);
	global $wdmPluginDataScheduler;

	if (isset($post->post_type) && $post->post_type == 'product' && isset($_GET['post']) && $_GET['action'] == 'edit' && in_array($product->get_type(), array( 'course', 'simple', 'variable' ))) {
		wp_register_style('wdm_edit_page', plugins_url('/css/wdm-edit-page.css', __FILE__));
		wp_enqueue_style('wdm_edit_page');
	}
	// enqueue script
	wp_register_script('notify_me_settings_js', plugins_url('/js/wdmws-notify-settings.js', __FILE__), array( 'jquery' ), $wdmPluginDataScheduler['pluginVersion']);
	wp_register_script('jquery_datatables_js', plugins_url('/js/datatables.js', __FILE__), array( 'jquery' ), $wdmPluginDataScheduler['pluginVersion']);
	wp_register_script('global_settings_js', plugins_url('/js/wdmws-global-settings.js', __FILE__), array( 'jquery' ), $wdmPluginDataScheduler['pluginVersion']);
	wp_register_script('woo_scheduler_note', plugins_url('/js/woo_schedule_add_note.js', __FILE__), array( 'jquery' ), $wdmPluginDataScheduler['pluginVersion']);
	wp_register_script('wdm_moment_js_handler', plugins_url('/js/moment.js', __FILE__), array( 'jquery' ), $wdmPluginDataScheduler['pluginVersion']);
	wp_register_script('wdm_datepicker', plugins_url('/js/bootstrap-datetimepicker.js', __FILE__), array( 'wdm_moment_js_handler' ));
	wp_register_script('chk-date-time', plugins_url('/js/wdm_chk_date_time.js', __FILE__), array( 'wdm_datepicker' ), true);
	wp_register_script('wdm_singleview_js_handler', plugins_url('/js/woo_schedule_single_view.js', __FILE__), array( 'jquery' ), $wdmPluginDataScheduler['pluginVersion']);
	wp_register_script('woo_singleview_validate_js_handler', plugins_url('/js/woo_schedule_validate.js', __FILE__), array( 'jquery' ), $wdmPluginDataScheduler['pluginVersion']);
	wp_register_script('woo_singleview_select2_js_handler', plugins_url('/js/woo_schedule_select2.js', __FILE__), array( 'jquery' ), $wdmPluginDataScheduler['pluginVersion']);
	wp_register_script('datatables_semanticui_js', 'https://cdn.datatables.net/1.10.19/js/dataTables.semanticui.min.js', array( 'jquery' ), $wdmPluginDataScheduler['pluginVersion']);
	wp_register_script('datatables_semantic_js', 'https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.3.1/semantic.min.js', array( 'jquery' ), $wdmPluginDataScheduler['pluginVersion']);
	wp_register_script('wdmws_modal_js', plugins_url('/js/bootstrap-modal.js', __FILE__), array( 'jquery' ));
	wp_register_script('wdm_bulk_js_form', plugins_url('/js/wdm_bulk_schedule_form.js', __FILE__), array( 'jquery' ));
	wp_register_script('wdm-scheduler-script', plugins_url('/js/wdm-scheduler-scripts.js', __FILE__), array( 'jquery' ));
	// Register stylesheets
	wp_register_style('general_settings_css', plugins_url('/css/wdmws-general-settings.css', __FILE__), array( 'jquery' ), $wdmPluginDataScheduler['pluginVersion']);
	wp_register_style('notify_me_settings_css', plugins_url('/css/wdmws-notify-settings.css', __FILE__));
	wp_register_style('jquery_datatables_css', plugins_url('/css/datatables.css', __FILE__));
	wp_register_style('wdmws_other_extension_css', plugins_url('/css/extension.css', __FILE__));
	wp_register_style('global_settings_css', plugins_url('/css/wdmws-global-settings.css', __FILE__));
	wp_register_style('wdm_bootstrap_css', plugins_url('/css/wdm_bootstrap.css', __FILE__));
	wp_register_style('wdm_datepicker_css', plugins_url('/css/bootstrap-datetimepicker.min.css', __FILE__));
	wp_register_style('bootstrap_css', plugins_url('/css/bootstrap.css', __FILE__), array(), $wdmPluginDataScheduler['pluginVersion']);
	wp_register_style('woo_select2_css', plugins_url('/css/woo_schedule_select2.css', __FILE__), array(), $wdmPluginDataScheduler['pluginVersion']);
	wp_register_style('bootstrap_select2_css', plugins_url('/css/select2-bootstrap.css', __FILE__), array(), $wdmPluginDataScheduler['pluginVersion']);
	wp_register_style('wdmws_modal_css', plugins_url('/css/bootstrap-modal.css', __FILE__));
	wp_register_style('datatables_semantic_css', 'https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.3.1/semantic.min.css');
	wp_register_style('datatables_semanticui_css', 'https://cdn.datatables.net/1.10.19/css/dataTables.semanticui.min.css');
	wp_register_style('wdm_bulk_edit_page_form', plugins_url('/css/wdm-scheduler-bulk-form.css', __FILE__));
	wp_register_style('wdm-scheduler-style', plugins_url('/css/wdm-scheduler-style.css', __FILE__));


	if (isset($post->post_type) && $post->post_type == 'product') {
		wp_register_script('wdm_edit_js', plugins_url('/js/wdm_edit_page.js', __FILE__), array( 'jquery' ));
		wp_register_script('wdm_edit_js_form', plugins_url('/js/wdm_scheduler_form.js', __FILE__), array( 'jquery' ));
		wp_enqueue_script('wdm_edit_js');
		wp_enqueue_script('wdm_edit_js_form');
		wp_enqueue_style('wdm-scheduler-style');
		wp_enqueue_script('wdm-scheduler-script');
		wp_register_style('wdm_edit_page_form', plugins_url('/css/wdm-scheduler-form.css', __FILE__));
		wp_enqueue_style('wdm_edit_page_form');
	}
}

function wdmSchedulerEnqueueScripts( $hook) {
	if ('post.php' == $hook || 'post-new.php' == $hook) {
		wp_enqueue_script('woo_scheduler_note');
		wp_enqueue_script('wdm_moment_js_handler');
		wp_enqueue_script('wdm_datepicker');
		wp_enqueue_script('chk-date-time');
		$wdmwsSettings = \Includes\AdminSettings\WdmWsSettings::getSettings();
		$subtype       = isset($wdmwsSettings) && isset($wdmwsSettings['wdmws_custom_product_expiration_type'][0]) ? $wdmwsSettings['wdmws_custom_product_expiration_type'][0] : 'per_day';
		wp_localize_script(
			'chk-date-time',
			'scheduler_option',
			array(
				'option' => $subtype,
				'empty_selection'   => __('You have not selected days. Click cancel to select days. If days are not selected the products are not visible', WDM_WOO_SCHED_TXT_DOMAIN),
				'err_start_end_time_empty' => __('Please enter start time and end time for scheduling the product.', WDM_WOO_SCHED_TXT_DOMAIN),
				'err_start_date_limit' => __('End date must be greater than start date', WDM_WOO_SCHED_TXT_DOMAIN),
				'err_start_time_limit' => __('End time must be greater than start time', WDM_WOO_SCHED_TXT_DOMAIN),
				'err_time_limit' => __('Start time must be greater than current time', WDM_WOO_SCHED_TXT_DOMAIN),
				'err_selection' => __("Day selected '", WDM_WOO_SCHED_TXT_DOMAIN),
				'err_day_selected'  => __('The days you have selected for variation id(s) ', WDM_WOO_SCHED_TXT_DOMAIN),
				'err_not_btwn'  => __("' does not lie between selected dates", WDM_WOO_SCHED_TXT_DOMAIN),
			)
		);

		wp_enqueue_style('wdm_bootstrap_css');
		wp_enqueue_style('wdm_datepicker_css');
	}
}

/**
 * Sets global variable of plugin data
 */
function schedulerLoadLicense() {
	global $wdmPluginDataScheduler;
	$wdmPluginDataScheduler = include_once 'license.config.php';
	require_once 'licensing/class-wdm-license.php';
	new \Licensing\WdmLicense($wdmPluginDataScheduler);
}

/**
 * Check date validation.
 *
 * @param [type] $product_id   product ID
 * @param string $product_type Product type
 *
 * @return [type] [description]
 */
function schedularBasePluginInactiveNotice() {
	// global $wdmPluginDataScheduler;
	?>
	 <div id="message" class="error">
	   <p>
	   <?php
		printf(__('%1$s %2$s is inactive. %3$s Install and activate %4$sWoocommerce%5$s for %6$s to work.', WDM_WOO_SCHED_TXT_DOMAIN), '<strong>', 'WooCommerce Scheduler', '</strong>', '<a href="https://wordpress.org/plugins/woocommerce/">', '</a>', 'WooCommerce Scheduler');
		?>
		</p>
	</div>
	<?php
}

function wdmWsValidateAvailability( $productId, $dateNow, $timeNow, $dayNow, $schedule) {
	$startDate        = get_post_meta($productId, 'wdm_start_date', true);
	$endDate          = get_post_meta($productId, 'wdm_end_date', true);
		$startTimeHr  = get_post_meta($productId, 'wdm_start_time_hr', true);
		$startTimeMin = get_post_meta($productId, 'wdm_start_time_min', true);
	$startTime        = getConcatenatedTime($startTimeHr, $startTimeMin, 'start');
		$endTimeHr    = get_post_meta($productId, 'wdm_end_time_hr', true);
		$endTimeMin   = get_post_meta($productId, 'wdm_end_time_min', true);
	$endTime          = getConcatenatedTime($endTimeHr, $endTimeMin, 'end');

	switch ($schedule['type']) {
		case 'productLaunch':
			return isAvailableAfterDateTime($dateNow, $timeNow, $startDate, $startTime);
			break;
		case 'wholeDay':
			return isAvailableToday($productId, $dateNow, $timeNow, $startDate, $startTime, $endDate, $endTime);
			break;
		case 'specificTime':
			return isAvailableTodayForDuration($productId, $dateNow, $timeNow);
			break;
		default:
			break;
	}
	unset($dayNow);
}

/**
 * wdmSchedulerAfterUpgrade
 * Function is called everytime after WordPress plugin/theme upgader completes the upgrade process.
 * Use of this function here is to check if this plugin is upgraded by the upgraded & to manage migration
 * process for the plugin.
 *
 * @param [type] $upgraderObject contain details for the recently completed upgrade process
 * @param [type] $options - list of upgradable entities.
 * @return void
 * @version 2.3.1
 */
function wdmSchedulerAfterUpgrade( $upgraderObject, $options) {
	$schedulerPathName = plugin_basename(__FILE__);
	if ($options['action'] == 'update' && $options['type'] == 'plugin') {
		foreach ($options['plugins'] as $pluginName) {
			if ($pluginName == $schedulerPathName) {
				$scheduler_install = new SchedulerInstall();
				$scheduler_install->schedulerOnActivation();
			}
		}
	}
	unset($upgraderObject);
}

