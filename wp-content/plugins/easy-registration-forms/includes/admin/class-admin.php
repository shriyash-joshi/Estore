<?php
/**
 * Register menu elements.
 *
 * @package    ERForms
 * @author     ERForms
 * @since      1.0.0
 */
class ERForms_Admin {

	/**
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
            // Let's make some menus.
            add_action('admin_menu',array( $this, 'register_menus' ),9);
            add_action('admin_head', array($this,'admin_head') );
            add_action('admin_footer', array($this,'uninstall_dialog'));
            add_action('admin_enqueue_scripts', array($this,'load_scripts'));
	}
        
        /*
         * Load stuff in admin head for tinymce form add plugin
         */
        public function admin_head(){
            global $pagenow;
            $screen = get_current_screen();
            if(!empty($screen) && ($screen->id == 'post' || $screen->id == 'page')) {
                // check if WYSIWYG is enabled
                if ( get_user_option('rich_editing') == 'true') {
                    add_filter("mce_external_plugins", array($this,'embed_forms_button_js'));
                    add_filter('mce_buttons', array($this,'register_forms_button'));
                }
                $forms= erforms_get_forms_tinymce();
        ?>
                <script>
                    var erf_form_names= <?php echo $forms; ?>;
                </script>
        <?php
            }
        }
        
        /*
         * Enqueues JS file as external plugin in TinyMCE
         */
        public function embed_forms_button_js($plugin_array){
            $plugin_array['erf_forms_button'] = ERFORMS_PLUGIN_URL.'assets/admin/js/tinymce/forms-button.js';
            return $plugin_array;
        }
        
        /*
         * Registers Add Field button in TinyMCE
         */
        function register_forms_button($buttons) {
            array_push($buttons, "erf_forms_button");
            return $buttons;
        }
        
	/**
	 * Registering menus.
	 *
	 * @since 1.0.0
	 */
	function register_menus() {
                
		$menu_cap = apply_filters( 'erforms_manage_cap', 'manage_options' );

		// Default Forms top level menu item.
		add_menu_page(
			__( 'ERForms', 'erforms' ),
			__( 'ERForms', 'erforms' ),
			$menu_cap,
			'erforms-overview',
			array( $this, 'admin_page' ),
			'dashicons-feedback',
			apply_filters( 'erforms_menu_position', '57.7' )
		);

		// All Forms sub menu item.
		add_submenu_page(
			'erforms-overview',
			__( 'ERForms', 'erforms' ),
			__( 'All Forms', 'erforms' ),
			$menu_cap,
			'erforms-overview',
			array( $this, 'admin_page' )
		);
                
                // Add New sub menu item.
		add_submenu_page(
			null,
			__( 'ERForms Dashboard', 'erforms' ),
			__( 'Add New', 'erforms' ),
			$menu_cap,
			'erforms-dashboard',
			array( $this, 'admin_page' )
		);               

		// Submissions sub menu item.
		add_submenu_page(
			'erforms-overview',
			__( 'Form Submissions', 'erforms' ),
			__( 'Submissions', 'erforms' ),
			$menu_cap,
			'erforms-submissions',
			array( $this, 'admin_page' )
		);
                
                add_submenu_page(
			'erforms-submissions',
			__( 'Form Submission', 'erforms' ),
			__( 'Submission', 'erforms' ),
			$menu_cap,
			'erforms-submission',
			array( $this, 'admin_page' )
		);
                
                add_submenu_page(
			'erforms-overview',
			__( 'Labels', 'erforms' ),
			__( 'Labels', 'erforms' ),
			$menu_cap,
			'erforms-labels',
			array( $this, 'admin_page' )
		);

		// Settings sub menu item.
		add_submenu_page(
			'erforms-overview',
			__( 'ERForms Settings', 'erforms' ),
			__( 'Global Settings', 'erforms' ),
			$menu_cap,
			'erforms-settings',
			array( $this, 'admin_page' )
		);
                
                // Analytics sub menu item.
		add_submenu_page(
			'erforms-overview',
			__( 'ERForms Analytics', 'erforms' ),
			__( 'Analytics', 'erforms' ),
			$menu_cap,
			'erforms-analytics',
			array( $this, 'admin_page' )
		);
                
                // Plans sub menu item.
                add_submenu_page(
                        'erforms-overview',
                        __( 'Plans', 'erform' ),
                        __( 'Plans', 'erform' ),
                        $menu_cap,
                        'erforms-plans',
                        array( $this, 'admin_page' )
                );

                add_submenu_page(
                        'erforms-plans',
                        __( 'Plan', 'erform' ),
                        __( 'Plan', 'erform' ),
                        $menu_cap,
                        'erforms-plan',
                        array( $this, 'admin_page' )
                );
                
                add_submenu_page(
                    'erforms-overview',
                    __( 'ERForms', 'erforms'),
                    __( 'Form Import/Export', 'erforms'),
                    $menu_cap,
                    'erforms-tools',
                    array($this, 'admin_page')
                );
                
                do_action('erf_admin_menus',$this,$menu_cap);
                add_submenu_page(
			'erforms-overview',
			__( 'Help', 'erforms' ),
			__( 'Help', 'erforms' ),
			$menu_cap,
			'erforms-field-shortcodes',
			array( $this, 'admin_page' )
		);
                add_submenu_page(
			'erforms-overview',
			__( 'Extensions', 'erforms' ),
			__( 'Addons', 'erforms' ),
			$menu_cap,
			'erforms-addon',
			array( $this, 'admin_page' )
		);
	}

	/**
	 * Wrapper for the hook to render our custom settings pages.
	 *
	 * @since 1.0.0
	 */
	public function admin_page() {
            if(erforms_is_admin_page()){
                wp_enqueue_script('jquery');
                wp_enqueue_script('jquery-ui-core');
                wp_enqueue_style ('wp-jquery-ui-dialog');
                wp_enqueue_script('erf-util-functions');
                wp_enqueue_script ('erf-admin');         
                wp_enqueue_style('erf-admin-style');
                do_action( 'erf_admin_global_enqueues' );
            }
            do_action( 'erforms_admin_page' );
	}
        
        function load_scripts(){
            wp_register_script('erf-util-functions', ERFORMS_PLUGIN_URL . 'assets/js/utility-functions.js',array(),ERFORMS_VERSION);
            wp_register_script ('erf-admin' ,ERFORMS_PLUGIN_URL.'assets/admin/js/admin.js',
                        array('jquery-ui-dialog'),ERFORMS_VERSION);
            wp_register_script('erf-print-submission', ERFORMS_PLUGIN_URL . 'assets/js/printThis.js',array('jquery'));
            wp_register_style('erf-admin-style',ERFORMS_PLUGIN_URL.'assets/admin/css/style.css','',ERFORMS_VERSION);
            wp_localize_script('erf-admin','erf_admin_data',array('text_helpers'=>erforms_admin_text_helpers()));
            do_action('erf_register_admin_scripts');
        }
        
        public function uninstall_dialog(){
            $screen = get_current_screen();
            if(!is_admin() || !isset($screen->id))
                return;

            if (!in_array($screen->id, array('plugins', 'plugins-network' )))
                return;
            
            require_once 'utils/uninstall_dialog.php';
        }

}
new ERForms_Admin;
