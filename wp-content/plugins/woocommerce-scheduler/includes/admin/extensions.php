<?php
wp_enqueue_style('wdmws_other_extension_css');
global $wdmPluginDataScheduler;
$min = (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG === true) ? '':'.min';
if (false === ($extensions = get_transient('_wdmcsp_extensions_data'))) {
    $extensions_json = wp_remote_get(
        'https://wisdmlabs.com/products-thumbs/woo_extension.json',
        array(
            'user-agent' => 'Woocommerce Extensions Page'
        )
    );

    if (!is_wp_error($extensions_json)) {
        $extensions = json_decode(wp_remote_retrieve_body($extensions_json));

        if ($extensions) {
            set_transient('_wdmcsp_extensions_data', $extensions, 72 * HOUR_IN_SECONDS);
        }
    }
}

wp_register_style($wdmPluginDataScheduler['pluginSlug'] .'-promotion', plugins_url('css/extension' . $min . '.css', dirname(dirname(__FILE__))), array(), $wdmPluginDataScheduler['pluginVersion']);

// Enqueue admin styles
wp_enqueue_style($wdmPluginDataScheduler['pluginSlug'] .'-promotion');

?>
<div id="csp-other-extensions">
    <?php
    if ($extensions) {
        ?>
        <ul class="extensions">
        <?php
            $extensions = $extensions->woo_extension;
            $i = 0;
        foreach ($extensions as $extension) {
            if ($i > 7) {
                break;
            }

            // If plugin is already installed, don't list this plugin.
            if (file_exists(WP_PLUGIN_DIR . "/" . $extension->dir . "/" . $extension->plug_file)) {
                continue;
            }

            echo '<li class="product" title="' . __('Click here to know more', WDM_WOO_SCHED_TXT_DOMAIN) . '">';
            echo '<a href="'.$extension->link.'" target="_blank">';
            echo '<h3>'.$extension->title.'</h3>';
            if (!empty($extension->image)) {
                echo '<img src="'.$extension->image.'"/>';
            }
            echo '<p>'.$extension->excerpt.'</p>';
            echo '</a>';
            echo '</li>';
            ++$i;
        }
        ?>
        </ul>
        <?php
        // If all the extensions have been installed on the site.
        if (0 == $i) {
            ?>
        <h1 class="thank-you"><?php _e('You have all of our extensions. Thank you for your support!', WDM_WOO_SCHED_TXT_DOMAIN); ?></h1>
            <?php
        }
    }
    ?>
    <p>
        <a href="https://wisdmlabs.com/woocommerce-extensions/" target="_blank" class="browse-all">
        <?php _e('Browse all our extensions', WDM_WOO_SCHED_TXT_DOMAIN); ?>
        </a>
    </p>
</div>
