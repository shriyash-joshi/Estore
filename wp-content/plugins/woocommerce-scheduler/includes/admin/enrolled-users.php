<?php
wp_enqueue_style('notify_me_settings_css');
wp_enqueue_style('datatables_semantic_css');
wp_enqueue_style('datatables_semanticui_css');

if (!isset($_GET['product_id'])) :
    ?>
    <style>
    .wdmws-page-title {
                font-size:28px;
                font-weight:400;
                color:#000000;
                line-height:1.2rem;
            }
    </style>
    <h1 class="wdmws-page-title"><?php esc_html_e('Enrolled Users', 'woocommerce-scheduler');?></h1>
    <table id="enrolled-users-count" class="ui celled table">
        <thead>
            <tr>
                <th><?php echo __('Products', WDM_WOO_SCHED_TXT_DOMAIN); ?></th>
                <th><?php echo __('Number of Enrolled Users', WDM_WOO_SCHED_TXT_DOMAIN); ?></th>
            </tr>
        </thead>
    </table>
    <?php
else :
    $productId = $_GET['product_id'];
    $product = wc_get_product($productId);
    $productTitle = empty($product) ? '' : $product->get_title();
    $enrolledUsersCountPage = admin_url().'admin.php?page=wdmws_settings_enrolled_users';
    ?>
    <h3 class="enrolled-product-name">
        For <?php echo $productTitle.'(#'.$productId.')'; ?>
        <small class="wc-admin-breadcrumb">
            <a href="<?php echo esc_url($enrolledUsersCountPage); ?>">
                <img draggable="false" class="emoji" alt="⤴" src="https://s.w.org/images/core/emoji/11/svg/2934.svg">
            </a>
        </small>
    </h3>
    <table id="enrolled-users" class="ui celled table" data-product-id="<?php echo $productId; ?>">
        <thead>
            <tr>
                <th><?php echo __('User Email', WDM_WOO_SCHED_TXT_DOMAIN); ?></th>
                <th><?php echo __('Enrolled At', WDM_WOO_SCHED_TXT_DOMAIN); ?></th>
                <th><?php echo __('Action', WDM_WOO_SCHED_TXT_DOMAIN); ?></th>
            </tr>
        </thead>
    </table>

    <button type="button" id="export-users-list" class="export-users-list button button-primary" value="<?php echo $productId; ?>"><?php echo __('Export data', WDM_WOO_SCHED_TXT_DOMAIN); ?></button>
    <p id="export-data-error"></p>
    <?php
endif;

wp_enqueue_script('jquery_datatables_js');
wp_enqueue_script('datatables_semantic_js');
wp_enqueue_script('datatables_semanticui_js');
wp_enqueue_script('notify_me_settings_js');

wp_localize_script(
    'notify_me_settings_js',
    'export_data',
    array(
        'security_check'  => __('Don\'t have access to export', WDM_WOO_SCHED_TXT_DOMAIN),
        'user_list_empty' => __('User List is empty', WDM_WOO_SCHED_TXT_DOMAIN)
    )
);