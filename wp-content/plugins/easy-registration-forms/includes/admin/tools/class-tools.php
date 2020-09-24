<?php
class ERForms_Tools {
	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
            add_action( 'admin_init', array( $this,'init'));  
            add_action('wp_ajax_erf_export', array($this, 'export'));
	}

	/**
	 *
	 * @since 1.0.0
	 */
	public function init() { 
		// Check what page we are on
		$page = isset( $_GET['page'] ) ? sanitize_text_field($_GET['page']) : '';
                
		// Only load if we are actually on the builder
		if ( 'erforms-tools' === $page ) {
                    add_action( 'erforms_admin_page',array( $this, 'output'));
		}
	}

	/**
	 * Load the appropriate files to build the page.
	 *
	 * @since 1.0.0
	 */
	public function output() {
            $forms= erforms()->form->get_forms();
            $submissions_to_import= array();
            $errors= array('import'=>array(),'export'=>array());
            $success='';
            if(!empty($_POST)){
                $action= sanitize_text_field($_POST['action']);
                if($action=='erf_import'){
                    $path_parts = pathinfo($_FILES["file"]["name"]);
                    $extension = $path_parts['extension'];
                    if($extension=='json'){
                        $json_data = file_get_contents($_FILES['file']['tmp_name']);
                        $data= json_decode($json_data,true);
                        if(empty($data)){
                            array_push($errors['import'],__('Invalid import file.','erforms'));
                        }
                        else
                        {
                            // Check if any form available to import
                            if(!empty($data['form'])){
                                $new_form_id= $this->import_form_schema($data['form']);
                                if(empty($new_form_id)){
                                    array_push($errors['import'],__('Unable to Import Form.','erforms'));
                                }
                            }
                        }
                    }
                    else{
                        array_push($errors['import'],__('Invalid import file.','erforms'));
                    }
                    
                    if(empty($errors['import'])){
                       $success= __('Form successfully imported','erforms');
                    }
                }              
            }
            include('html/tools.php');
        }
        
        public function import_form_schema($form){
            $new_form_id= erforms()->form->add_form($form['title'],$form['type']);
            $form['id']= $new_form_id;
            erforms()->form->update_form($form);
            return $new_form_id;
        }
        
        public function export(){ 
            if (!current_user_can('administrator'))
                wp_die('You are not allowed to access this page');

            $form_id= absint($_POST['form']);
            if(empty($form_id)){
               _e('Please select Form','erforms');
               wp_die;
            }
            $this->export_form_schema($form_id);
            wp_die();    
        }
        
        public function export_form_schema($form_id){
            $form= erforms()->form->get_form($form_id);
            $form_title= sanitize_file_name($form['title']);
            $path = get_temp_dir()."{$form_title}_{$form['id']}_".time().'.json';
            $fp = fopen($path, 'w');
            fwrite($fp, json_encode(array('form'=>$form)));
            fclose($fp);
            erforms_download_file($path, 'application/json; charset=utf-8', true);
        }
        
        public function export_all($form_id){
            $data= array('form'=>array(),'submissions'=>array());    
            $data['form']= erforms()->form->get_form($form_id);
            $data['submissions']= erforms()->submission->get_submissions_by_form($form_id);
            $form= erforms()->form->get_form($form_id);
            $path = get_temp_dir()."{$form['title']}_{$form['id']}_".time().'.json';
            $fp = fopen($path, 'w');
            fwrite($fp, json_encode($data));
            fclose($fp);
            erforms_download_file($path, 'application/json; charset=utf-8', true);
        }
        
        
}

new ERForms_Tools;
