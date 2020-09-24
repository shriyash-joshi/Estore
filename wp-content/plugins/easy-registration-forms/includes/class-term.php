<?php

abstract class ERForms_Term
{

        /**
	 * Return WP_Term
	 *
	 * @since 1.4.1
	 *
	 * @param mixed $id
	 * @param array $args
	 *
	 * @return array|bool|null|WP_Post
	 */
	public function get($id='', $args = array() ) {
            if (!empty($id )) 
            {
                // If ID is provided, we get a single term
                $term = get_term(absint($id),$this->tax_type);
                return $term;        
            }
            else 
            {     
                // No ID provided, get multiple terms
                $defaults = array(
                        'taxonomy'     => $this->tax_type,
                        'hide_empty'   => false, 
                        'orderby'       => 'id',
                        'order'         => 'Desc'
                );
                $args = wp_parse_args( $args, $defaults );
                $terms = get_terms( $args );
                return $terms;
            }
	}
        
        
	/**
	 * Add new WP_Term.
	 *
	 * @since 1.4.1
	 *
	 * @param string $title
	 * @param array $args
	 * @param array $data
	 *
	 * @return mixed
	 */
	public function add($args=array()) {
            $term= wp_insert_term(
                        $args['name'], // the term 
                        $this->tax_type, // the taxonomy
                        array(
                          'description'=> $args['desc'],
                          'slug' => $args['name']
                        )
                    );
            return $term;
	}
        
        public function update($args){
            return wp_update_term($args['id'],$this->tax_type,array('name'=>$args['name'],'slug'=>$args['name'],'description'=>$args['desc']));
        }
        
        /**
	 * Get WP_Term meta
	 *
	 * @since 1.4.1
	 *
	 * @param string $term_id
	 * @param string $meta
	 *
	 * @return bool
	 */
	public function get_meta($term_id,$meta='') {
            if (empty($term_id)) {
                    return false;
            }
            
            
            if(empty($meta))
                return get_term_meta($term_id);
            
            $meta = "erform_".$meta;
            $meta_value= get_term_meta($term_id,$meta,true);
            return $meta_value;
	}
	
        public function add_meta($term_id,$meta_key,$meta_value,$unique= true){
            add_term_meta($term_id,'erform_'.$meta_key,$meta_value,$unique);
        }
        
        /**
         * 
         * @param int $id
         * @param string $meta_key
         * @param string $meta_value
         */
        public function update_meta($term_id,$meta_key, $meta_value){
            if (empty($term_id)) {
                    return false;
            }
            $meta_key = 'erform_'.$meta_key;
            update_term_meta($term_id, $meta_key, $meta_value);
        }
        
        public function delete($term_id){
            return wp_delete_term($term_id,$this->tax_type); 
        }
        
        public function set_post_terms($sub_id,$terms,$append= true){
            return wp_set_post_terms($sub_id,$terms,$this->tax_type,$append);
        }
        
        public function remove_post_terms($sub_id,$terms){
            return wp_remove_object_terms($sub_id,$terms,$this->tax_type);
        }
        
        public function get_terms_by_post($post_id){
            return wp_get_post_terms($post_id,$this->tax_type);
        }
        
        public function get_by($by,$value){
            return get_term_by($by, $value, $this->tax_type); 
        }
}