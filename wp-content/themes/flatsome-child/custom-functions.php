<?php

include get_stylesheet_directory().'/OpenSslEncrypt.php';
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

//adding custom product fields

function unwp_create_custom_product_fields() {
    $viewCountArgs = array(
    'id' => 'viewed',
    'label' => __( 'Viewed', 'unwp' ),
    'class' => 'unwp-viewed-field',
    );
    woocommerce_wp_text_input( $viewCountArgs );
}

//function to add fileds under product general fields section
add_action( 'woocommerce_product_options_general_product_data', 'unwp_create_custom_product_fields' );

function unwp_save_custom_product_fields( $post_id ) {
    $product = wc_get_product( $post_id );
    $viewed = isset( $_POST['viewed'] ) ? $_POST['viewed'] : '';
    $product->update_meta_data( 'viewed', sanitize_text_field( $viewed ) );
    $product->save();
}

add_action( 'woocommerce_process_product_meta', 'unwp_save_custom_product_fields' );

// user logout without confirmation
add_action( 'template_redirect', 'logout_confirmation' );
function logout_confirmation() {
    global $wp;
    if ( isset( $wp->query_vars['customer-logout'] ) ) {
        wp_redirect( str_replace( '&amp;', '&', wp_logout_url( wc_get_page_permalink( 'myaccount' ) ) ) );
        exit;
    }
}

// product custom tabs implementations
add_filter( 'woocommerce_product_tabs', 'woo_rename_tabs', 98 );
function woo_rename_tabs( $tabs ) {
	$tabs['description']['title'] = __( 'Overview' );		// Rename the description tab
	$tabs['additional_information']['title'] = __( 'Product Data' );	// Rename the additional information tab
	return $tabs;
}

add_filter( 'woocommerce_product_tabs', 'woo_new_product_tab' );
function woo_new_product_tab( $tabs ) {
	
	// Adds the new tab
	if( get_field('benefits') ){
        $tabs['benefits_tab'] = array(
            'title' 	=> __( 'Benefits', 'woocommerce' ),
            'priority' 	=> 12,
            'callback' 	=> 'benefits_tab_content'
        );
    }
    if(  get_field('frequently_asked_questions') ){

        $tabs['faq_tab'] = array(
            'title' 	=> __( 'FAQs', 'woocommerce' ),
            'priority' 	=> 13,
            'callback' 	=> 'faq_tab_content'
        );
    }
    $instructor = get_field('instructor_profile');
    if(  get_field('show_instructor_profile')  ){
        $tabs['instructor_tab'] = array(
            'title' 	=> __( 'Instructor Profile', 'woocommerce' ),
            'priority' 	=> 14,
            'callback' 	=> 'instructor_tab_content'

        );
    }

    if( get_field('program_type') == 'premium') {

        $tabs['table_of_contents_tab'] = array(
            'title' 	=> __( 'Table of Contents', 'woocommerce' ),
            'priority' 	=> 11,
            'callback' 	=> 'table_of_contents_tab_content'
        );
    }

    if( get_field('program_type') == 'module') {

      $tabs['table_of_contents_tab'] = array(
          'title' 	=> __( 'Modules', 'woocommerce' ),
          'priority' 	=> 11,
          'callback' 	=> 'modules_tab_content'
      );
  }
	return $tabs;

}

add_filter( 'woocommerce_product_tabs', 'woo_batches_tab' );

// for batches tab
function woo_batches_tab( $tabs ) {
    
    if( get_field('program_type') !=='batch') return $tabs;
	// add batches tab
	
	$tabs['batches_tab'] = array(
		'title' 	=> __( 'Batches', 'woocommerce' ),
		'priority' 	=> 11,
		'callback' 	=> 'batches_tab_content'
    );
	return $tabs;
}

function modules_tab_content(){

    echo "<h2 class='fontsize30'>Modules</h2>";
    $rows = get_field('modules');
    if( $rows ) {
      echo '<div class="modules">';
      foreach( $rows as $row ) { ?>
          <div class='module-section'>
          <h3 class='module-title'><?php echo $row['name'];?></h3>
           <table>
             <tbody>
                <tr>
                    <td> <?php echo $row['module_content'];?> </td>
                    <td>
                      <?php if( $row["trail_link"] ):?>    
                          <a  target="_blank" href='<?php echo $row["trail_link"]; ?>' class="button primary is-large" style="border-radius:6px;">FREE TRIAL</a>
                      <?php endif;?>
                     </td> 
                </tr>
            </tbody>
           </table> 
        </div>
        <?php 
      }    
    }
}

