<div>
    <label class="br_admin_center"><?php _e('Title', 'BeRocket_AJAX_domain'); ?></label>
    <input class="br_admin_full_size" name="title" value="">
</div>
<div class="berocket_filter_groups">
    <h3><?php _e('Filters In Group', 'BeRocket_AJAX_domain'); ?></h3>
    <?php
    $query = new WP_Query(array('post_type' => 'br_product_filter', 'nopaging' => true));
    if ( $query->have_posts() ) {
        echo '<select class="berocket_filter_list berocket_new_widget_selectbox single">';
        while ( $query->have_posts() ) {
            $query->the_post();
            echo '<option data-name="' . get_the_title() . '" value="' . get_the_id() . '">' . get_the_title() . ' (ID:' . get_the_id() . ')</option>';
        }
        echo '</select>';
        echo ' <a class="button berocket_add_filter_to_group" href="#add_filter">' . __('Add filter', 'BeRocket_AJAX_domain') . '</a>';
        echo '<a class="button berocket_create_new" data-type="single" data-action="berocket_aapf_load_simple_filter_creation" href="#">' . __('Create Filter', 'BeRocket_AJAX_domain') . '</a>';
        wp_reset_postdata();
    }
    ?>
    <ul class="berocket_filter_added_list" data-name="<?php echo $post_name; ?>[filters][]" data-url="<?php echo admin_url('post.php');?>">
    <?php 
    if( isset($filters['filters']) && is_array($filters['filters']) ) {
        foreach($filters['filters'] as $filter) {
            $filter_id = $filter;
            $filter_post = get_post($filter_id);
            if( ! empty($filter_post) ) {
                echo '<li class="berocket_filter_added_' . $filter_id . '"><fa class="fa fa-bars"></fa>
                    <input type="hidden" name="'.$post_name.'[filters][]" value="' . $filter_id . '">
                    ' . $filter_post->post_title . ' <small>ID:' . $filter_id . '</small>
                    <i class="fa fa-times"></i>
                    <a class="berocket_edit_filter" target="_blank" href="' . admin_url('post.php?post='.$filter_id.'&action=edit') . '">' . __('Edit', 'BeRocket_AJAX_domain') . '</a>
                </li>';
            }
        }
    }
    ?>
    </ul>
</div>
<p>
    <?php 
    _e('Need more options? Create it on ', 'BeRocket_AJAX_domain');
    echo '<a href="' . admin_url('edit.php?post_type=br_filters_group') . '">' . __('Manage groups', 'BeRocket_AJAX_domain') . '</a>';
    _e(' page', 'BeRocket_AJAX_domain');
    ?>
</p>
<script>
    jQuery('.berocket_add_filter_to_group').on('click', function(event) {
        event.preventDefault();
        var $parent = jQuery(this).parents('form').first();
        if( ! jQuery('.berocket_filter_added_'+jQuery('.berocket_filter_list', $parent).val(), $parent).length ) {
            var html = '<li class="berocket_filter_added_'+jQuery('.berocket_filter_list', $parent).val()+'"><i class="fa fa-bars"></i> ';
            html += '<input type="hidden" name="'+jQuery('.berocket_filter_added_list', $parent).data('name')+'" value="'+jQuery('.berocket_filter_list', $parent).val()+'">';
            html += jQuery('.berocket_filter_list', $parent).find(':selected').data('name');
            html += ' <small>ID:'+jQuery('.berocket_filter_list', $parent).val()+'</small>';
            html += '<i class="fa fa-times"></i>';
            html += ' <a class="berocket_edit_filter" target="_blank" href="'+jQuery('.berocket_filter_added_list').data('url')+'?post='+jQuery('.berocket_filter_list').val()+'&action=edit"><?php _e('Edit', 'BeRocket_AJAX_domain'); ?></a>';
            html += '</li>';
            jQuery('.berocket_filter_added_list', $parent).append(jQuery(html));
        } else {
            jQuery('.berocket_filter_added_'+jQuery('.berocket_filter_list').val(), $parent).css('background-color', '#ee3333').clearQueue().animate({backgroundColor:'#eeeeee'}, 1000);
        }
    });
    jQuery(document).on('click', '.berocket_filter_added_list .fa-times', function(event) {
        jQuery(this).parents('li').first().remove();
    });
    jQuery(document).ready(function() {
        if(typeof(jQuery( ".berocket_filter_added_list" ).sortable) == 'function') {
            jQuery( ".berocket_filter_added_list" ).sortable({axis:"y", handle:".fa-bars", placeholder: "berocket_sortable_space"});
        }
    });
</script>
<style>
.berocket_filter_added_list li {
    font-size: 1em;
    border: 2px solid rgb(153, 153, 153);
    background-color: rgb(238, 238, 238);
    padding: 5px;
    line-height: 1em;
}
.berocket_filter_added_list li .fa-bars {
    margin-right: 0.5em;
    cursor: move;
}
.berocket_filter_added_list small {
    font-size: 0.8em;
    line-height: 1.25em;
    vertical-align: middle;
}
.berocket_filter_added_list li .fa-times {
    margin-left: 0.5em;
    cursor: pointer;
    float: right;
}
.berocket_filter_added_list li .fa-times:hover {
    color: black;
}
.berocket_filter_added_list .berocket_edit_filter {
    vertical-align: middle;
    font-size: 1em;
    float: right;
    line-height: 1em;
    height: 1em;
    display: inline-block;
}
.berocket_filter_added_list .berocket_sortable_space {
    border: 2px dashed #aaa;
    background: white;
    font-size: 1em;
    height: 1.1em;
    box-sizing: content-box;
    padding: 5px;
}
.berocket_filter_groups {
    margin-top: 20px;
}
.berocket_filter_added_list .berocket_hidden_clickable_options {
    font-size: 12px;
    float: right;
    margin-right: 10px;
    display: none;
}
.berocket_hidden_clickable_options input{
    width: 100px;
}
.berocket_filter_added_list.berocket_hidden_clickable_enabled .berocket_hidden_clickable_options {
    display: inline-block;
}
.berocket_filter_list {
    width: 100%;
}
</style>
