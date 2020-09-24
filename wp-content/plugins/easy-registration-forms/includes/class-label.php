<?php

/**
 * Label handler
 *
 * Contains a bunch of helper methods as well.
 *
 * @package    ERForms
 * @author     ERForms
 * @since      1.4.1
 */
class ERForms_Label extends ERForms_Term {
    protected $tax_type = 'erforms_label';
    private static $instance = null;
     /**
     * Primary class constructor.
     *
     * @since 1.0.0
     */
    private function __construct() {
        add_action( 'init', array($this,'register_taxonomy'));
        add_action('wp_ajax_erf_save_label',array($this,'ajax_save_label'));
        add_action('wp_ajax_erf_delete_label',array($this,'ajax_delete_label'));
        add_action('wp_ajax_erf_assign_label',array($this,'ajax_assign_label'));
        add_action('wp_ajax_erf_remove_sub_label',array($this,'ajax_remove_sub_label'));
    }
    
    public static function get_instance()
    {   
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }
    /**
     * Registers the custom taxonomy to be used for labels.
     *
     * @since 1.4.1
     */
    public function register_taxonomy() {
       register_taxonomy(
		$this->tax_type,
		'erforms_submission',
		array(
                    'label' => __('Label','erforms'),
                    'public' => false,
                    'rewrite' => false,
                    'update_count_callback' => '_update_post_term_count',
		)
	);
    }
    
    // Saves label
    public function save($params) { 
        $defaults= array('desc'=>'');
        $params = wp_parse_args($params,$defaults);
        // Name and color validation
        if(empty($params['name']) || empty($params['color'])){
            return;
        }
        if(!empty($params['id'])){ // Edit/Update term
            $term= $this->update($params);
            if(!is_wp_error($term)){
                $meta_keys = $this->meta_keys();
                foreach($meta_keys as $m_key){
                    $this->update_meta($term['term_id'], $m_key, $params[$m_key]);
                }
            }
        }
        else
        {
            $term= $this->add($params);
            if(!is_wp_error($term)){
                $meta_keys = $this->meta_keys();
                foreach($meta_keys as $m_key){
                    $this->add_meta($term['term_id'], $m_key, $params[$m_key]);
                }
            }
        }
        return $term;
    }
    
    public function get_labels($args= array()){
        $defaults= array('order'=>'DESC');
        $args= wp_parse_args($args,$defaults);
        $terms= $this->get(); 
        if(is_wp_error($terms))
            return array();
        
        $labels= array();
        $meta_keys = $this->meta_keys();
        foreach($terms as $term){
            $label= $this->get_label($term);
            if(empty($label))
                continue;
            array_push($labels, $label);
        }
        return $labels;
    }
    
    public function meta_keys(){
        $meta_input = array('color');
        return $meta_input;
    }
    
    public function ajax_save_label(){
        if(!erforms_is_user_admin()){
            wp_send_json_error();
        }
        
        // Sanitize request data
        $name= sanitize_text_field(wp_unslash($_POST['label']));
        $desc= sanitize_text_field(wp_unslash($_POST['desc']));
        $color= sanitize_text_field(wp_unslash($_POST['color']));
        $id= absint($_POST['id']);
        
        if(empty($name) || empty($color) || empty($id)){
            wp_send_json_error(array('msg'=>__('Invalid request.','erforms')));
        }
        
        $term= $this->save(array('name'=>$name,'desc'=>$desc,'color'=>$color,'id'=>$id));
        if(is_wp_error($term)){
            $duplicate_error= $term->get_error_message('duplicate_term_slug');
            if(empty($duplicate_error)){
                    $duplicate_error= $term->get_error_message('term_exists');
            }
                
            if(!empty($duplicate_error))
                $msg= __('Label already in use.','erforms');
            else
                $msg= $term->get_error_message();
            wp_send_json_error(array('msg'=>$msg));
        }
        else
        {
            wp_send_json_success(array('msg'=>__('Label updated.','erforms')));
        }
    }
    
    public function ajax_delete_label(){
        if(!erforms_is_user_admin()){
            wp_send_json_error();
        }
        $id= absint($_POST['id']);
        if(empty($id)){
            wp_send_json_error(array('msg'=>__('Invalid request.','erforms')));
        }
        
        $term= $this->delete($id);
        if(is_wp_error($term)){
            wp_send_json_error(array('msg'=>$term->get_error_message));
        }
        else
        {
            wp_send_json_success(array('msg'=>__('Label deleted','erforms')));
        }
    }
    
    public function get_tags($sanitize= false){
        $labels= $this->get_labels();
        $dd= array();
        foreach($labels as $label){
            if($sanitize){
                $name= sanitize_title($label['name']);
            }
            else{
                $name= $label['name'];
            }
            $dd[$name]='#'.$label['color'];
        }
        return $dd;
    }
    
    public function assign_label_by_name($sub_id,$name){
        $label= $this->set_post_terms($sub_id,$name);
        if(is_array($label)){ //Successful
            do_action('erf_label_assigned',$sub_id,$name,$this->tax_type);
            return true;
        }
        return false;
    }
    
    public function remove_label_by_name($sub_id,$name){
        $label= $this->remove_post_terms($sub_id,$name);
        if(!is_wp_error($label) && !empty($label)){ //Successful removal
            do_action('erf_label_revoked',$sub_id,$name,$this->tax_type);
            return true;
        }
        return false;
    }
    
    public function ajax_assign_label(){
        if(!erforms_is_user_admin()){
            wp_send_json_error();
        }
        $label_name= sanitize_text_field(strtolower(wp_unslash($_POST['name'])));
        $sub_id= absint($_POST['sub_id']);
        if(empty($label_name) || empty($sub_id)){
            wp_send_json_error(array('msg'=>__('Invalid request.','erforms')));
        }
        $this->assign_label_by_name($sub_id,$label_name);;
        wp_send_json_success();
    }
    
    public function ajax_remove_sub_label(){
        if(!erforms_is_user_admin()){
            wp_send_json_error();
        }
        $label_name= sanitize_text_field(strtolower(wp_unslash($_POST['name'])));
        $sub_id= absint($_POST['sub_id']);
        if(empty($label_name) || empty($sub_id)){
            wp_send_json_error(array('msg'=>__('Invalid request.','erforms')));
        }
        $this->remove_label_by_name($sub_id,$label_name);;
        wp_send_json_success();
    }
    
    public function tags_by_submission($sub_id){
        $tags= array();
        $terms=  $this->get_terms_by_post($sub_id);
        if(is_wp_error($terms)){
            return $terms;
        }
        foreach($terms as $term){
            array_push($tags, $term->name);
        }
        return $tags;
    }
    
    function get_label($term){
        if (!($term instanceof WP_Term)) {
            $term= $this->get($term);
        }
        
        if(empty($term) || is_wp_error($term)){
            return false;
        }
        $meta_keys = $this->meta_keys();
        $label= array('name'=>$term->name,'desc'=>$term->description,'id'=>$term->term_id);
        foreach($meta_keys as $m_key){
            $m_value= $this->get_meta($term->term_id,$m_key);
            $label[$m_key]= $m_value;
        }
        $label['count']= $term->count;
        return $label;
    }
    
    public function get_label_by_name($name){
        $term=  $this->get_by('name',$name);
        if(empty($term)){
            return false;
        }
        return $this->get_label($term);
    }
    
    public function get_tax_type(){
        return $this->tax_type;
    }
}