function table_of_contents_tab_content(){
  echo "<h2 class='fontsize30'>Table of Contents</h2>";
  if( have_rows('table_of_contents') ) {
    echo "<div class='table-of-contents'>";
    while( have_rows('table_of_contents') ): the_row();   
    echo '<div class="accordion-item"><a href="#" class="accordion-title plain"><button class="toggle"><i class="icon-angle-down"></i></button><span>'.get_sub_field('name').'</span></a>
              <div class="accordion-inner">';
           while( have_rows('content') ): the_row();  
                echo '<div class="toc-module"><span class="toc-module-title">'.get_sub_field('module_title').'</span>';                      
                if(get_sub_field('preview_link') &&  get_sub_field('preview_link') =='video'){
                    echo '<a class="pull-right toc-preview open-video" href="'.get_sub_field('video_url').'" title="Preview" ><i class="dashicons dashicons-welcome-view-site"></i></a>';
                }
                elseif(get_sub_field('preview_link') &&  get_sub_field('preview_link') =='link'){
                  echo '<a href="'.get_sub_field('link').'" target="_blank" title="View" class="pull-right toc-preview"><i class="dashicons dashicons-admin-links"></i> </a>';
                }

                elseif(get_sub_field('preview_link') &&  get_sub_field('preview_link') =='attachment'){
                  echo '<a href="'.get_sub_field('attachment').'" target="_blank" title="Download" class="pull-right toc-preview"><i class="dashicons dashicons-download"></i> </a>';
                }
                else {}
                          
                echo '</div>';
          endwhile;         
            echo '</div></div>';            
    endwhile;
    echo '</div>'; 
  }
}

function batches_tab_content(){
    echo "<h2 class='fontsize30'>Batches</h2>";
    echo do_shortcode('[batches_list]');
}

function benefits_tab_content() {
    echo "<h2 class='fontsize30'>Benefits</h2>";
    "<div class='content-block-list'>" .the_field('benefits'). "</div>";
}

function faq_tab_content() {
    echo "<h2 class='fontsize30'>Frequently Asked Questions</h2>";
    the_field('frequently_asked_questions');	
}

function instructor_tab_content() {
    $instructor = get_field('instructor_profile');

    if( $instructor ){
        echo '<h2 class="fontsize30">About the Instructor</h2>';
        echo '<section class="product-section-bg"><div class="row row-large align-center about-instructor" id="row-1931296033">
        <div class="col medium-8 small-12 large-3">
        <div class="col-inner" style="margin:0px 1px 0px 0px;">
            <div class="img has-hover x md-x lg-x y md-y lg-y" id="image_1805076487">
                <div class="img-inner dark">
                <img src="'.esc_url( $instructor['profile_image'] ).'" alt="'.esc_attr( $instructor['name'] ).'" />
                </div>
                <style scope="scope">
                    #image_1805076487 {
                    width: 100%;
                    }
                </style>
            </div>
        </div>
        </div>
        <div class="col medium-8 small-12 large-9 paddingLeft0">
        <div class="col-inner paddingLeft0">
            <h2 style="width: 200px;height: 15px;color: #000000;font-family: "poppins";font-size:="" 18px;font-weight:bold;line-height:42px;">'.esc_attr( $instructor['name'] ).'</h2>
            <h6 style="height: 17px;color: #000000;font-family: Poppins;font-size: 16px;font-weight: bold;line-height: 42px;text-transform:none;">'.esc_attr( $instructor['qualification'] ).'</h6>
            <h4 style="height: 18px;color: #000000;font-family: Poppins;font-size: 16px;font-weight: bold;line-height: 42px;">'.esc_attr( $instructor['university'] ).'</h4>
            <div id="gap-356940601" class="gap-element clearfix" style="display:block; height:auto;">
                <style scope="scope">
                    #gap-356940601 {
                    padding-top: 30px;
                    }
                </style>
            </div>
            <p class="instructor-about">'.esc_attr( $instructor['about_instructor'] ).'</p>
        </div>
        </div>
        </section>
            <style scope="scope">
            #row-1931296033 > .col > .col-inner {
            padding: 0px 0px 0px 0px;
            }
            @media (min-width:850px) {
            #row-1931296033 > .col > .col-inner {
            padding: 20px 20px 0px 20px;
            }
            }
            </style>
         </div>';
    }
}

add_filter( 'woocommerce_product_tabs', 'woo_remove_product_tabs', 98 );

