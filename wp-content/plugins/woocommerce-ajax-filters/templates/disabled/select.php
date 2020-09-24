<?php
/**
 * The template for displaying checkbox filters
 *
 * Override this template by copying it to yourtheme/woocommerce-filters/checkbox.php
 *
 * @author     BeRocket
 * @package     WooCommerce-Filters/Templates
 * @version  1.0.1
 */
extract($berocket_query_var_title);
global $berocket_unique_value;
$berocket_unique_value++;
$random_name = strval($berocket_unique_value);
$child_parent = berocket_isset($child_parent);
$is_child_parent = $child_parent == 'child';
$is_child_parent_or = ( $child_parent == 'child' || $child_parent == 'parent' );
$child_parent_depth = berocket_isset($child_parent_depth, false, 0);
if ( $child_parent == 'parent' ) {
    $child_parent_depth = 0;
}
if ( $is_child_parent ) {
?>
<li class="berocket_child_parent_sample select<?php if( ! empty($select_multiple) ) echo ' multiple'; ?>"><ul>
    <span>
        <?php $term = br_get_value_from_array($terms, 0);
        $term_taxonomy_echo = berocket_isset($term, 'wpml_taxonomy');
        if( empty($term_taxonomy_echo) ) {
            $term_taxonomy_echo = berocket_isset($term, 'taxonomy');
        } ?>
        <ul<?php if( ! empty($select_multiple) ) echo ' multiple="multiple" data-placeholder="'.$select_first_element_text.'"'?> id='checkbox_<?php echo berocket_isset($term, 'term_id') ?>_<?php echo berocket_isset($random_name) ?>'
                class="<?php echo br_get_value_from_array($uo, array('class', 'selectbox')) ?> <?php echo $term_taxonomy_echo; ?>"
                data-taxonomy='<?php echo $term_taxonomy_echo ?>'>
                <?php if( empty($select_multiple) ) { ?>
                <li data-taxonomy='<?php echo $term_taxonomy_echo ?>' value=''><?php echo $select_first_element_text ?></li>
                <?php } ?>
                <li value='<?php echo berocket_isset($term, 'term_id') ?>' data-term_id='<?php echo berocket_isset($term, 'term_id') ?>' autocomplete="off"
                    <?php echo br_is_term_selected( $term, false, $is_child_parent_or, $child_parent_depth ); ?>
                    ><?php echo apply_filters('berocket_radio_filter_term_name', berocket_isset($term, 'name'), $term) ?></li>
        </ul>
    </span>
</ul></li>
<?php 
unset($terms[0]);
} 
$terms = array_values($terms);
    if( $is_child_parent && is_array(berocket_isset($terms)) && count($terms) == 0 ) {
        if( BeRocket_AAPF_Widget_functions::is_parent_selected($attribute, $child_parent_depth - 1) ) {
            echo '<li>'.$child_parent_no_values.'</li>';
        } else {
            echo '<li>'.$child_parent_previous.'</li>';
        }
    } else {
    if( $child_parent_no_values ) {?>
        <script>
        if ( typeof(child_parent_depth) == 'undefined' || child_parent_depth < <?php echo $child_parent_depth; ?> ) {
            child_parent_depth = <?php echo $child_parent_depth; ?>;
        }
        jQuery(document).ready(function() {
            if( child_parent_depth == <?php echo $child_parent_depth; ?> ) {
                jQuery('.woocommerce-info').text('<?php echo $child_parent_no_values; ?>');
            }
        });
        </script>
    <?php }
    }
if ( is_array(berocket_isset($terms)) && count( $terms ) > 0 ) {
$term_taxonomy_echo = berocket_isset($terms[0], 'wpml_taxonomy');
if( empty($term_taxonomy_echo) ) {
    $term_taxonomy_echo = berocket_isset($terms[0], 'taxonomy');
}
?>
<li class="berocket_disabled_filter_element">
    <span>
        <select disabled<?php if( ! empty($select_multiple) ) echo ' multiple="multiple" data-placeholder="'.$select_first_element_text.'"'?> id='checkbox_<?php echo berocket_isset($terms[0], 'term_id') ?>_<?php echo berocket_isset($random_name) ?>' autocomplete="off"
                class="<?php echo br_get_value_from_array($uo, array('class', 'selectbox')) ?> <?php echo $term_taxonomy_echo ?>"
                data-taxonomy='<?php echo $term_taxonomy_echo ?>'>
            <?php foreach ( $terms as $term ): 
            $term_taxonomy_echo = berocket_isset($term, 'wpml_taxonomy');
            if( empty($term_taxonomy_echo) ) {
                $term_taxonomy_echo = berocket_isset($term, 'taxonomy');
            }
            $parent_count = 0;
            if(isset($term->parent) && $term->parent != 0) {
                $parent_count = get_ancestors( $term->term_id, $term->taxonomy );
                $parent_count = count($parent_count);
            } elseif( isset($term->depth) ) {
                $parent_count = $term->depth;
            }
             ?>
                <option value=''
                    selected
                        <?php if( ! $is_child_parent_or && ! empty($hide_o_value) && berocket_isset($term, 'count') == 0 ) { echo ' hidden disabled'; $hiden_value = true; } ?>
                    ><?php for($i=0;$i<$parent_count;$i++){echo apply_filters('berocket_aapf_select_term_child_prefix', '-&nbsp;');}echo apply_filters('berocket_select_filter_term_name', berocket_isset($term, 'name'), $term) ?></option>
            <?php endforeach; ?>
        </select>
    </span>
</li>
<?php } ?>
