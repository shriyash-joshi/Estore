<?php

/**
 * @param $uid
 * @throws Exception
 */
function pmui_delete_user($uid) {
    if (class_exists('PMXI_Post_Record')) {
        $post = new PMXI_Post_Record();
        $post->get_by_post_id($uid)->isEmpty() or $post->delete();
    }
}