function woo_remove_product_tabs( $tabs ) {
    unset( $tabs['additional_information'] );  	// Remove the additional information tab
    return $tabs;
}
// restrict counsellor registration if login
add_action('wp', 'login_check');
function login_check()
{
    if ( is_user_logged_in() && is_page( 'counsellor-registration' ) ) {
        wp_redirect(get_site_url().'/my-account');
        exit;
    }
}

function uni_counsellor_define() {
    $custom_meta_fields = array();
    $custom_meta_fields['website'] ='Does Counselling Setup have a Website?';
    $custom_meta_fields['form_of_practice'] = 'Form Of Practice';
    $custom_meta_fields['setup'] = 'Counselling Practice Has';
    $custom_meta_fields['operate'] = 'Where do you work/operate from?';
    $custom_meta_fields['link'] = 'Social Link';
    $custom_meta_fields['experience'] = 'Counsellor Experience';
    $custom_meta_fields['services_provided'] = 'Counsellor Services';
    return $custom_meta_fields;
  }

// show custom fields in user edit
function uni_show_extra_profile_fields($user) {
    print('<h3>Counsellor profile information</h3>');
  
    print('<table class="form-table">');
  
    $meta_number = 0;
    $custom_meta_fields = uni_counsellor_define();
    foreach ($custom_meta_fields as $meta_field_name => $meta_disp_name) {
      $meta_number++;
      print('<tr>');
      print('<th><label for="' . $meta_field_name . '">' . $meta_disp_name . '</label></th>');
      print('<td>');
      print('<input disabled type="text" name="' . $meta_field_name . '" id="' . $meta_field_name . '" value="' . esc_attr( get_the_author_meta($meta_field_name, $user->ID ) ) . '" class="regular-text" /><br />');
      print('<span class="description"></span>');
      print('</td>');
      print('</tr>');
    }
    print('</table>');
  }

add_action('edit_user_profile', 'uni_show_extra_profile_fields'); 


function post_title_shortcode(){
    return get_the_title();
}
add_shortcode('post_title','post_title_shortcode');

function post_excerpt_shortcode(){
    return get_the_excerpt();
}
add_shortcode('post_excerpt','post_excerpt_shortcode');

function product_render_rating(){
   if(is_single()):
    global $product;    
    if ( get_option( 'woocommerce_enable_review_rating' ) === 'no' ) {
		return '';
    } 
    echo '<div class="product-ratings" style="display:inline-block;">';
    $review_count = $product->get_review_count();
    $average      = round($product->get_average_rating(),1);
    $width = ($average/5)*100;

        echo '<a href="#reviews" class="woocommerce-review-link"><div class="white-color woocommerce-product-rating" itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">';
        echo '<div class="success-color-before star-rating"><span style="width: '.$width.'%"></span></div>'; 
        echo '<div title="'.printf( __( '%s star, %s Reviews', 'woocommerce' ), $average,$review_count ).'">';
        echo '</span></div></a></div>';
    echo "</div>";
  endif;    
}
add_shortcode('product_ratings','product_render_rating');

//get the thumnail shortcode
function get_product_thumbnail(){
  global $post;
 // $image_size = apply_filters( 'single_product_archive_thumbnail_size', 'shop_catalog' );
  
  return get_the_post_thumbnail_url( $post->ID );
}
add_shortcode('product_thumbnail','get_product_thumbnail');

// get the dilemmas addressed

function get_dilemmas(){
    echo "<div class='product-dilemmas product-section-bg'>";
    echo "<h3 class='dielemmas-heading'>Dilemmas Addressed</h3>";
    the_field('dilemmas_addressed');
    echo "</div>";
}
add_shortcode('product_dilemmas','get_dilemmas');

//get the add to cart normal program

