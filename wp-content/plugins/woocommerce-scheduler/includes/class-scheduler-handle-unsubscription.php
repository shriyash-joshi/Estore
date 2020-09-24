<?php
namespace Includes;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
/**
 * Handle Unsubcription, removing the user from the notification list functionality.
 * @author WisdmLabs
 */
if (!class_exists('SchedulerHandleUnsubscription')) {
    class SchedulerHandleUnsubscription
    {
        public function __construct()
        {
            add_action('wdmws_notification_unsubscription_content', array($this, 'handleNotificationUnsubscription'));
        }

        /**
         * Triggers the display for Shortcode.
         * Action for adding Unsubscription option.
         * @return string $getContent ob contentS
         */
        public static function unsubscriptionShortcodeCallback()
        {
            ob_start();
            do_action('wdmws_notification_unsubscription_content');
            $getContent = ob_get_contents();
            ob_end_clean();

            return $getContent;
        }
        
        /**
         * Determines whether unsubscription option should be displayed to the user.
         */
        public function handleNotificationUnsubscription()
        {
            $wdmwsUnsubscribe = $this->getUnsubscriptionKey();
            $enrollmentTable = wdmwsReturnEnrlUserListTable();
            $enrollmentData = array();

            if (false === $wdmwsUnsubscribe || empty($enrollmentData = $this->getProductIdUserEmail($enrollmentTable, $wdmwsUnsubscribe))) {
                return;
            }

            wp_enqueue_script('wdmws_unsubscribe_js');
            wp_enqueue_style('wdmws_unsubscribe_css');
            wp_localize_script(
                'wdmws_unsubscribe_js',
                'wdmws_unsubscribe',
                array(
                    'admin_ajax' => admin_url('admin-ajax.php'),
                )
            );

            $this->displayDisenrollmentOption($enrollmentData[0]);
        }

        /**
         * Returns the unsubcription key.
         *
         * @return string|bool Returns the unsubscription key or false if not exists.
         */
        private function getUnsubscriptionKey()
        {
            if (isset($_GET['wdmws_unsubscribe'])) {
                return $_GET['wdmws_unsubscribe'];
            }

            return false;
        }

        /**
         * Returns the product id and user email from the unsubscription
         * key ($wdmwsUnsubscribe).
         *
         * @param string $enrollmentTable  Enrollment data table name.
         * @param string $wdmwsUnsubscribe Unsubscription Key
         *
         * @return array Returns array containing the id, product id, user email
         *               for the unsubscription key or an empty array.
         */
        public function getProductIdUserEmail($enrollmentTable, $wdmwsUnsubscribe)
        {
            global $wpdb;
            $query = "SELECT id, product_id, user_email FROM ".$enrollmentTable." WHERE unsubscription_link='".$wdmwsUnsubscribe."'";

            $results = $wpdb->get_results($query, ARRAY_A);
            return $results;
        }

        /**
         * Display the form so that the user can unsubscribe from the mailing list.
         *
         * @param array $enrollmentData Array containing product ID and enrolled
         *                              user's email address.
         */
        public function displayDisenrollmentOption($enrollmentData)
        {
            $productId      = $enrollmentData['product_id'];
            $userEmail     = $enrollmentData['user_email'];
            $product        = wc_get_product($productId);
            $productTitle   = $product->get_title();

            // Unsubscribe Text
            $unsubscriptionHeadingText = apply_filters('wdmws_unusubscribe_heading_text', sprintf(__('%s is subscribed to our mailing list(s).', WDM_WOO_SCHED_TXT_DOMAIN), $userEmail), $enrollmentData);
            $unsubscribeProductText = apply_filters('wdmws_unsubscribe_from_product_text', sprintf(__('Unsubscribe for %s product.', WDM_WOO_SCHED_TXT_DOMAIN), $productTitle), $enrollmentData);
            $unsubscribeAllProductsText = apply_filters('wdmws_unsubscribe_from_all_products_text', __('Unsubscribe for all products.', WDM_WOO_SCHED_TXT_DOMAIN), $enrollmentData);

            // Unsubscription Response Text
            $productUnsubscriptionReponse = sprintf(__('%s is unsubscribed from %s product.', WDM_WOO_SCHED_TXT_DOMAIN), $userEmail, $productTitle);
            $allProductsUnsubscriptionReponse = sprintf(__('%s is unsubscribed from all products.', WDM_WOO_SCHED_TXT_DOMAIN), $userEmail);
            $productUnsubscriptionReponse = apply_filters('wdmws_product_unsubscription_response', $productUnsubscriptionReponse, $userEmail, $productId);
            $allProductsUnsubscriptionReponse = apply_filters('wdmws_allproduct_unsubscription_response', $allProductsUnsubscriptionReponse, $userEmail, $productId);
            ?>
            <div class="unsubscription-options-wrapper" id="unsubscription-options-wrapper">
                <h4><?php echo $unsubscriptionHeadingText; ?></h4>

                <form method="POST">
                    <?php wp_nonce_field('wdmws_unsubscription_option'); ?>
                    <div class="wdmws-unsubscription-div" style="display: block;">
                        <input type="radio" id="wdmws-prod-unsub-radio" name="wdmws-unsubscription" value="product" checked><?php echo $unsubscribeProductText; ?><br>
                        <input type="radio" id="wdmws-allprod-unsub-radio" name="wdmws-unsubscription" value="allProducts"><?php echo $unsubscribeAllProductsText; ?><br>
                        <button type="button" class="button wdmws-unsubscribe-submit" id="wdmws-unsubscribe-submit" data-product-id="<?php echo $productId; ?>" data-user-email="<?php echo $userEmail; ?>" data-product-name="<?php echo $productTitle; ?>">
                        <?php echo apply_filters('wdmws_unsubscribe_submit_text', __('Unsubscribe', WDM_WOO_SCHED_TXT_DOMAIN), $productId, $userEmail); ?>
                        </button>
                    </div>

                    <!-- Unsubscription Response -->
                    <label id="wdmws-product-unsubscription-message" class="wdmws-unsubscription-message"><?php echo $productUnsubscriptionReponse; ?></label>
                    <label id="wdmws-allproduct-unsubscription-message" class="wdmws-unsubscription-message"><?php echo $allProductsUnsubscriptionReponse; ?></label>
                    <label id="wdmws-error-unsubscription-message" class="wdmws-unsubscription-message"></label>
                </form>
            </div>
            <?php
        }

        /**
         * Removes the user from the notification list of a particular product.
         *
         * @param $userEmail string User Email ID.
         * @param $productId int    Product ID from which user will be disenrolled.
         *
         * @return int|bool Returns the number of rows updated, or false on error
         */
        public function disenrollUserFromEnrlList($userEmail, $productId)
        {
            global $wpdb;
            $table = wdmwsReturnEnrlUserListTable();
            $where = array('product_id' => $productId, 'user_email' => $userEmail);

            return $wpdb->delete($table, $where);
        }

        /**
         * Removes the user from all products notification list.
         *
         * @param $userEmail string User Email ID.
         *
         * @return int|bool Returns the number of rows updated, or false on error.
         */
        public function disenrollUserFromAllList($userEmail)
        {
            global $wpdb;
            $table = wdmwsReturnEnrlUserListTable();
            $where = array('user_email' => $userEmail);

            return $wpdb->delete($table, $where);
        }
    }
    new SchedulerHandleUnsubscription();
}
