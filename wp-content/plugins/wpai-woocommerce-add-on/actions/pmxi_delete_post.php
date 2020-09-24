<?php

/**
 * @param $ids
 * @param $import
 */
function pmwi_pmxi_delete_post($ids, $import) {
    if (!empty($ids)) {
        foreach ($ids as $pid) {
            if ('product_variation' == get_post_type($pid)) {
                $variation = new WC_Product_Variation($pid);
                // Add parent product to sync circle after import completed.
                $productStack = get_option('wp_all_import_product_stack_' . XmlImportWooCommerceService::getInstance()->getImport()->id, array());
                if (!in_array($variation->get_parent_id(), $productStack)) {
                    $productStack[] = $variation->get_parent_id();
                    update_option('wp_all_import_product_stack_' . XmlImportWooCommerceService::getInstance()->getImport()->id, $productStack);
                }
            }
        }
    }
}