function get_add_to_cart(){
    global $product;
    $currentUser = wp_get_current_user();
    if ( in_array( 'counsellor', (array) $currentUser->roles ) ) {
        $isCounsellor = "counsellor-quantity";
    }
    else {
        $isCounsellor = "";
    }
    $discount = '';
    $regular_price = $product->get_regular_price();
    $sale_price = $product->get_sale_price();
    $currency = get_woocommerce_currency();
    if( $product->is_on_sale() ) {
       $discount =  $product->get_regular_price()-$product->get_sale_price();
       $discount_rate = round(($discount/$regular_price)*100,2);
    }
    echo "<div class='product-section-bg padding20 uni-cart-price ".$isCounsellor."'>";
    if($discount_rate):
      echo "<div><p class='discount'>".$discount_rate."% OFF</p></div>";
    endif;
    echo  $product->get_price_html();
    echo '<div class="add-to-cart-container normal-program '.$isCounsellor.'" >';
    echo "<div class='add-to-cart'>";
    woocommerce_template_single_add_to_cart();
    echo "</div>";
    echo "<div class='buy-enrol'>";   
    $buy_enrol_text ='Buy and Enroll';
    if(get_field('buy_and_enrol_text') ==''){
      $buy_enrol_text = 'Buy and Enroll';
    }
    else {
      $buy_enrol_text = get_field('buy_and_enrol_text');
    }
    echo "<a  title='buy-and-enroll' href=".get_site_url()."/checkout/?add-to-cart=".$product->get_id()." class='single_add_to_cart_button button primary'>".$buy_enrol_text."</a>"; 
    echo '</div></div>';    
    echo do_shortcode('[coupon_field]');    
    echo '</div>';
}
add_shortcode('add_to_cart_normal','get_add_to_cart');

// append to product description

add_filter( 'the_content', 'append_to_content', 10, 1 );
function append_to_content( $content ){
    // Only for single product pages
    if( ! is_product() ) return $content;
     
        return "<h2 class='fontsize30'>Overview</h2>" . $content;

    return $content;
}

add_shortcode('batches_list','batches_add_to_cart');

function batches_add_to_cart() {
    global $product, $post;
    $variations = $product->get_available_variations();
    foreach ($variations as $key => $value) {
    ?> 
    <div class='batch-section'>
         <h3 class='batch-title'><?php echo $value['batch_title'];?></h3>
    <table>
        <tbody>
            <tr> 
                <td> <b> Days </b> <br/> <?php echo $value['batch_days'];?> </td>   
                <td> <b> Timings </b> <br/> <?php echo $value['batch_timings'];?> </td> 
                <!-- <td> 
                    <span class='button is-small lowercase <?php echo $value["is_in_stock"] ? "batch-available":"batch-not-available";?>'>
                        <?php echo $value["is_in_stock"] ? "Available":"Not Available";?>
                    </span>
                </td>  -->
            </tr>
            <tr>
                <td><?php echo $value['price_html'];?> </td> 
                <td></td>
                <td>
                <?php if( $value["is_in_stock"] ):?>    
                    <a  href='<?php echo site_url().'/checkout/?add-to-cart='.$value['variation_id']?>' class="button new-primary is-large lowercase" style="border-radius:6px;float: right;box-shadow:none !important;">Enroll</a>
                <?php endif;?>
                </td> 
            </tr>
        </tbody>
    </table>
    </div>
    <?php }?>  
    <?php 
   }
// batch product custom fields

// Add Variation Settings
add_action( 'woocommerce_product_after_variable_attributes', 'batch_settings_fields', 10, 3 );

// Save Variation Settings
add_action( 'woocommerce_save_product_variation', 'save_batch_settings_fields', 10, 2 );

/**
 * Create new fields for batch variations
 *
*/
function batch_settings_fields( $loop, $variation_data, $variation ) {

     // Batch Title
	woocommerce_wp_text_input( 
		array( 
			'id'          => '_batch_title[' . $variation->ID . ']', 
			'label'       => __( 'Batch Title', 'woocommerce' ), 
			'placeholder' => 'Enter Batch Title i.e July Batch',
			'desc_tip'    => 'false',
			'value'       => get_post_meta( $variation->ID, '_batch_title', true )
		)
    );

	// Batch Days
	woocommerce_wp_text_input( 
		array( 
			'id'          => '_batch_days[' . $variation->ID . ']', 
			'label'       => __( 'Batch Days', 'woocommerce' ), 
			'placeholder' => 'Enter Batch Days i.e July 01 - July 30',
			'desc_tip'    => 'false',
			'value'       => get_post_meta( $variation->ID, '_batch_days', true )
		)
    );
    
   // Batch Timings
	woocommerce_wp_text_input( 
		array( 
			'id'          => '_batch_timings[' . $variation->ID . ']', 
			'label'       => __( 'Batch Timings', 'woocommerce' ), 
			'placeholder' => 'Enter Batch Timings i.e 8:30 AM-10:30 AM',
			'desc_tip'    => 'false',
			'value'       => get_post_meta( $variation->ID, '_batch_timings', true )
		)
    );    	
}

