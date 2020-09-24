<?php
if ( ! function_exists('berocket_aapf_bodycommerce_archive_module_args') ) {
    add_filter('db_archive_module_args', 'berocket_aapf_bodycommerce_archive_module_args');
    function berocket_aapf_bodycommerce_archive_module_args( $new_args ) {
        if ( class_exists('BeRocket_AAPF') ) {
            $BeRocket_AAPF = BeRocket_AAPF::getInstance();
            $new_args = $BeRocket_AAPF->woocommerce_filter_query_vars( $new_args );
        }

        return $new_args;
    }
}
