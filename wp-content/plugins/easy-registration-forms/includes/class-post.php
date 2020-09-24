<?php

abstract class ERForms_Post
{

        /**
	 * Return WP_Post
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $id
	 * @param array $args
	 *
	 * @return array|bool|null|WP_Post
	 */
	public function get($id='', $args = array() ) {
            if (!empty($id )) 
            {
                // If ID is provided, we get a single form
                $post = get_post(absint($id));
                return $post;        
            }
            else 
            {     
                // No ID provided, get multiple records
                $defaults = array(
                        'post_type'     => $this->post_type,
                        'orderby'       => 'id',
                        'order'         => 'ASC',
                        'no_found_rows' => true,
                        'nopaging'      => true,
                        'suppress_filters'=> false
                );
                $args = wp_parse_args( $args, $defaults );
                $posts = get_posts( $args );
                return $posts;
            }
	}
        
        /**
	 * Delete WP_Post.
	 *
	 * @since 1.0.0
	 *
	 * @param array $ids
	 *
	 * @return boolean
	 */
	public function delete($ids=array()) {
            if (!is_array($ids)) {
               $ids = array($ids);
            }

            $ids = array_map( 'absint', $ids );
            foreach ( $ids as $id ) {
               wp_delete_post( $id, true );
            }

            return true;
	}
        
        

	/**
	 * Add new WP_Post.
	 *
	 * @since 1.0.0
	 *
	 * @param string $title
	 * @param array $args
	 * @param array $data
	 *
	 * @return mixed
	 */
	public function add($args=array(),$data = array() ) {
            // Merge args and create the form
            $post    = wp_parse_args($args,array(
                                                'post_status'  => 'publish',
                                                'post_type'    => $this->post_type
                                                )); 
            $post_id = wp_insert_post($post);
            return $post_id;
	}
        
        
        /**
	 * Get WP_Post meta
	 *
	 * @since 1.0.0
	 *
	 * @param string $post_id
	 * @param string $meta
	 *
	 * @return bool
	 */
	public function get_meta($post_id,$meta='') {
            if (empty($post_id)) {
                    return false;
            }
            
            if(empty($meta)){
                return erforms_get_post_meta($post_id);
            }
            
            $meta_value= erforms_get_post_meta($post_id,$meta,true);
            return $meta_value;
	}
	
        /**
         * 
         * @param int $id
         * @param string $meta_key
         * @param string $value
         */
        public function update_meta($post_id,$meta_key, $value){
            if (empty($post_id)) {
                    return false;
            }
            return erforms_update_post_meta($post_id,$meta_key,$value);
        }
        
        public function get_posts_dropdown($args){
            $posts = $this->get();
            $dropdown= '<select>';
            if(isset($args['name'])){
               $id= str_replace('[]', '', $args['name']); 
               $dropdown= '<select name="'.$args['name'].'" id="'.$id.'">'; 
            }
            
            if(isset($args['default'])){
                $dropdown .= '<option value="">'.$args['default'].'</option>';
            }
            if(!empty($posts) && is_array($posts)){
                foreach($posts as $post){
                    if(isset($args['selected']) && $args['selected']==$post->ID){
                        $dropdown .= '<option selected value="'.$post->ID.'">'.$post->post_title.'</option>';
                    }
                    else
                    $dropdown .= '<option value="'.$post->ID.'">'.$post->post_title.'</option>';
                }
            }
            $dropdown .= '</select>';
            return $dropdown;
        }
        
}