/**
 * Save fields for variations
 *
*/
function save_batch_settings_fields( $post_id ) {

    // _batch_title
	$batch_title = $_POST['_batch_title'][ $post_id ];
	if( ! empty( $batch_title ) ) {
		update_post_meta( $post_id, '_batch_title', esc_attr( $batch_title ) );
    }
	// _batch_days
	$batch_days = $_POST['_batch_days'][ $post_id ];
	if( ! empty( $batch_days ) ) {
		update_post_meta( $post_id, '_batch_days', esc_attr( $batch_days ) );
    } 
    
   // batch_timings
	$batch_timings = $_POST['_batch_timings'][ $post_id ];
	if( ! empty( $batch_timings ) ) {
		update_post_meta( $post_id, '_batch_timings', esc_attr( $batch_timings ) );
	}
}

// Add New Variation Settings
add_filter( 'woocommerce_available_variation', 'load_variation_settings_fields' );

/**
 * Add custom fields for batches to load on front end
 *
*/
function load_variation_settings_fields( $variations ) {	
	$variations['batch_days'] = get_post_meta( $variations[ 'variation_id' ], '_batch_days', true );
  $variations['batch_title'] = get_post_meta( $variations[ 'variation_id' ], '_batch_title', true );
  $variations['batch_timings'] = get_post_meta( $variations[ 'variation_id' ], '_batch_timings', true );
	return $variations;
}

// to improve speeds on product edits
add_filter('acf/settings/remove_wp_meta_box', '__return_true');

// coupon apply short code
add_shortcode( 'coupon_field', 'display_coupon_field' );
function display_coupon_field() {
    if( isset($_GET['coupon']) && isset($_GET['redeem-coupon']) ){
        if( $coupon = esc_attr($_GET['coupon']) ) {
            if ( WC()->cart->has_discount( $coupon ) ) {
                $success = sprintf( __('Coupon "%s" is available in cart add product to see discount now.'), $coupon );
                $message = $success;    
            }
            else {
                $applied = WC()->cart->apply_coupon($coupon);
                $message = !$applied ? 'Sorry! Coupon code is Invalid or does not exist!':'Coupon '.$coupon.' applied successfully';
            }           
        }     

    }
    $output  = '<span>Got a coupon? <a id="apply-promo-click">Apply Now</a></span><div class="redeem-coupon"><form id="coupon-redeem">
    <p id="coupon-apply" style="display:none;">
        <input required type="text" name="coupon" id="coupon" value=""/>
        <input type="submit" name="redeem-coupon" value="'.__('Apply').'" />
    </p>';
    $output .= isset($coupon) ? '<p class="result">'.$message.'</p>' : '';
    return $output . '</form></div>';
}

add_filter('woocommerce_format_sale_price', 'uni_format_sale_price', 100, 3);
function uni_format_sale_price( $price, $regular_price, $sale_price ) {
     return ' <ins class="sale-price">' . ( is_numeric( $sale_price ) ? wc_price( $sale_price ) : $sale_price ) . '</ins> <del class="regular-price">' . ( is_numeric( $regular_price ) ? wc_price( $regular_price ) : $regular_price ) . '</del>';
}


// wp_register_script('custom-js',get_site_url().'/js/custom.js',array(),NULL,true);
// wp_enqueue_script('custom-js');

// $wnm_custom = array( 'site_url' => get_site_url() );
// wp_localize_script( 'custom-js', 'univariety', $wnm_custom );

add_action('woocommerce_checkout_create_order', 'before_checkout_create_order', 20, 2);
function before_checkout_create_order( $order, $data ) {
    $order->update_meta_data( 'link_generated', 0 );
}

