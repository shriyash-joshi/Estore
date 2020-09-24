<?php
namespace Includes\Admin\Privacy;

/**
 * Privacy related functionality which ties into WordPress functionality.
 *
 */
defined('ABSPATH') || exit;

/**
 * WC_Privacy Class.
 */
class SchedulerPrivacy
{
     /**
     * This is the name of this object type.
     *
     * @var string
     */
    public $name;

    /**
     * This is a list of exporters.
     *
     * @var array
     */
    protected $exporters = array();

    /**
     * This is a list of erasers.
     *
     * @var array
     */
    protected $erasers = array();

    /**
     * Constructor.
     *
     * @param string $name Plugin identifier.
     */
    public function __construct($name = '')
    {
        $this->name = $name;
        $this->init();
    }

    /**
     * Hook in events.
     */
    protected function init()
    {
        add_action('admin_init', array($this, 'addPrivacyMessage'));
        add_filter('wp_privacy_personal_data_exporters', array($this, 'registerExporters'), 10);
        add_filter('wp_privacy_personal_data_erasers', array($this, 'registerErasers'));

         // This hook registers Scheduler data erasers.
        $this->addEraser('scheduler-notify-eraser', __('Notify User', WDM_WOO_SCHED_TXT_DOMAIN), array($this, 'notifyFeatureDataEraser'));
        // This hook registers Scheduler data exporters.
        $this->addExporter('scheduler-notify-exporter', __('Notify User', WDM_WOO_SCHED_TXT_DOMAIN), array($this, 'notifyFeatureDataExporter'));
    }

    /**
     * Adds the privacy message on WC privacy page.
     */
    public function addPrivacyMessage()
    {
        if (function_exists('wp_add_privacy_policy_content')) {
            $content = $this->getPrivacyMessage();

            if ($content) {
                wp_add_privacy_policy_content($this->name, $content);
            }
        }
    }

    /**
     * Integrate this exporter implementation within the WordPress core exporters.
     *
     * @param array $exporters List of exporter callbacks.
     *
     * @return array
     */
    public function registerExporters($exporters = array())
    {
        foreach ($this->exporters as $id => $exporter) {
            $exporters[ $id ] = $exporter;
        }

        return $exporters;
    }

    /**
     * Integrate this eraser implementation within the WordPress core erasers.
     *
     * @param array $erasers List of eraser callbacks.
     *
     * @return array
     */
    public function registerErasers($erasers = array())
    {
        foreach ($this->erasers as $id => $eraser) {
            $erasers[ $id ] = $eraser;
        }

        return $erasers;
    }

    /**
     * Add exporter to list of exporters.
     *
     * @param string $id       ID of the Exporter.
     * @param string $name     Exporter name.
     * @param string $callback Exporter callback.
     */
    public function addExporter($exporterId, $name, $callback)
    {
        $this->exporters[ $exporterId ] = array(
            'exporter_friendly_name' => $name,
            'callback' => $callback,
        );

        return $this->exporters;
    }

    /**
     * Add eraser to list of erasers.
     *
     * @param string $id       ID of the Eraser.
     * @param string $name     Exporter name.
     * @param string $callback Exporter callback.
     */
    public function addEraser($eraserId, $name, $callback)
    {
        $this->erasers[ $eraserId ] = array(
            'eraser_friendly_name' => $name,
            'callback' => $callback,
        );

        return $this->erasers;
    }

