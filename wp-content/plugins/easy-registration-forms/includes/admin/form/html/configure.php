<?php $menus= erforms_form_configuration_menus($form['type']);
      $type= !empty($_REQUEST['type']) ? sanitize_text_field($_REQUEST['type']) : '';
      if(in_array($type, array_keys($menus))){
        include_once('configuration-type.php');
      }