add_action('woocommerce_thankyou', 'create_assessments', 10, 1);
function create_assessments( $order_id ) {
    if ( ! $order_id )
        return;
    // Allow code execution only once
    if( ! get_post_meta( $order_id, '_thankyou_action_done', true ) ) {    
        // Get an instance of the WC_Order object
        $order = wc_get_order( $order_id );
        $sq_order_number = $order->get_order_number();
        //generate assessment links
        if(function_exists('generateAssessments'))
        {
                 generateAssessments($order_id);          
        }
        //remove item from wishlist
        if(class_exists('YITH_WCWL')) {
            if (!function_exists('yith_wcwl_after_ask_an_estimate_remove_wishlist')) {
                function yith_wcwl_after_ask_an_estimate_remove_wishlist($url, $wishlist_id, $additional_notes, $reply_email, $session)
                {
                    $args['wishlist_token'] = $wishlist_id;
                    $wishlist_products = YITH_WCWL()->get_products($args);
                    foreach ($wishlist_products as $wishlist_product) {
                        YITH_WCWL()->details['remove_from_wishlist'] = $wishlist_product['prod_id'];
                        YITH_WCWL()->details['wishlist_id'] = $wishlist_id;
                        YITH_WCWL()->remove();
                    }
                    return $url;
                }
            }
        }
        // Flag the action as done (to avoid repetitions on reload)
        $order->update_meta_data( '_thankyou_action_done', true );
        global $wpdb;
        $order_assessments = $wpdb->get_results("select * from ".$wpdb->prefix."order_assessment where order_id=".$sq_order_number);
        if( !empty($order_assessments)){
          $order->update_status('wc-completed', 'Assessment links generated'); 
        }        
        $order->save();
        $user = wp_get_current_user();
		if ( !in_array( 'counsellor', (array) $user->roles ) && !in_array( 'super_counsellor', (array) $user->roles ) ) {
        //sending order assessment links
        send_order_assessment_emails( $order_id , $order );
    }
        
  }
}

//gifting functions
add_action( 'woocommerce_before_order_notes', 'gift_checkout_fields' );

function gift_checkout_fields( $checkout ) {

    if( isset($_GET['is_gift']) && $_GET['is_gift'] == 'true' ){
      echo '<div id="gift_checkout_fields"><h2>' . __('Gift Details') . '</h2>';

      woocommerce_form_field( 'gift_to_name', array(
          'type'          => 'text',
          'class'         => array('required gift-to-name-checkout form-row-wide'),
          'label'         => __('Name'),
          'required'  => true,
          'placeholder'   => __('Enter your friend\'s name to whom you are gifting to'),
          ), $checkout->get_value( 'gift_to_name' ));
  
      woocommerce_form_field( 'gift_to_email', array(
        'type'          => 'email',
        'class'         => array('required gift-to-email-checkout form-row-wide'),
        'label'         => __('Email'),
        'required'  => true,
        'placeholder'   => __('Enter your friend\'s email to whom you are gifting to'),
        ), $checkout->get_value( 'gift_to_email' ));  
      
      woocommerce_form_field( 'gift_message', array(
        'type'          => 'textarea',
        'class'         => array('required gift-to-message-checkout form-row-wide'),
        'label'         => __('Gift Message'),
        'required'  => true,
        'placeholder'   => __('Enter gift message'),
        ), $checkout->get_value( 'gift_message' ));  

        echo '<div id="is_gift_hidden_field">
            <input type="hidden" class="input-hidden" name="is_gift" id="is_gift" value="' .$_GET['is_gift']. '">
        </div>';    
        
      echo '</div>';
    }

}
               
//validation
add_action('woocommerce_checkout_process', 'my_custom_checkout_field_process');

function my_custom_checkout_field_process() {

  if( isset( $_POST['is_gift'] ) && $_POST['is_gift'] == 'true'  ){
        // Check if set, if its not set add an error.
        if ( ! $_POST['gift_to_name'] )
        wc_add_notice( __( 'Please your friend\'s name to whom you are gifting to ' ), 'error' );
        
        if ( ! $_POST['gift_to_email'] )
        wc_add_notice( __( 'Please your friend\'s email to whom you are gifting to ' ), 'error' );
 

        if ( ! $_POST['gift_message'] )
        wc_add_notice( __( 'Please enter gift message ' ), 'error' );
       
       if( $_POST['gift_to_email']!=='' && !is_email( $_POST['gift_to_email'], $deprecated = false ))
       wc_add_notice( __( 'Please enter valid friend\'s email to whom you are gifting to ' ), 'error' );

  }
 
}

add_action( 'woocommerce_checkout_update_order_meta', 'gift_fields_order_data' );

function gift_fields_order_data( $order_id ) {
    if ( ! empty( $_POST['gift_to_name'] ) ) {
        update_post_meta( $order_id, '_gifted_name', sanitize_text_field( $_POST['gift_to_name'] ) );
    }
    if ( ! empty( $_POST['is_gift'] ) ) {
      update_post_meta( $order_id, '_is_gift', true );
    }
    if ( ! empty( $_POST['gift_to_email'] ) ) {
      update_post_meta( $order_id, '_gifted_email', sanitize_text_field( $_POST['gift_to_email'] ) );
    }

    if ( ! empty( $_POST['gift_message'] ) ) {
      update_post_meta( $order_id, '_gift_message', sanitize_text_field( $_POST['gift_message'] ) );
    }
}

