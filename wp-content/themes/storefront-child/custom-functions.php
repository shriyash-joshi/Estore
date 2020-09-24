<?php

// to get the current user country based on ip 
$locationData = WC_Geolocation::geolocate_ip();
$userCountry = $locationData['country'];
if($userCountry =='' || $userCountry == null ){
	$userCountry ='IN'; // default India
}

// setting the country before front end loads
add_action('wc_price_based_country_before_frontend_init', function() { global $userCountry; setCustomerCountry($userCountry); });
function setCustomerCountry($userCountry){  
    wcpbc_set_woocommerce_country($userCountry);    
}

//adding email subject field under product

function unwp_create_email_fields() {
    $subjectArgs = array(
    'id' => 'email_subject',
    'label' => __( 'Email Subject', 'unwp' ),
    'class' => 'unwp-email-subject-field',
    'desc_tip' => true,
    'description' => __( 'Enter the Email Subject.', 'unwp' ),
    );
    $bodyArgs = array(
        'id' => 'email_body',
        'label' => __( 'Email Body', 'unwp' ),
        'class' => 'unwp-email-body-field',
        'desc_tip' => true,
        'description' => __( 'Enter the Email Body.', 'unwp' ),
    );
    woocommerce_wp_text_input( $subjectArgs );
    woocommerce_wp_textarea_input( $bodyArgs );
}

//function to add fileds under product general fields section
add_action( 'woocommerce_product_options_general_product_data', 'unwp_create_email_fields' );

function unwp_save_email_fields( $post_id ) {
    $product = wc_get_product( $post_id );
    $emailSubject = isset( $_POST['email_subject'] ) ? $_POST['email_subject'] : '';
    $emailBody  = isset( $_POST['email_body'] ) ? $_POST['email_body'] : '';
    $product->update_meta_data( 'email_subject', sanitize_text_field( $emailSubject ) );
    $product->update_meta_data( 'email_body', sanitize_textarea_field( $emailBody ) );
    $product->save();
}

add_action( 'woocommerce_process_product_meta', 'unwp_save_email_fields' );

?>