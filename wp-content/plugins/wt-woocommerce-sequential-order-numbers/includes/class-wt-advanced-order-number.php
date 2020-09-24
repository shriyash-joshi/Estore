<?php
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

class Wt_Advanced_Order_Number {

    protected $loader;
    protected $plugin_name;
    protected $version;

    public function __construct() {
        if (defined('WT_SEQUENCIAL_ORDNUMBER_VERSION')) {
            $this->version = WT_SEQUENCIAL_ORDNUMBER_VERSION;
        } else {
            $this->version = '1.2.3';
        }
        $this->plugin_name = 'wt-advanced-order-number';
        $this->plugin_base_name = WT_SEQUENCIAL_ORDNUMBER_BASE_NAME;

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    private function load_dependencies() {

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-wt-advanced-order-number-loader.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-wt-advanced-order-number-i18n.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-wt-advanced-order-number-admin.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-wt-advanced-order-number-public.php';

        $this->loader = new Wt_Advanced_Order_Number_Loader();
    }

    private function set_locale() {

        $plugin_i18n = new Wt_Advanced_Order_Number_i18n();
        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    private function define_admin_hooks() {

        $plugin_admin = new Wt_Advanced_Order_Number_Admin($this->get_plugin_name(), $this->get_version());

        $this->loader->add_filter('plugin_action_links_' . $this->get_plugin_base_name(), $plugin_admin, 'add_plugin_links_wt_wtsequentialordnum');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        $this->loader->add_filter('woocommerce_shop_order_search_fields', $plugin_admin, 'custom_ordernumber_search_field');

        add_action('plugins_loaded', array($this, 'setup_sequential_number'));
        add_action('admin_menu', array($this, 'wt_sequence_admin_menu'));
        add_action('init', array($this, 'wt_sequence_save_settings'), 20);
    }

    /**
     * Admin Menu
     */
    public function wt_sequence_admin_menu() {
        $page = add_submenu_page('woocommerce', __('Sequential Order Number', 'wt-woocommerce-sequential-order-numbers'), __('Sequential Order Number', 'wt-woocommerce-sequential-order-numbers'), apply_filters('webtoffee_sequential_order_number', 'manage_woocommerce'), 'sequential_order_number', array($this, 'wt_sequence_settings_page'));
    }

    public function wt_sequence_settings_page() {

        $wt_sequence_prefix = get_option('wt_sequence_order_number_prefix');
        $wt_sequence_start = get_option('wt_sequence_order_number_start');
        ?>

        <style>
            .tool-box{
                margin: 5px 0 15px;
            }
            .tool-box .bg-white{
                background: #fff;
                padding:20px;
            }
            .tool-box.bg-white{
                background: #fff;
            }
            .tool-box.p-20p{
                padding: 20px;
            }
            .tool-box #datagrid td input[type=text]{
                width: 100% !important;
            }
            .aw-title{
                font-size: 14px;
                padding: 8px 0px;
                margin: 0;
                line-height: 1.4;
                border-bottom: 1px solid #eee;
            }
            .form-table td{
                padding: 15px 0px !important;
            }

        </style>
        <div class="tool-box">
            <form action="<?php echo esc_attr(wp_nonce_url('admin.php?page=sequential_order_number&action=settings', 'wtsq-settings')); ?>" method="post">
                <div class="tool-box bg-white p-20p">                  
                    <?php if (!empty($_POST['wt_sequence_prefix'])): ?>
                        <div  style="padding:20px; width: 100%;" class="update message update-nag update-message"> Order Numbers Updated Successfully </div>
                    <?php endif; ?>

                    <h3 class="title aw-title"><?php _e('Settings', 'wt-woocommerce-sequential-order-numbers'); ?></h3>
                    <span style="font-size: 15px; color: red;">Updating this will regenerate all order numbers for new changes!</span>  
                    <table class="form-table">
                        <tr style="">
                            <td>
                                <input type="text" name="wt_sequence_prefix" id="wt_sequence_prefix"  value="<?php echo $wt_sequence_prefix; ?>" class="input-text" />
                                <p style="font-size: 12px"><?php _e('Enter Prefix', 'wt-woocommerce-sequential-order-numbers'); ?></p>
                            </td>
                        </tr>
                        <tr style="">
                            <td>
                                <input type="text" name="wt_sequence_start" id="wt_sequence_start"  value="<?php echo $wt_sequence_start; ?>" class="input-text" />
                                <p style="font-size: 12px"><?php _e('Enter Start Number', 'wt-woocommerce-sequential-order-numbers'); ?></p>
                            </td>
                        </tr>
                    </table>     

                    <p class="submit"><input type="submit" class="button button-primary" value="<?php _e('Save Settings', 'wt-woocommerce-sequential-order-numbers'); ?>" /></p>
                </div>
            </form>
        </div>

        <?php
    }

    public function wt_sequence_save_settings() {
        if (!empty($_GET['action']) && !empty($_GET['page']) && $_GET['page'] == 'sequential_order_number') {
            switch ($_GET['action']) {
                case "settings" :
                    self::save_settings();
                    break;
            }
        }
    }

    public static function save_settings() {

        check_admin_referer( 'wtsq-settings' );
        if (isset($_POST['wt_sequence_prefix'])) {

            update_option('wt_sequence_order_number_prefix', sanitize_text_field($_POST['wt_sequence_prefix']));
            update_option('wt_sequence_order_number_start', absint($_POST['wt_sequence_start']));
            self::initial_setup(TRUE);
        }
    }

    public function setup_sequential_number() {


        add_action('wp_insert_post', array($this, 'set_sequential_number'), 10, 2);
        add_action('woocommerce_process_shop_order_meta', array($this, 'set_sequential_number'), 10, 2);
        add_filter('woocommerce_order_number', array($this, 'display_sequence_number'), 10, 2);

        if (is_admin() && (!defined('DOING_AJAX'))) {
            self::initial_setup();
        }
    }

    public static function get_sequence_prefix() {

        $prefix = get_option('wt_sequence_order_number_prefix', '');

        return apply_filters('wt_order_number_sequence_prefix', $prefix);
    }

    public static function initial_setup($rerun = FALSE) {


        $wt_advanced_order_number_version = get_option('wt_advanced_order_number_version');


        if (!$wt_advanced_order_number_version || $rerun === TRUE) {

            $offset = (int) get_option('wt_advanced_order_number_offset', 0);

            $start = (int) get_option('wt_sequence_order_number_start', 1);


            $prefix = self::get_sequence_prefix();

            $posts_per_page = 50;

            do {
                $order_ids = get_posts(array('post_type' => 'shop_order', 'fields' => 'ids', 'offset' => $offset, 'posts_per_page' => $posts_per_page, 'post_status' => 'any', 'orderby' => 'date', 'order' => 'ASC'));


                if (!empty($order_ids)) {

                    foreach ($order_ids as $order_id) {
                        if (get_post_meta($order_id, '_order_number', TRUE) === '' || $rerun === TRUE) {
                            $order_number = $prefix . $start;
                            $order_number = apply_filters('wt_order_number_sequence_data', $order_number, $prefix, $order_id);
                            update_post_meta($order_id, '_order_number', $order_number);
                            $start++;
                        }
                    }
                }


                $offset += $posts_per_page;
            } while (count($order_ids) === $posts_per_page);


            update_option('wt_advanced_order_number_version', WT_SEQUENCIAL_ORDNUMBER_VERSION);
            update_option('wt_last_order_number', $start - 1);
        } else {
            update_option('wt_advanced_order_number_version', WT_SEQUENCIAL_ORDNUMBER_VERSION);
        }
    }

    private function define_public_hooks() {

        $plugin_public = new Wt_Advanced_Order_Number_Public($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
    }

    public function set_sequential_number($post_id, $post) {

        global $wpdb;

        if ($post->post_type === 'shop_order' && $post->post_status !== 'auto-draft') {

            $order = wc_get_order($post_id);
            $order_id = (WC()->version < '2.7.0') ? $order->id : $order->get_id();
            $order_number = get_post_meta($order_id, '_order_number', TRUE);

            if ($order_number === '') {

                $prefix = self::get_sequence_prefix();

                $nextnumber = 1;

                $last_order_num = get_option('wt_last_order_number');
                if (!$last_order_num) {

                    $query = "SELECT '_order_number', IF( MAX( CAST( meta_value as UNSIGNED ) ) IS NULL, 1, MAX( CAST( meta_value as UNSIGNED ) ) + 1 ) as NEXTNUM FROM {$wpdb->postmeta} WHERE meta_key='_order_number'";
                    $res = $wpdb->get_results($query);
                    $nextnumber = $res[0]->NEXTNUM;
                    $nextnumber = $nextnumber - 1;
                }


                $wt_last_order_number = get_option('wt_last_order_number', $nextnumber);
                $next_insert_id = $wt_last_order_number + 1;

                $next_order_number = $prefix . $next_insert_id;

                $sql = "INSERT INTO {$wpdb->postmeta} (post_id, meta_key, meta_value) VALUES (%d,%s,%s)";

                $query = $wpdb->prepare($sql, $post_id, '_order_number', $next_order_number);

                $res = $wpdb->query($query);

                update_option('wt_last_order_number', $next_insert_id);
            }
        }
    }

    public function get_plugin_name() {
        return $this->plugin_name;
    }

    public function get_plugin_base_name() {
        return $this->plugin_base_name;
    }

    public function get_loader() {
        return $this->loader;
    }

    public function get_version() {
        return $this->version;
    }

    public function display_sequence_number($order_number, $order) {

        $order_id = (WC()->version < '2.7.0') ? $order->id : $order->get_id();
        $sequential_order_number = get_post_meta($order_id, '_order_number', TRUE);
        return ($sequential_order_number) ? $sequential_order_number : $order_number;
    }

    public function run() {
        $this->loader->run();
    }

}