//add inventory link for counsellor
add_filter ( 'woocommerce_account_menu_items', 'inventory_link' );
function inventory_link( $menu_links ){
  $user = wp_get_current_user();
  if ( !in_array( 'counsellor', (array) $user->roles ) && !in_array( 'super_counsellor', (array) $user->roles)) {
    return $menu_links; 
  } 
	$new = array( 'inventory' => 'Inventory' );
    $menu_links = array_slice( $menu_links, 0, 5, true ) 
	+ $new 
	+ array_slice( $menu_links, 1, NULL, true );  
	return $menu_links;
}
 
add_filter( 'woocommerce_get_endpoint_url', 'inventory_hook_endpoint', 10, 4 );
function inventory_hook_endpoint( $url, $endpoint, $value, $permalink ){ 
	if( $endpoint === 'inventory' ) {      
    $hash = OpenSslEncrypt::getInstance()->encrypt(get_current_user_id()); 
    $url = site_url().'/assessments/counsellor/?ref='.$hash; 
	}
	return $url; 
}

// add home menu item for other pages
function add_home_menu_item($items) {
  if(is_front_page()){
      return $items;
  }
  else {
    echo "<style>
          .nav>li.menu-item.current_page_item.active {
             visibility: visible;
          } 
        </style>";
 
    $homelink = '<li class="home home-menu-item menu-item"><a href="' . home_url( '/' ) . '">' . __('Home') . '</a></li>';
    // add the home link to the end of the menu
    $items = $homelink . $items ;
    return $items;
  }
}
add_filter( 'wp_nav_menu_items', 'add_home_menu_item' );

//remove related products from single product summary
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );

//Loads Dashicons for all users
function load_dashicons(){
  wp_enqueue_style('dashicons');
}
add_action('wp_enqueue_scripts', 'load_dashicons');

// Removes Order Notes Title - Additional Information & Notes Field
add_filter( 'woocommerce_enable_order_notes_field', '__return_false', 9999 );

// Remove Order Notes Field
add_filter( 'woocommerce_checkout_fields' , 'remove_order_notes' );
function remove_order_notes( $fields ) {
     unset($fields['order']['order_comments']);
     return $fields;
}

//wp-admin product edit css
add_action('admin_head', 'product_edit_css');

function product_edit_css() {
  echo "<style>
     #post-body #normal-sortables {
      display: flex;
      flex-direction: column;
    }
    #wpseo_meta {
      order:10;
    }
    .post-type-product .acf-field[data-type='true_false'] .acf-label {
      display: inline-block;
  }
  
  .post-type-product .acf-field[data-type='true_false'] .acf-input {
      display: inline-block;
  }
  </style>";
}

//product edit js to change the product type on program selection
function product_edit_js() {
  $url = get_stylesheet_directory_uri() . '/assets/js/product-edit.js';
  echo '"<script type="text/javascript" src="'. $url . '"></script>"';
}
add_action('admin_footer', 'product_edit_js');

//trim zeros from product price
add_filter( 'woocommerce_price_trim_zeros', '__return_true' );

add_filter( 'woocommerce_show_variation_price', '__return_true');

function send_order_assessment_emails( $order_id , $order ) {
  if ( isset( $order ) && !empty($order) ) {
     $items = $order->get_items();
     foreach ( $items as $item ) {
       $seq_order = $order->get_order_number();
       $product_id = $item->get_product_id();
       $email_subject = get_post_meta( $product_id, 'email_subject')[0];
       $email_body = get_post_meta( $product_id, 'email_body')[0];
       if( $email_subject !=='' && $email_body !==''):        
           $email_subject = $email_subject .' - Order ' .$seq_order;
           do_action( 'order_assessment_email_notification', $order, $product_id,$email_subject,$email_body );
         endif;
   }
   }
 }

 //remove query param s when no keyword typed and redirect to shop page
//  if($_GET['s'] =='' && $_GET['s'] !==null){
//   wp_safe_redirect(home_url()."/shop");
//  }

 //unvariety login
