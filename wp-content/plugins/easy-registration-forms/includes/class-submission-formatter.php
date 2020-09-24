<?php

/**
 * Main Submission Formatter
 *
 * Contains a bunch of helper methods as well.
 *
 * @package    ERForms
 * @author     ERForms
 * @since      1.0.0
 */
class ERForms_Submission_Formatter
{
    protected $type;
    protected $submission;
    
    public function __construct($type='html',$submission){
        $this->type= $type;
        $this->submission= $submission;
    }
    
    public function format(){
        if($this->type=='html'){
            $this->html();
        }
        else if($this->type=='report'){
            $this->report();
        }
        
        else if($this->type=='personal_data_export'){
            $this->personal_data_export();
        }
        return $this->submission;
    }
    
    public function set_submission($submission){
        $this->submission= $submission;
    }
    
    public function set_type($type){
        $this->type= $type;
    }
    
    protected function personal_data_export(){
        foreach ($this->submission['fields_data'] as $field_index=>$single) {
            if ($single['f_type'] == 'file' && !empty($single['f_val'])) {
                $url = erforms_get_attachment_url($single['f_val'],$this->submission['id']);
                if(!empty($url)){
                    $single['f_val']='<a target="_blank" href="' . $url . '">'.$url.'</a>';
                }
                else
                {
                    $single['f_val']= __('Unable to fetch file URL. Possible reasons: File might have deleted from WordPress media section.','erforms');
                }
            }
            else
            {  
                if (is_array($single['f_val'])) {
                    $single['f_val']= implode(', ', $single['f_val']);
                } 
                else{                                     // Handling scalar values
                    if($single['f_type'] == 'url'){
                         $single['f_val']= '<a target="_blank" href="'.$single['f_val'].'">'.$single['f_val'].'</a>';
                    }
                    // Backward compatibility
                    if(!empty($single['f_entity']) && !empty($single['f_entity_property'])){
                        $single['f_type'] = $single['f_entity_property'];
                    }
                    if($single['f_type']=='state' || $single['f_type']=='country'){
                        $single['f_val']= apply_filters('erforms_address_'.$single['f_type'].'_formatter_html',$single['f_val'],$single['f_name'],$this->submission['id']);
                    }
                }
            }
            $this->submission['fields_data'][$field_index]=$single;
        }
        
        $this->add_default_fields();
    }
    
    protected function html(){
        
        foreach ($this->submission['fields_data'] as $field_index=>$single) {
            if ($single['f_type'] == 'file' && !empty($single['f_val'])) {
                if (wp_attachment_is_image($single['f_val'])) {
                    $single['f_val']= '<a class="erf-image-attachment" target="_blank" href="'.
                                       esc_url(erforms_get_attachment_url($single['f_val'],$this->submission['id'])).'">'.
                                      '<img src="' . erforms_get_attachment_image($single['f_val'],$this->submission['id']) . '"/></a>';
                } else {
                    $url = wp_get_attachment_url($single['f_val']);
                    if(!empty($url)){
                        $image_url = esc_url(erforms_get_attachment_url($single['f_val'],$this->submission['id']));
                        $single['f_val']='<a target="_blank" href="' . $image_url . '">'.$image_url.'</a>';
                    }
                    else
                    {
                        $single['f_val']= __('Unable to fetch file URL. Possible reasons: File might have deleted from WordPress media section.','erforms');
                    }
                    
                }
            }
            else
            {  
                if (is_array($single['f_val'])) {
                    $single['f_val']= implode(', ', $single['f_val']);
                } 
                else{                                     // Handling scalar values
                    if($single['f_type'] == 'url'){
                         $single['f_val']= '<a target="_blank" href="'.esc_url($single['f_val']).'">'.$single['f_val'].'</a>';
                    }
                    
                    // Backward compatibility
                    if(!empty($single['f_entity']) && !empty($single['f_entity_property'])){
                        $single['f_type']= $single['f_entity_property'];
                    }
                    if($single['f_type']=='state' || $single['f_type']=='country'){
                        $single['f_val']= apply_filters('erforms_address_'.$single['f_type'].'_formatter_html',$single['f_val'],$single['f_name'],$this->submission['id']);
                    }
                }
            }
            $this->submission['fields_data'][$field_index]=$single;
        }
        
        $this->add_default_fields();
    }
    
    protected function report(){
        foreach ($this->submission['fields_data'] as $field_index=>$single) {
            if ($single['f_type'] == 'file' && !empty($single['f_val'])) {
                if (wp_attachment_is_image($single['f_val'])) {
                    $single['f_val']= erforms_get_attachment_url($single['f_val'],$this->submission['id']);
                } else {
                    $url = erforms_get_attachment_url($single['f_val'],$this->submission['id']);
                    if(!empty($url)){
                       $single['f_val']=$url; 
                    }
                    else
                    {
                        $single['f_val']= __('Unable to fetch file URL. Possible reasons: File might have deleted from WordPress media section.','erforms');
                    }
                    
                }
            }
            else
            {  
                if (is_array($single['f_val'])) {
                    $single['f_val']= implode(', ', $single['f_val']);
                } 
                else{  // Handling scalar values
                        if(!empty($single['f_entity']) && !empty($single['f_entity_property'])){
                            $single['f_type']= $single['f_entity_property'];
                        }
                        if($single['f_type']=='state' || $single['f_type']=='country'){
                            $single['f_val']= apply_filters('erforms_address_'.$single['f_type'].'_formatter_csv',$single['f_val'],$single['f_name'],$this->submission['id']);
                        }
                        $single['f_val']= $single['f_val'];
                }
            }
            $this->submission['fields_data'][$field_index]=$single;
        }
        $this->add_default_fields();
    }
    
    protected function add_default_fields(){
        $default_fields= erforms_get_default_submission_fields();
        foreach($default_fields as $df){
            switch($df){
                case 'user_active': if(!isset($this->submission['user_active'])) { break; }
                                    $this->submission['user_active']= $this->submission['user_active']=='0' ? __('Deactive','erforms') : __('Active','erforms');
                                    break;    
                case 'user_role':   if(!isset($this->submission['user_role'])) { break; }
                                    $role_labels= array(); 
                                    foreach($this->submission['user_role'] as $r){
                                        array_push($role_labels,ucwords(translate_user_role($r)));
                                    }   

                                    $this->submission['user_role']= implode(',', $role_labels); break;
                                    
                case 'plans': if(isset($this->submission['plans'])){
                                $plan_names = array();
                                if(!empty($this->submission['plans']) && is_array($this->submission['plans'])){
                                    foreach($this->submission['plans'] as $row){
                                        $plan= erforms()->plan->get_plan($row['id']);
                                        if(empty($plan)){ //Plan deleted.
                                            $plan= $row['plan'];
                                            array_push($plan_names,$plan['name'].'('.__('Plan does not exist','erforms').')');
                                        } else{
                                            array_push($plan_names,$plan['name']);
                                        }
                                    }
                                }
                                $this->submission['plans'] = implode(',',$plan_names);
                              }   
                              break;
            }
        }
    }
}