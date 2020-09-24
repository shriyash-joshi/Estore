<?php
class ERForms_WC
{   
    private $from= null;
    /**
     * Primary class constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {
        add_action('erforms_loaded',array($this,'initialize'));
    }
    
    public function initialize(){
        $options= erforms()->options->get_options();
        if (!erforms_is_woocommerce_activated()){
            return;
        }
        if(!empty($options['en_wc_my_account'])){
            add_action('init', array($this, 'woocommerce_end_point'));
            add_filter('query_vars', array($this, 'woocommerce_query_vars'), 0);
            add_action('woocommerce_account_erf-my-account_endpoint', array($this, 'woocommerce_endpoint_content'));
            add_filter('woocommerce_account_menu_items', array($this,'woocommerce_sort_menu'));
            add_filter('the_title', array($this,'woocommerce_endpoint_title'), 10, 2 ); 
        }
        
    }
    
    
    public function woocommerce_end_point() {
        add_rewrite_endpoint('erf-my-account', EP_ROOT | EP_PAGES);
    }

    public function woocommerce_query_vars($vars) {
        $vars[] = 'erf-my-account';
        return $vars;
    }

    public function woocommerce_endpoint_content() {
        echo do_shortcode('[erforms_my_account wc="1"]');
    }

    // Adds Submissions link before logout link
    public function woocommerce_sort_menu($menu_links) {
        $logout_exists = false;
        if(isset($menu_links['customer-logout'])){
            unset($menu_links['customer-logout']);
            $logout_exists = true;
        }
        $menu_links['erf-my-account'] = __('Submissions','erforms');
        if($logout_exists){
            $menu_links['customer-logout'] = __('Logout','woocommerce');
        }
        return $menu_links;
    }
    
    public function woocommerce_endpoint_title($title, $id=0){
        global $wp_query;
        $is_endpoint = isset( $wp_query->query_vars['erf-my-account'] );
        if ($is_endpoint && is_main_query() && in_the_loop() && is_account_page()) {
            $title = __('Submissions','erforms');
            remove_filter( 'the_title', array( $this, 'woocommerce_endpoint_title' ) );
	}
        return $title;
    }
    
    
}

new ERForms_WC;