add_action( 'wp_ajax_nopriv_get_uni_ogin', 'uni_login_handler' );
add_action( 'wp_ajax_get_uni_ogin', 'uni_login_handler' );
function uni_login_handler() {
        $email= trim($_POST['uni_email']);
        $pass= md5($_POST['uni_pass']);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,"https://www.univariety.com/app/unishop/checkLogin");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,array('email'=>$email,'pass_word'=>$pass));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $server_output = curl_exec($ch);
        curl_close ($ch);
        $result=json_decode($server_output,true); 
        if( $result['status']==1 ){
          // getting user by email and check unishop has the user or not
          $exists = email_exists( $result['email'] );
          $getUniCustomerType=checkUniCustomersType($result['email']);
          // change the user group based on result
          $result['customer_type']=1; // default value
          if($getUniCustomerType==1){
            $result['role']= 'student_parent';
          }else{
            $result['role']= 'super_counsellor'; // for super counsellors
          }
        
          if( !$exists ){           
            $userdata = array(
              'user_login' =>  $result['email'],
              'user_pass'  =>  md5($pass),
              'user_email' => $result['email'],
              'first_name' =>$result['first_name'],
              'last_name' => $result['last_name'],
              'role'  => $result['role']
             );   
            //insert user into database
             $user_id = wp_insert_user( $userdata);
             if ( ! is_wp_error( $user_id ) ) {                  
                uni_login_user( $result['email'] );
             }
             else {
              echo 0;
              die();
            }                   
          }
          else {
           uni_login_user( $result['email'] );            
        }
      }
      else {
        echo 0;
        die();
      }
}

// login user
function uni_login_user($email) {  
    if(!is_user_logged_in()){
      $user = get_user_by('email', $email );
      clean_user_cache($user->ID);
      wp_clear_auth_cookie();
      wp_set_current_user($user->ID);
      wp_set_auth_cookie($user->ID, true, false);
      update_user_caches($user);
      echo 1;
      die();
    }
    else {
        echo 4;
        die();
    }
}
//checking on univariety.com 
function getUniCustomersByEmail($email){
  $url="https://www.univariety.com/app/unishop/checkAccountExists?email=".$email;
     $server_output=file_get_contents($url);
     return $server_output;
}
//checking on univariety.com 
function checkUniCustomersType($email){
 $email=urlencode($email);
  $url="https://www.univariety.com/app/unishop/checkUser?email=".$email;
     $server_output=file_get_contents($url);
     return $server_output;
}

// intro video short code 
add_shortcode( 'intro_video', 'product_video_content' );
function product_video_content(){
if(get_field('intro_video')):
?>
<div class="video-button-wrapper" style="display: inline-block;margin-right:30px;font-size:76%;color:#fff;"><a href="<?php echo get_field('intro_video');?>" class="button primary lowercase open-video is-xlarge" style="border-radius:4px;color:#fff;background: transparent linear-gradient(106deg, #4DAD4C 0%, #8BC24B 100%) 0% 0% no-repeat padding-box;"> <i class="single-product-play"></i> <span style="color:#fff">Watch Intro</span></a></div>
<?php endif;
}

// remove item meta data: oc_order_product_id
add_filter( 'woocommerce_order_item_get_formatted_meta_data', 'univariety_order_item_get_formatted_meta_data', 10, 1 );
function univariety_order_item_get_formatted_meta_data($formatted_meta){
    $temp_metas = [];
    foreach($formatted_meta as $key => $meta) {
        if ( isset( $meta->key ) && ! in_array( $meta->key, [
                'oc_order_product_id'
            ] ) ) {
            $temp_metas[ $key ] = $meta;
        }
    }
    return $temp_metas;
}

// thank you page content
add_filter( 'woocommerce_thankyou_order_received_text', 'uni_thank_you',15,1 );
function uni_thank_you() {
    $added_text = '<p class="success-color woocommerce-notice woocommerce-notice--success woocommerce-thankyou-order-received"><strong>Thank you. Your order has been received. For more information please click on <a href="'.get_site_url().'/my-account/orders/">Order History</a>.</strong></p>';
    return $added_text ;
}

function order_edit_assessment_box()
{
         add_meta_box(
            'assessments_data',   // Unique ID
            'Assessment Details',  // Box title
            'uni_order_edit_block',  // Content callback, must be of type callable
            'shop_order' // Post type
        );    
}
add_action('add_meta_boxes', 'order_edit_assessment_box');
function uni_order_edit_block($post)
{
  global $wpdb;
  $order = wc_get_order( $post->ID );
  $order_assessments = $wpdb->get_results("select * from ".$wpdb->prefix."order_assessment where order_id=".$order->get_order_number());
  
  if ( $order_assessments ) {
    wc_get_template( 'order/admin-order-details-assessment.php', array( 'order_assessments'=>$order_assessments) );
  }
}

?>