    /**
     * Add privacy policy content for the privacy policy page.
     *
     */
    public function getPrivacyMessage()
    {
        ob_start();
        ?>
        <div>
        <p>This is sample text for the policies your Privacy Policy should include, with respect to Wisdm Scheduler for WooCommerce. Depending on what settings are enabled, the specific information shared by your store will vary.</p>

        <p>We recommend consulting a lawyer when deciding what information to disclose in your Privacy Policy. You are free to make edits as necessary and add to the content below.</p>

        <p>We collect user information whenever you want to get notified on emails if the products are back in stock. When you click on 'notify me' we’ll ask you to provide following information<sup>1</sup>:</p>
        <ol>
            <li>Your Email Address</li>
        </ol>

        <p>[1] Note to Admin - You can add any other relevant reasons that justify your collection of user information.</p>

        <p>By clicking on notify me, you allow Site Owner to send you email when the product gets back in stock. <sup>4</sup></p>

        </div>
        <?php
        $content = ob_get_clean();

        return apply_filters('scheduler_privacy_policy_content', $content);
    }

    /**
     * Finds and erase notify feature data by email address.
     *
     * @param string $emailAddress  The user email address.
     * @param int    $page          Page.
     *
     * @return array
     */
    public function notifyFeatureDataEraser($emailAddress, $page)
    {
        global $wpdb;
        $response = array(
            'items_removed' => false,
            'items_retained' => false,
            'messages' => array(),
            'done' => true,
        );
        $isRemoved = false;
        $enrlmentTable = wdmwsReturnEnrlUserListTable();

        $query = "SELECT id, product_id FROM ".$enrlmentTable." WHERE 
        user_email='".$emailAddress."'";

        $enrolmentData = $wpdb->get_results($query, ARRAY_A);

        foreach ($enrolmentData as $value) {
            $enrollmentId = $value['id'];
            $productId = $value['product_id'];
            if (true == SchedulerPrivacy::eraseCustDataNotifyFeature($enrollmentId)) {
                $isRemoved = true;
                $product = wc_get_product($productId);

                $response['messages'][] = sprintf(__('Removed from the notification list for the product %s (#%d).', WDM_WOO_SCHED_TXT_DOMAIN), $product->get_title(), $productId);
                $response['items_removed'] = true;
            }
        }

        unset($page);
        return $response;
    }

    /**
     * Removes the customer email address from the list.
     *
     * @param int    $enrollmentId     Enrollement Id for the user corresponding
     *                                 to the product.
     *
     * @return bool  Returns true if customer is removed from the notification list,
     *               false otherwise.
     */
    public static function eraseCustDataNotifyFeature($enrollmentId)
    {
        global $wpdb;
        $enrollmentTable = wdmwsReturnEnrlUserListTable();
        $isDataRemoved = false;
        $where = array('id' => $enrollmentId);
        $isDataRemoved = $wpdb->delete($enrollmentTable, $where);

        if (false == $isDataRemoved) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Finds and exports notify feature related data by email address.
     *
     * @param string $emailAddress  The user email address.
     * @param int    $page          Page.
     *
     * @return array
     */
    public static function notifyFeatureDataExporter($emailAddress, $page)
    {
        global $wpdb;
        $data_to_export = array();
        $productNames = array();
        $enrlmentTable = wdmwsReturnEnrlUserListTable();
        $query = "SELECT product_id FROM ".$enrlmentTable." WHERE 
        user_email='".$emailAddress."'";
        $productIds = $wpdb->get_col($query);

        foreach ($productIds as $productId) {
            $product = wc_get_product($productId);
            array_push($productNames, $product->get_title());
        }

        if (0 < count($productNames)) {
            $data = array();

            array_push(
                $data,
                array(
                    'name'  => 'Email',
                    'value' => $emailAddress
                )
            );

            array_push(
                $data,
                array(
                    'name'  => 'Products',
                    'value' => implode(', ', $productNames)
                )
            );

            $data_to_export[] = array(
                'group_id' => 'scheduler_product_notification',
                'group_label' => __('Enrolled for Notification', WDM_WOO_SCHED_TXT_DOMAIN),
                'item_id' => 'product-notification',
                'data' => $data
            );
        }

        unset($page);
        return array(
            'data' => $data_to_export,
            'done' => true,
        );
    }
}

new SchedulerPrivacy(__('WooCommerce Scheduler', WDM_WOO_SCHED_TXT_DOMAIN));
