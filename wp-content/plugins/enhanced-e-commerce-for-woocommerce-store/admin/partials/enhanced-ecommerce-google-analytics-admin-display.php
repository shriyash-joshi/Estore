<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       tatvic.com
 * @since      1.0.0
 *
 * @package    Enhanced_Ecommerce_Google_Analytics
 * @subpackage Enhanced_Ecommerce_Google_Analytics/admin/partials
 *
 */
if (!defined('ABSPATH')) {
    exit;
}

$site_url = "admin.php?page=enhanced-ecommerce-google-analytics-admin-display&tab=";

if(isset($_GET['tab']) && $_GET['tab'] == 'general_settings'){
    $general_class_active = "active";
}
else{
    $general_class_active = "";
}
if(isset($_GET['tab']) && $_GET['tab'] == 'about_plugin'){
    $advanced_class_active = "active";
}
else{
    $advanced_class_active = "";
}
if(empty($_GET['tab'])){
    $general_class_active = "active";
}
// date function to hide 30% off sale after certain date
$obj = new Enhanced_Ecommerce_Google_Analytics_Admin($plugin_name = 'enhanced-e-commerce-for-woocommerce-store', $version = PLUGIN_NAME_VERSION);
$today = $obj->today();
$start = $obj->start_date();
$end = $obj->end_date();
$currentime = $obj->current_time();
$endtime = $obj->end_time();
?>
<header class='background-color:#E8E8E8;height:500px;width:auto;margin-top:100px;margin-left:20px;'>
    <img class ="banner" src='<?php echo plugins_url('../images/banner.png', __FILE__ )  ?>' style="margin-left:10px;">
    <?php if($today >= $start && $today <= $end  && $currentime <= $endtime) { ?>
        <div class="banner-new">
            <p><img class="banner-blink" src='<?php echo plugins_url('../images/discount.gif', __FILE__ )  ?>' /> On the Premium Version Till 8th Sept 2020</p>
            <p class="clickhere-txt"><a href="https://codecanyon.net/item/actionable-google-analytics-for-woocommerce/9899552?utm_source=TatvicEE&utm_medium=DashboardBanner&utm_campaign=SeptCamp" target="_blank">Click here</a></p>
        </div>
    <?php } ?>
</header>
<ul class="nav nav-tabs nav-pills" style="margin-left: 10px;margin-top:20px;">
    <li class="nav-item">
        <a  href="<?php echo $site_url.'general_settings'; ?>"  class="border-left aga-tab nav-link <?php echo $general_class_active; ?>">General Settings</a>
    </li>
    <?php if($today >= $start && $today <= $end  && $currentime <= $endtime) {?>
        <li class="nav-item"><a href="<?php echo $site_url.'about_plugin'; ?>" class="border-left aga-tab nav-link <?php echo $advanced_class_active; ?>">Premium <img class="new-img-blink" src='<?php echo plugins_url('../images/discount.gif', __FILE__ )  ?>' /></a></li>
    <?php } else { ?>
        <li class="nav-item"><a href="<?php echo $site_url.'about_plugin'; ?>" class="border-left aga-tab nav-link <?php echo $advanced_class_active; ?>">Premium <img class="new-img-blink" src='<?php echo plugins_url('../images/new-2.gif', __FILE__ )  ?>' /></a></li>
    <?php } ?>
</ul>
