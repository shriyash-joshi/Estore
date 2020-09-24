<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

class ERForms_Form_Render{
    private $html_attr_mapping = array('className'=>'class','name'=>'name','type'=>'type','placeholder'=>'placeholder','maxlength'=>'maxlength',
                                        'pattern'=>'pattern','required'=>'required','masking'=>'masking',
                                        'cols'=>'cols','rows'=>'rows','multiple'=>'multiple','id'=>'id','value'=>'value',
                                        'enableIntl'=>'enable-intl','disabled'=>'disabled','min'=>'min','max'=>'max',
                                        'dataDateFormat'=>'data-date-format','data-parsley-confirm-password'=>'data-parsley-confirm-password',
                                        'dataErfBtnPos'=>'data-erf-btn-pos','dataRefId'=>'data-ref-id','dataRefLabel'=>'data-ref-label','expression'=>'data-expression');
    private static $instance = null;
    
    public static function get_instance()
    {   
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function input_html($form,$field){
        $field['id']= $field['name'];
        $html = '<div class="erf-'.esc_attr($field['type']).' form-group field-'.esc_attr($field['name']).esc_attr($field['widthClass']).'">';
        $icon = !empty($field['icon']) ? "<i class='erforms-icon fa ".esc_attr($field['icon'])."'></i>" : '';
        $html .= '<label for="'.esc_attr($field['name']).'" class="erf-'.esc_attr($field['type']).'-label">'.$icon.
                 $field['label'];
        if(!empty($field['required'])){
            $html .= '<span class="erf-required">*</span>';
        }
        if(!empty($field['description'])){
            $html .= '<span class="tooltip-element" tooltip="'.esc_attr($field['description']).'"><i class="fa fa-info" aria-hidden="true"></i></span>';
        }
        $html .= '</label>';
        
        if(!empty($field['required'])){
            $field['required']= 'required';
        } else{
            unset($field['required']);
        }
        
        $str = '';
        foreach($field as $key=>$attr){
            if(isset($this->html_attr_mapping[$key]) && !empty($attr)){
                $str .= $this->html_attr_mapping[$key]."='".esc_attr($attr)."' ";
            }
        }
        $html .= "<input $str />";
        $html .= '</div>';
        return $html;
    }
    
    private function formula_html($form,$field){
        $field['id']= $field['name'];
        $field['type'] = 'text';
        $field['className'] = 'formula-field form-control '; 
        
        $str = '';
        foreach($field as $key=>$attr){
            if(isset($this->html_attr_mapping[$key]) && !empty($attr)){
                $str .= $this->html_attr_mapping[$key]."='".esc_attr($attr)."' ";
            }
        }
        if(!empty($field['hide'])){
            $html = '<div style="display:none" class="erf-'.esc_attr($field['type']).' form-group field-'.esc_attr($field['name']).esc_attr($field['widthClass']).'">';
        } else {
            $html = '<div class="erf-'.esc_attr($field['type']).' form-group field-'.esc_attr($field['name']).esc_attr($field['widthClass']).'">';
        }
        
        $html .= '<label for="'.esc_attr($field['name']).'" class="erf-'.esc_attr($field['type']).'-label">'.$field['label'];
        $html .=  "<input readonly $str />";
        $html .= '</div>';
        return $html;
    }
    
    private function hidden_html($form,$field){
        $field['id']= $field['name'];
        $str = '';
        foreach($field as $key=>$attr){
            if(isset($this->html_attr_mapping[$key]) && !empty($attr)){
                $str .= $this->html_attr_mapping[$key]."='".esc_attr($attr)."' ";
            }
        }
        return "<input $str />";
    }
    
    private function textarea_html($form,$field){
        $field['id']= $field['name'];
        $html = '<div class="erf-'.esc_attr($field['type']).' form-group field-'.esc_attr($field['name']).esc_attr($field['widthClass']).'">';
        $icon = !empty($field['icon']) ? "<i class='erforms-icon fa ".esc_attr($field['icon'])."'></i>" : '';
        $html .= '<label for="'.esc_attr($field['name']).'" class="erf-'.esc_attr($field['type']).'-label">'.$icon.
                 $field['label'];
        if(!empty($field['required'])){
            $html .= '<span class="erf-required">*</span>';
        }
        if(!empty($field['description'])){
            $html .= '<span class="tooltip-element" tooltip="'.esc_attr($field['description']).'"><i class="fa fa-info" aria-hidden="true"></i></span>';
        }
        $html .= '</label>';
        if(!empty($field['required'])){
            $field['required']= 'required';
        } else{
            unset($field['required']);
        }
        $str = '';
        unset($field['value']);
        unset($field['type']);
        foreach($field as $key=>$attr){
            if(isset($this->html_attr_mapping[$key]) && !empty($attr)){
                $str .= $this->html_attr_mapping[$key]."='".esc_attr($attr)."' ";
            }
        }
        $html .= "<textarea $str></textarea>";
        $html .= '</div>';
        return $html;
    }
    
    private function button_html($form,$field,$input_type){
        $field['id']= $field['name'];
        $field['type'] = $input_type;
        $html = '<div class="erf-button form-group field-'.esc_attr($field['name']).esc_attr($field['widthClass']).'">';
        $str = '';
        $field['className'] = str_replace('form-control','',$field['className']);
        foreach($field as $key=>$attr){
            if(isset($this->html_attr_mapping[$key])){
                $str .= $this->html_attr_mapping[$key]."='".esc_attr($attr)."'";
            }
        }
        $html .= "<button $str>".esc_html($field['label'])."</button>";
        $html .= '</div>';
        return $html;
    }
    
    private function select_html($form,$field){
        $field['id']= $field['name'];
        if(!empty($field['multiple'])){
            $field['name']= $field['name'].'[]';
        }
        
        $html = '<div class="erf-select form-group field-'.esc_attr($field['name']).esc_attr($field['widthClass']).'">';
        $icon = !empty($field['icon']) ? "<i class='erforms-icon fa ".esc_attr($field['icon'])."'></i>" : '';
        $html .= '<label for="'.esc_attr($field['name']).'" class="erf-'.esc_attr($field['type']).'-label">'.$icon.
                 $field['label'];
        if(!empty($field['required'])){
            $html .= '<span class="erf-required">*</span>';
        }
        if(!empty($field['description'])){
            $html .= '<span class="tooltip-element" tooltip="'.esc_attr($field['description']).'"><i class="fa fa-info" aria-hidden="true"></i></span>';
        }
        $html .= '</label>';
        if(!empty($field['required'])){
            $field['required']= 'required';
        } else{
            unset($field['required']);
        }
        $str = '';
        unset($field['value']);
        unset($field['type']);
        if(!empty($field['placeholder'])){
            $field['values'] = array_merge(array(array('label'=>$field['placeholder'],'value'=>'')),$field['values']);
            
        }
        foreach($field as $key=>$attr){
            if(isset($this->html_attr_mapping[$key]) && !empty($attr)){
                if(in_array($key,array('multiple','placeholder'))){
                   continue;
                }
                $str .= $this->html_attr_mapping[$key]."='".esc_attr($attr)."' ";
            }
        }
        
        // Options
        $options = '';
        foreach($field['values'] as $option){
            if(!empty($option['selected'])){
                $options .= '<option selected value="'.esc_attr($option['value']).'">'.esc_attr($option['label']).'</option>';
            } else{
                $options .= '<option value="'.esc_attr($option['value']).'">'.esc_attr($option['label']).'</option>';
            }
        }
        $multiple = empty($field['multiple']) ? '' : 'multiple="1"';
        $html .= "<select $str $multiple>$options</select>";
        $html .= '</div>';
        return $html;
    }
    
    private function cb_html($form,$field){
        $field['id']= $field['name'];
        $field['name']= $field['name'].'[]';
        $html = '<div class="erf-'.esc_attr($field['type']).' form-group field-'.esc_attr($field['id']).esc_attr($field['widthClass']).'">';
        $icon = !empty($field['icon']) ? "<i class='erforms-icon fa ".esc_attr($field['icon'])."'></i>" : '';
        $html .= '<label for="'.esc_attr($field['id']).'" class="erf-'.esc_attr($field['type']).'-label">'.$icon.
                 $field['label'];
        if(!empty($field['required'])){
            $html .= '<span class="erf-required">*</span>';
        }
        if(!empty($field['description'])){
            $html .= '<span class="tooltip-element" tooltip="'.esc_attr($field['description']).'"><i class="fa fa-info" aria-hidden="true"></i></span>';
        }
        $html .= '</label>';
        if(!empty($field['required'])){
            $field['required']= 'required';
        } else{
            unset($field['required']);
        }
        $html.= '<div class="checkbox-group">';
        $str = '';
        unset($field['value']);
        unset($field['type']);
        if(!empty($field['other'])){
            $field['className']= $field['className'].' other-option';
        }
        foreach($field as $key=>$attr){
            if(isset($this->html_attr_mapping[$key]) && !empty($attr)){
                if($key=='id') continue;
                $str .= $this->html_attr_mapping[$key]."='".esc_attr($attr)."' ";
            }
        }
        
        // Options
        $options = '';
        foreach($field['values'] as $op_index=>$option){
            $options .= empty($field['inline']) ? '<div class="checkbox">' : '<div class="checkbox-inline">';
            if(!empty($option['selected'])){
                $options .= '<input id="'.esc_attr($field['id'].'-'.$op_index).'" '.$str.' type="checkbox" checked value="'.esc_attr($option['value']).'" />';
            } else{
                $options .= '<input id="'.esc_attr($field['id'].'-'.$op_index).'" '.$str.' type="checkbox" value="'.esc_attr($option['value']).'" />';
            }
            $options .= '<label for="'.esc_attr($field['id'].'-'.$op_index).'">'.esc_attr($option['label']).'</label></div>';
        }
        if(!empty($field['other'])){
            $options .= '<div class="checkbox-inline">'.
                        '<input '.$str.' type="checkbox" value="" />'.
                        '<label for="'.esc_attr($field['id']).'">'.__('Other','erforms').'<input type="text" id="'.esc_attr($field['id']).'-other-value" class="other-val"></label></div>';
        }
        
        $html .= $options;
        $html .= '</div></div>';
        return $html;
    }
    
    private function rg_html($form,$field){
        $field['id']= $field['name'];
        $icon = !empty($field['icon']) ? "<i class='erforms-icon fa ".esc_attr($field['icon'])."'></i>" : '';
        $html = '<div class="erf-'.esc_attr($field['type']).' form-group field-'.esc_attr($field['id']).esc_attr($field['widthClass']).'">';
        $html .= '<label for="'.esc_attr($field['id']).'" class="erf-'.esc_attr($field['type']).'-label">'.$icon.
                 $field['label'];
        if(!empty($field['required'])){
            $html .= '<span class="erf-required">*</span>';
        }
        if(!empty($field['description'])){
            $html .= '<span class="tooltip-element" tooltip="'.esc_attr($field['description']).'"><i class="fa fa-info" aria-hidden="true"></i></span>';
        }
        $html .= '</label>';
        if(!empty($field['required'])){
            $field['required']= 'required';
        } else{
            unset($field['required']);
        }
        $html.= '<div class="radio-group">';
        $str = '';
        unset($field['value']);
        unset($field['type']);
        if(!empty($field['other'])){
            $field['className']= $field['className'].' other-option';
        }
        if(!empty($field['user_roles']) && is_user_logged_in()){
            $field['className']=$field['className'].' erf-disabled';
        }
        foreach($field as $key=>$attr){
            if(isset($this->html_attr_mapping[$key]) && !empty($attr)){
                if($key=='id') continue;
                $str .= $this->html_attr_mapping[$key]."='".esc_attr($attr)."' ";
            }
        }
        
        // Options
        $options = '';
        foreach($field['values'] as $op_index=>$option){
            $options .= empty($field['inline']) ? '<div class="radio">' : '<div class="radio-inline">';
            if(!empty($option['selected'])){
                $options .= '<input id="'.esc_attr($field['id'].'-'.$op_index).'" '.$str.' type="radio" checked value="'.esc_attr($option['value']).'" />';
            } else{
                $options .= '<input id="'.esc_attr($field['id'].'-'.$op_index).'" '.$str.' type="radio" value="'.esc_attr($option['value']).'" />';
            }
            $options .= '<label for="'.esc_attr($field['id'].'-'.$op_index).'">'.esc_attr($option['label']).'</label></div>';
        }
        if(!empty($field['other']) && empty($field['user_roles'])){
            $options .= '<div class="radio-inline">'.
                        '<input '.$str.' type="radio" value="" />'.
                        '<label for="'.esc_attr($field['id']).'">'.__('Other','erforms').
                        '<input type="text" id="'.esc_attr($field['id']).'-other-value" class="other-val"></label></div>';
        }
        
        $html .= $options;
        $html .= '</div></div>';
        return $html;
    }
    
    private function username_html($form,$field){
        $field['id']= $field['name'];
        $field['type']= 'text';
        $icon = !empty($field['icon']) ? "<i class='erforms-icon fa ".esc_attr($field['icon'])."'></i>" : '';
        $html = '<div class="erf-'.esc_attr($field['type']).' form-group field-'.esc_attr($field['name']).esc_attr($field['widthClass']).'">';
        $html .= '<label for="'.esc_attr($field['name']).'" class="erf-'.esc_attr($field['type']).'-label">'.$icon.
                 $field['label'];
        $html .= '<span class="erf-required">*</span>';
        if(!empty($field['description'])){
            $html .= '<span class="tooltip-element" tooltip="'.esc_attr($field['description']).'"><i class="fa fa-info" aria-hidden="true"></i></span>';
        }
        $html .= '</label>';
        
        if(is_user_logged_in()){
            $field['className']=$field['className'].' erf-disabled';
        }
        $str = '';
        foreach($field as $key=>$attr){
            if(isset($this->html_attr_mapping[$key]) && !empty($attr)){
                $str .= $this->html_attr_mapping[$key]."='".esc_attr($attr)."' ";
            }
        }
        $html .= "<input $str />";
        $html .= '</div>';
        return $html;
    }
    
    private function password_html($form,$field){
        $field['id']= $field['name'];
        $field['type']= 'password';
        $icon = !empty($field['icon']) ? "<i class='erforms-icon fa ".esc_attr($field['icon'])."'></i>" : '';
        $html = '<div class="erf-'.esc_attr($field['type']).' form-group field-'.esc_attr($field['name']).esc_attr($field['widthClass']).'">';
        $html .= '<label for="'.esc_attr($field['name']).'" class="erf-'.esc_attr($field['type']).'-label">'.$icon.
                 $field['label'];
        $html .= '<span class="erf-required">*</span>';
        if(!empty($field['description'])){
            $html .= '<span class="tooltip-element" tooltip="'.esc_attr($field['description']).'"><i class="fa fa-info" aria-hidden="true"></i></span>';
        }
        $html .= '</label>';
        
        $str = '';
        $field['value'] = '';
        if(is_user_logged_in()){
            $field['value'] = 'Cheating!!';
            $field['className']=$field['className'].' erf-disabled';
        }
        foreach($field as $key=>$attr){
            if(isset($this->html_attr_mapping[$key]) && !empty($attr)){
                $str .= $this->html_attr_mapping[$key]."='".esc_attr($attr)."' ";
            }
        }
        
        $html .= "<input $str />";
        $html .= '</div>';
        return $html;
    }
    
    private function user_email_html($form,$field){
        $field['id']= $field['name'];
        $field['type']= 'email';
        $icon = !empty($field['icon']) ? "<i class='erforms-icon fa ".esc_attr($field['icon'])."'></i>" : '';
        $html = '<div class="erf-'.esc_attr($field['type']).' form-group field-'.esc_attr($field['name']).esc_attr($field['widthClass']).'">';
        $html .= '<label for="'.esc_attr($field['name']).'" class="erf-'.esc_attr($field['type']).'-label">'.$icon.
                 $field['label'];
        $html .= '<span class="erf-required">*</span>';
        if(!empty($field['description'])){
            $html .= '<span class="tooltip-element" tooltip="'.esc_attr($field['description']).'"><i class="fa fa-info" aria-hidden="true"></i></span>';
        }
        $html .= '</label>';
        
        $str = '';
        if(is_user_logged_in()){
            $field['className']=$field['className'].' erf-disabled';
        }
        foreach($field as $key=>$attr){
            if(isset($this->html_attr_mapping[$key]) && !empty($attr)){
                $str .= $this->html_attr_mapping[$key]."='".esc_attr($attr)."' ";
            }
        }
        
        $html .= "<input $str />";
        $html .= '</div>';
        return $html;
    }
    
    private function header_html($form,$field){
        $html = '<div class="erf-'.esc_attr($field['type']).esc_attr($field['widthClass']).'">';
        $str='';
        unset($field['type']);
        $field['className'] = str_replace('form-control','',$field['className']);
        foreach($field as $key=>$attr){
            if(isset($this->html_attr_mapping[$key])){
                $str .= $this->html_attr_mapping[$key]."='".esc_attr($attr)."' ";
            }
        }
        $input = '';
        switch($field['subtype']){
            case 'h1': $input= 'h1'; break;
            case 'h2': $input= 'h2'; break;
            case 'h3': $input= 'h3'; break;
            case 'h4': $input= 'h4'; break;
            default: $input= 'h4'; break;
        }
        $html .= "<$input  data-non-input='1' $str>".$field['label']."</$input>";
        $html .= '</div>';
        return $html;
    }
    
    private function richtext_html($form,$field){
        $html = '<div class="erf-'.esc_attr($field['type']).esc_attr($field['widthClass']).'">';
        $str='';
        unset($field['type']);
        $field['className'] = str_replace('form-control','erf-rich-text',$field['className']);
        foreach($field as $key=>$attr){
            if(isset($this->html_attr_mapping[$key])){
                $str .= $this->html_attr_mapping[$key]."='".esc_attr($attr)."' ";
            }
        }
        $html .= "<div data-non-input='1' $str>".do_shortcode($field['label'])."</div>";
        $html .= '</div>';
        return $html;
    }
    
    private function splitter_sep__html($form,$field){
        $html = '<div class="erf-'.esc_attr($field['type']).esc_attr($field['widthClass']).'">';
        $str='';
        $field['className'] = str_replace('form-control','',$field['className']);
        $custom_type = $field['type']=='separator' ? 'spacer' : 'page-break';
        unset($field['type']);
        foreach($field as $key=>$attr){
            if(isset($this->html_attr_mapping[$key])){
                $str .= $this->html_attr_mapping[$key]."='".esc_attr($attr)."' ";
            }
        }
        $html .= "<div data-non-input='1' custom-type='$custom_type' $str>".$field['label']."</div>";
        $html .= '</div>';
        return $html;
    }
    
    public function generate_html_from_json($form){
        $html = '';
        $fields= $form['fields'];
        foreach($fields as $index=>$field){
            $field['className'] = !empty($field['className']) ? $field['className'] : 'form-control';
            $field['widthClass'] = isset($field['width']) ? ' erf-element-width-'.$field['width'] : ' erf-element-width-12';
            if($field['type']=='date'){
                $field['className'] = $field['className'].' erf-date-field';
                $field['type']='text';
                $html .= $this->input_html($form,$field);
            } else if($field['type']=='textarea'){
                $html .= $this->textarea_html($form,$field);
            } else if($field['type']=='button'){
                $html .= $this->button_html($form,$field,$field['subtype']);
            } else if($field['type']=='select' || $field['type']=='country' || $field['type']=='state'){
                $html .= $this->select_html($form,$field);
            } else if($field['type']=='checkbox-group'){
                $html .= $this->cb_html($form,$field);
            } else if($field['type']=='radio-group'){
                $html .= $this->rg_html($form,$field);
            } else if($field['type']=='username'){
                $html .= $this->username_html($form,$field);
            } else if($field['type']=='password'){
                $html .= $this->password_html($form,$field);
                if(!empty($field['confirmPassword'])){
                    $field['data-parsley-confirm-password'] = $field['name'];
                    $field['label'] = !empty($field['confirmPasswordLabel']) ? $field['confirmPasswordLabel'] : __('Confirm Password','erforms');
                    if(!empty($field['placeholder'])){
                        $field['placeholder']= !empty($field['confirmPasswordLabel']) ? $field['confirmPasswordLabel'] : __('Confirm Password','erforms');
                    }
                    $field['name'] = 'text-'.wp_generate_password(10,false,false);
                    $html .= $this->password_html($form,$field);
                }
            } else if($field['type']=='user_email'){
                $html .= $this->user_email_html($form,$field);
            }else if($field['type']=='header'){
                $html .= $this->header_html($form,$field);
            }else if($field['type']=='richtext'){
                $html .= $this->richtext_html($form,$field);
            }else if($field['type']=='splitter' || $field['type']=='separator'){
                $html .= $this->splitter_sep__html($form,$field);
            }else if($field['type']=='hidden'){
                $html .= $this->hidden_html($form,$field);
            }else if($field['type']=='formula'){
                $html .= $this->formula_html($form,$field);
            }else{
                $html .= $this->input_html($form,$field);
            }
        }
        return $html;
    }
}
