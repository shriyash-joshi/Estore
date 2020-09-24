<?php
namespace Includes\AdminSettings;

if (!class_exists('schedulerAdminSettings')) {
    
    /**
     * This class extends wordpress settings API to register & display the settings
     * on the scheduler settings page.
     * 
     * @since 3.0.0
     */
    class schedulerAdminSettings {
        
        public function __construct() {
            $this->addSchedulerSettingSections();
            $this->addSettingFields();
            $this->registerSchedulerSettings();
        }
        
        /**
         * This method adds setting sections according to wordpress settings API
         *
         * @return void
         */
        public function addSchedulerSettingSections() {
            add_settings_section('wdmws_messages_setting_section',
                                esc_html__('Availability Messages', 'woocommerce-scheduler'), 
                                array($this, 'availabilityMessagesSectionDescription'),
                                'wdmws_global_settings'    
                            );

            add_settings_section('wdmws_timer_setting_section',
                                esc_html__('Scheduler Timer Color Settings', 'woocommerce-scheduler'), 
                                array($this, 'timerSettingSectionDescription'),
                                'wdmws_global_settings'    
                            );
        }

        /**
         * This method registers all the settings using function register_setting.
         *
         * @return void
         */
        public function registerSchedulerSettings() {
            //Availability Messages Settings
            $args = array('type' => 'string', 'sanitize_callback' => 'sanitize_text_field', 'default' => null);
            $args['default'] = 'Currently Unavailable';
            register_setting('wdmws_global_settings', 'wdmws_custom_product_expiration', $args);
            $args['default'] = 'Unavailable';
            register_setting('wdmws_global_settings', 'wdmws_custom_product_shop_expiration', $args);
            $args['default'] = 'This product will be available in';
            register_setting('wdmws_global_settings', 'wdmws_start_timer_text', $args);
            $args['default'] = 'This product will be available only for';
            register_setting('wdmws_global_settings', 'wdmws_end_timer_text', $args);
            $args['default'] = 'This product will be launched in';
            register_setting('wdmws_global_settings', 'wdmws_launch_start_timer_text', $args);
            
            //Timer Color Settings
            $args = array('type' => 'string', 'sanitize_callback' => 'sanitize_hex_color', 'default' => '#dd3333');
            register_setting('wdmws_global_settings', 'wdmws_font_color', $args);
            $args['default'] = '#EEEEEE';
            register_setting('wdmws_global_settings', 'wdmws_background_color', $args);
            $args['default'] = '#CCCCCC';
            register_setting('wdmws_global_settings', 'wdmws_front_color', $args);
        }


        /**
         * This method uses wp Settings API function add_setting_field
         * to register the callback method to show the setting field
         * on front end.
         *
         * @return void
         */
        public function addSettingFields() {
            //Availability Messages Settings
            add_settings_field('wdmws_custom_product_expiration', esc_html__('Single Product Expiration', 'woocommerce-scheduler'), array($this, 'showSingleProductExpirationMessageSetting'), 'wdmws_global_settings', 'wdmws_messages_setting_section');
            add_settings_field('wdmws_custom_product_shop_expiration', esc_html__('Shop Page Expiration', 'woocommerce-scheduler'), array($this, 'showShopPageExpirationMessageSetting'), 'wdmws_global_settings', 'wdmws_messages_setting_section');
            add_settings_field('wdmws_start_timer_text', esc_html__('Text before the product becomes available', 'woocommerce-scheduler'), array($this, 'showTextBeforeTimerSettingForUnavailableProduct'), 'wdmws_global_settings', 'wdmws_messages_setting_section');
            add_settings_field('wdmws_end_timer_text', esc_html__('Text after the product becomes available', 'woocommerce-scheduler'), array($this, 'showTextBeforeTimerSettingForAvailableProduct'), 'wdmws_global_settings', 'wdmws_messages_setting_section');
            add_settings_field('wdmws_launch_start_timer_text', esc_html__('Text before the product launch', 'woocommerce-scheduler'), array($this, 'showLaunchTextTimerSettingForAvailableProduct'), 'wdmws_global_settings', 'wdmws_messages_setting_section');            
            
            //Scheduler Timer Color Settings
            add_settings_field('wdmws_font_color', esc_html__('Color for Timer Texts', 'woocommerce-scheduler'), array($this, 'showColorPickerForFont'), 'wdmws_global_settings', 'wdmws_timer_setting_section');
            add_settings_field('wdmws_background_color', esc_html__('Base Ring Colour', 'woocommerce-scheduler'), array($this, 'showColorPickerForBaseRing'), 'wdmws_global_settings', 'wdmws_timer_setting_section');
            add_settings_field('wdmws_front_color', esc_html__('Top Ring Colour', 'woocommerce-scheduler'), array($this, 'showColorPickerForTopRing'), 'wdmws_global_settings', 'wdmws_timer_setting_section');
        }
    

        /*********************************************************
         * Setting Field Input Elements : Availability Messages  *
         *********************************************************/
        public function showSingleProductExpirationMessageSetting() {
            $singleProductExpMessage    = get_option('wdmws_custom_product_expiration');
            $description                = esc_html__('This message will be displayed on the product page when the product is not available according to the schedule', 'woocommerce-scheduler');
            ?>
            <input type="text" name="wdmws_custom_product_expiration" id="wdmws_custom_product_expiration" aria-describedby="desc-wdmws-custom-product-expiration" class="regular-text" value="<?php echo esc_attr($singleProductExpMessage); ?>">
            <p class="description" id="desc-wdmws-custom-product-expiration"><?php echo $description; ?></p>
            <?php
        }

        public function showShopPageExpirationMessageSetting() {
            $shopPageExpMessage = get_option('wdmws_custom_product_shop_expiration');
            $description        = esc_html__('This will be shown on the shop page instead of the add to cat button when the products are unavailable', 'woocommerce-scheduler');
            ?>
            <input type="text" name="wdmws_custom_product_shop_expiration" id="wdmws_custom_product_shop_expiration" class="regular-text" area-describedby="desc-wdmws-custom-shop-expiration" value="<?php echo esc_attr($shopPageExpMessage); ?>">
            <p class="description" id="desc-wdmws-custom-shop-expiration"><?php echo $description; ?></p>
            <?php
        }

        public function showTextBeforeTimerSettingForUnavailableProduct() {
            $startTimerText = get_option('wdmws_start_timer_text');
            $description    = esc_html__('This will be displayed over the timer where the product is going to get unavailable', 'woocommerce-scheduler');
            ?>
            <input type="text" name="wdmws_start_timer_text" id="wdmws_start_timer_text" class="regular-text" value="<?php echo esc_attr($startTimerText); ?>" area-describedby="desc-wdmws-custom-before-avl-msg">
            <p class="description" id="desc-wdmws-custom-before-avl-msg"><?php echo $description; ?></p>
            <?php
        }

        public function showTextBeforeTimerSettingForAvailableProduct() {
            $endTimerText   = get_option('wdmws_end_timer_text');
            $description    = esc_html__('This will be displayed over the timer where the product is going to get available', 'woocommerce-scheduler');
            ?>
            <input type="text" name="wdmws_end_timer_text" id="wdmws_end_timer_text" class="regular-text" value="<?php echo esc_attr($endTimerText); ?>" area-describedby="desc-wdmws-custom-before-unavl-msg">
            <p class="description" id="desc-wdmws-custom-before-unavl-msg"><?php echo $description; ?></p>
            <?php
        }
        

        public function showLaunchTextTimerSettingForAvailableProduct() {
            $launchTimerText   = get_option('wdmws_launch_start_timer_text');
            $description    = esc_html__('This will be displayed over the timer where the launch schedule is applied', 'woocommerce-scheduler');
            ?>
            <input type="text" name="wdmws_launch_start_timer_text" id="wdmws_launch_start_timer_text" aria-describedby="desc-wdmws-custom-product-launch" class="regular-text" value="<?php echo esc_attr($launchTimerText); ?>">
            <p class="description" id="desc-wdmws-custom-product-launch"><?php echo $description; ?></p>
            <?php
        }

        /**********************************************
         * Setting Field Input Elements : TimerColors *
         **********************************************/
        public function showColorPickerForFont() {
            $selectedColor = get_option('wdmws_font_color');
            $description    = esc_html__('Select the color for the font of the timers', 'woocommerce-scheduler');
            ?>
            <input type="color" name="wdmws_font_color" id="wdmws_font_color" aria-describedby="desc-font-color" class="regular-text" value="<?php echo esc_attr($selectedColor); ?>">
            <p class="description" id="desc-font-color"> <?php echo $description; ?></p>
            <?php
        }

        public function showColorPickerForBaseRing() {
            $selectedColor  = get_option('wdmws_background_color');
            $description    = esc_html__('Select the color for the base timer circle', 'woocommerce-scheduler');
            ?>
            <input type="color" name="wdmws_background_color" id="wdmws_background_color" aria-describedby="desc-base-color" class="regular-text" value="<?php echo esc_attr($selectedColor); ?>">
            <p class="description" id="desc-base-color"> <?php echo $description; ?></p>
            <?php
        }

        public function showColorPickerForTopRing() {
            $selectedColor = get_option('wdmws_front_color');
            $description    = esc_html__('Select the color for the top timer circle', 'woocommerce-scheduler');
            ?>
            <input type="color" name="wdmws_front_color" id="wdmws_front_color" aria-describedby="desc-front-color"  value="<?php echo esc_attr($selectedColor); ?>">
            <p class="description" id="desc-front-color"> <?php echo $description; ?></p>
            <?php
        }

        
    

        /*****************************************
         * Descriptions for the setting sections *
         *****************************************/
        public function availabilityMessagesSectionDescription() {
            //esc_html_e('Enter the messages to be shown on the different schedule conditions', 'woocommerce-scheduler');
        }

        public function timerSettingSectionDescription() {
            ?>
            <style>
            input[type="color"] {
                                border: 1px solid gray;
                                width: 48px;
                                height: 28px;
                                padding: 2px 2px;
                                border-radius: 3px;
                                background: lightgray;
                           }
            input[type="color"]::-webkit-color-swatch-wrapper {
            	            padding: 0;
                        }
            input[type="color"]::-webkit-color-swatch {
            	            border: none;
                        }
            </style>
            <?php
            //esc_html_e('Settings for the timer sections', 'woocommerce-scheduler');
        }
    }
}