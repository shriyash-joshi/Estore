<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              univariety.com
 * @since             1.0.0
 * @package           Univariety_opencart_to_woocommerce_migration
 *
 * @wordpress-plugin
 * Plugin Name:       Univariety Opencart to Woocommerce migration
 * Plugin URI:        univariety.com
 * Description:       Migrate Date from opencart to woocommerce
 * Version:           1.0.0
 * Author:            univariety
 * Author URI:        univariety.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       univariety_opencart_to_woocommerce_migration
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'UNIVARIETY_OPENCART_TO_WOOCOMMERCE_MIGRATION_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-univariety_opencart_to_woocommerce_migration-activator.php
 */
function activate_univariety_opencart_to_woocommerce_migration() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-univariety_opencart_to_woocommerce_migration-activator.php';
	Univariety_opencart_to_woocommerce_migration_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-univariety_opencart_to_woocommerce_migration-deactivator.php
 */
function deactivate_univariety_opencart_to_woocommerce_migration() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-univariety_opencart_to_woocommerce_migration-deactivator.php';
	Univariety_opencart_to_woocommerce_migration_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_univariety_opencart_to_woocommerce_migration' );
register_deactivation_hook( __FILE__, 'deactivate_univariety_opencart_to_woocommerce_migration' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-univariety_opencart_to_woocommerce_migration.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_univariety_opencart_to_woocommerce_migration() {

	$plugin = new Univariety_opencart_to_woocommerce_migration();
	$plugin->run();

}
run_univariety_opencart_to_woocommerce_migration();
// user password actions

remove_action('authenticate',  'wp_authenticate_email_password', 20);
add_action('authenticate',  'new_wp_authenticate_email_password', 20, 3);
function new_wp_authenticate_email_password( $user, $email, $password ) {
	global $wpdb;
	if ( $user instanceof WP_User ) {
		return $user;
	}

	if ( empty( $email ) || empty( $password ) ) {
		if ( is_wp_error( $user ) ) {
			return $user;
		}

		$error = new WP_Error();

		if ( empty( $email ) ) {
			// Uses 'empty_username' for back-compat with wp_signon().
			$error->add( 'empty_username', __( '<strong>Error</strong>: The email field is empty.' ) );
		}

		if ( empty( $password ) ) {
			$error->add( 'empty_password', __( '<strong>Error</strong>: The password field is empty.' ) );
		}

		return $error;
	}

	if ( ! is_email( $email ) ) {
		return $user;
	}

	$user = get_user_by( 'email', $email );

	if ( ! $user ) {
		return new WP_Error(
			'invalid_email',
			__( 'Unknown email address. Check again or try your username.' )
		);
	}

	/** This filter is documented in wp-includes/user.php */
	$user = apply_filters( 'wp_authenticate_user', $user, $password );

	if ( is_wp_error( $user ) ) {
		return $user;
	}

	if ( ! wp_check_password( $password, $user->user_pass, $user->ID ) ) {
		if($user->check_status){
			return new WP_Error(
				'incorrect_password','<strong>Error</strong>: email address and/or password is incorrect..'
				 .
				' <a href="' . wp_lostpassword_url() . '">' .
				__( 'Lost your password?' ) .
				'</a>'
			);
		}

		$user_data = $wpdb->get_row("SELECT * FROM oc_customer WHERE email = '".$email ."'" );
		if($user_data){
		$check = ( $user_data->password == sha1($user_data->salt . sha1($user_data->salt . sha1($password))) );
		if($check){
			wp_set_password($password,$user->ID);
			$wpdb->update('unwp_users', array('id'=>$user->ID, 'check_status'=>1),array('id'=>$user->ID));
			return get_user_by( 'email', $email );
		}else{
			return new WP_Error(
				'incorrect_password','<strong>Error</strong>: email address and/or password is incorrect..'
				 .
				' <a href="' . wp_lostpassword_url() . '">' .
				__( 'Lost your password?' ) .
				'</a>'
			);
		}

	}

		return new WP_Error(
			'incorrect_password',
			sprintf(
				/* translators: %s: Email address. */
				__( '<strong>Error</strong>: The password you entered for the email address %s is incorrect.' ),
				'<strong>' . $email . '</strong>'
			) .
			' <a href="' . wp_lostpassword_url() . '">' .
			__( 'Lost your password?' ) .
			'</a>'
		);
	}

	return $user;
}


function migrate_users() {
	global $wpdb;
	date_default_timezone_set("Asia/Kolkata"); // kolkata time zone
	$startTime = date("Y-m-d h:i:sa");
	WP_CLI::log( 'Migration Started  at: ' . $startTime );
  

	//creating user id for admin

	$adminUser = array(
		'user_login'           => 'sreeni', 
		'user_pass'            => 'Sreenivas23', 
		'role'=>'administrator',
		'user_email'=>'sreenivas+uni@zessta.com',
		'first_name'=>'Sreenivas',
		'last_name'=>'Y',
		'display_name'=>'Sreenivas'
	);	  
	$adminID = wp_insert_user( $adminUser );
	if($adminID):
		WP_CLI::log('Admin Created');
	endif;		
	$results = $wpdb->get_results("SELECT customer_id,customer_group_id,customer_role,password,email,firstname,lastname FROM oc_customer order by customer_id");
	$wpdb->query("ALTER TABLE unwp_users MODIFY COLUMN ID BIGINT(20) NOT NULL");
	//$wpdb->query("ALTER TABLE unwp_users ADD check_status INT NOT NULL DEFAULT '0' AFTER display_name");
	$lastID = 0;
	foreach ($results as $user_data)
	{        
		$wpdb->insert( 'unwp_users', array( 'ID' => $user_data->customer_id,'user_login'=>$user_data->email ) ); 
		$ID = $user_data->customer_id;
		$user_pass = $user_data->password;
		$user_login=$user_data->email;
		$user_email=$user_data->email;
		$display_name=$user_data->firstname;
		$first_name= $user_data->firstname;
		$last_name= $user_data->lastname;
		$customerRole = $user_data->customer_role;
		$customer_group_id=$user_data->customer_group_id;
		$role;
		$role ='customer';
		if($customer_group_id==1){
				$role="student_parent";
		}else if($customer_group_id==3){
			$role="counsellor";
		}else if($customer_group_id==2){
			$role="super_counsellor";
		}

		// get the address
		
		//$address = $wpdb->get_results("SELECT * FROM oc_address order by customer_id where customer_id=$user_data->customer_id limit 1");

	    $user_id = wp_insert_user( compact('ID','user_pass','user_login','user_email','display_name','first_name','last_name','role') );
 
	  
	// $user_id = wc_create_new_customer( $email, $username, $password );

	// update_user_meta( $user_id, "billing_first_name", 'God' );
	// update_user_meta( $user_id, "billing_last_name", 'Almighty' );
	 //add_user_meta( $user_id, '_oc_user_id', $user_id);
	
	// On success.
	if ( ! is_wp_error( $user_id ) ) {
		echo "User created : ". $user_id . " With Email ".$user_email . PHP_EOL;
		$lastID = $user_id;
	}                    
}
	//set auto increment on column back
	$wpdb->query("ALTER TABLE unwp_users MODIFY COLUMN ID BIGINT(20) NOT NULL AUTO_INCREMENT"); 
	WP_CLI::success( 'User Migration Success' );
	$endTime = date("Y-m-d h:i:sa");
	WP_CLI::log( 'Migration Completed  at: ' . $endTime );

	$startTimeObject = new DateTime($startTime);
	$endTimeObject = new DateTime($endTime);
	$interval = $startTimeObject->diff($endTimeObject);
	WP_CLI::log("Completed In: ".$interval->format('%y years %m months %a days %h hours %i minutes %s seconds'));

}

if ( class_exists( 'WP_CLI' )) {
	WP_CLI::add_command( 'univariety_migrate_users', 'migrate_users' );
}


function migrate_products() {
	global $wpdb;
	date_default_timezone_set("Asia/Kolkata"); // kolkata time zone
	$startTime = date("Y-m-d h:i:sa");
	WP_CLI::log( 'Migration Started  at: ' . $startTime );
	$results = $wpdb->get_results("SELECT p.product_id,p.model,p.sku,p.quantity,p.image,p.price,p.date_available,p.status,p.viewed,pd.name,pd.description,pd.email_subject,pd.video,pd.faq,pd.benefits,pd.email_body FROM oc_product p join oc_product_description pd on p.product_id=pd.product_id order by product_id");
	foreach ($results as $product_data)
	{        
		$category_array=$wpdb->get_results("SELECT cd.name from oc_category_description cd join oc_product_to_category pc on  pc.category_id=cd.category_id where pc.product_id=".$product_data->product_id);
		$categories=array();
		foreach($category_array as $category_data){
			array_push($categories,$category_data->name);
		}
    	$post = array(
				'post_author'=>1,
				'import_id'=>$product_data->product_id,
				'post_content' => $product_data->description,
				'post_status' => ($product_data->status==1)?"publish":"draft",
				'post_title' => $product_data->name,
				'post_parent' => '',
				'post_type' => "product",
			);
		 //Create post
		 $post_id = wp_insert_post( $post, false );	
  
	wp_set_object_terms( $post_id, $categories, 'product_cat' );
	wp_set_object_terms( $post_id, 'simple', 'product_type');
	update_post_meta( $post_id, '_visibility', 'visible' );
	update_post_meta( $post_id, '_stock_status', 'instock');
	update_post_meta( $post_id, 'total_sales', '0');
	update_post_meta( $post_id, '_downloadable', 'yes');
	update_post_meta( $post_id, '_virtual', 'yes');
	update_post_meta( $post_id, '_regular_price',  $product_data->price );
	update_post_meta( $post_id, '_sale_price',  $product_data->price );
	update_post_meta( $post_id, '_purchase_note', "" );
	update_post_meta( $post_id, '_featured', "no" );
	update_post_meta( $post_id, '_weight', "" );
	update_post_meta( $post_id, '_length', "" );
	update_post_meta( $post_id, '_width', "" );
	update_post_meta( $post_id, '_height', "" );
	update_post_meta( $post_id, '_sku', $product_data->sku);
	update_post_meta( $post_id, '_product_attributes', array());
	update_post_meta( $post_id, '_sale_price_dates_from', "" );
	update_post_meta( $post_id, '_sale_price_dates_to', "" );
	update_post_meta( $post_id, '_price', $product_data->price );
	update_post_meta( $post_id, '_sold_individually', "" );
	update_post_meta( $post_id, '_manage_stock', "no" );
	update_post_meta( $post_id, '_backorders', "no" );
	update_post_meta( $post_id, '_stock', "" );
	update_post_meta( $post_id, '_downloadable_files', '');
	update_post_meta( $post_id, '_download_limit', '');
	update_post_meta( $post_id, '_download_expiry', '');
	update_post_meta( $post_id, '_download_type', '');
	update_post_meta( $post_id, '_product_image_gallery', '');
	// custom product filelds
	update_post_meta( $post_id, 'viewed', $product_data->viewed );
	update_post_meta( $post_id, 'email_subject', $product_data->email_subject );
	update_post_meta( $post_id, 'email_body', $product_data->email_body );
	update_post_meta( $post_id, 'frequently_asked_questions', $product_data->faq );
	update_post_meta( $post_id, 'benefits', $product_data->benefits );
	update_post_meta( $post_id, 'program_type','normal');
	update_post_meta( $post_id, 'video', $product_data->video );
		// On success.
		if ( ! is_wp_error( $post_id ) ) {		
			echo "product created : ". $post_id . PHP_EOL;
		}  
	}
	WP_CLI::success( 'product Migration Success' );
	$endTime = date("Y-m-d h:i:sa");
	WP_CLI::log( 'Migration Completed  at: ' . $endTime );

	$startTimeObject = new DateTime($startTime);
	$endTimeObject = new DateTime($endTime);
	$interval = $startTimeObject->diff($endTimeObject);
	WP_CLI::log("Completed In: ".$interval->format('%y years %m months %a days %h hours %i minutes %s seconds'));

}

if ( class_exists( 'WP_CLI' )) {
	WP_CLI::add_command( 'univariety_migrate_products', 'migrate_products' );
}

function migrate_product_images(){
	global $wpdb;
	date_default_timezone_set("Asia/Kolkata"); // kolkata time zone
	$startTime = date("Y-m-d h:i:sa");
	WP_CLI::log( 'Image Migration Started  at: ' . $startTime );
	$results = $wpdb->get_results("SELECT p.product_id,p.model,p.sku,p.quantity,p.image,p.price,p.date_available,p.status,p.viewed,pd.name,pd.description,pd.email_subject,pd.video,pd.faq,pd.benefits,pd.email_body FROM oc_product p join oc_product_description pd on p.product_id=pd.product_id order by product_id");
	foreach ($results as $product_data)
	{       			
		$imageUrl = 'https://studentproducts.univariety.com/image/cache/'.str_replace('.png','',trim($product_data->image)).'-550x550.png';
		if($imageUrl !==''){
			Generate_Featured_Image($imageUrl,$product_data->product_id);
		}	
    }
	WP_CLI::success( 'Image Migration Success' );
	$endTime = date("Y-m-d h:i:sa");
	WP_CLI::log( 'Migration Completed  at: ' . $endTime );

	$startTimeObject = new DateTime($startTime);
	$endTimeObject = new DateTime($endTime);
	$interval = $startTimeObject->diff($endTimeObject);
	WP_CLI::log("Completed In: ".$interval->format('%y years %m months %a days %h hours %i minutes %s seconds'));

}


function Generate_Featured_Image( $image_url, $post_id  ){
    $upload_dir = wp_upload_dir();
    $image_data = file_get_contents($image_url);
    $filename = basename($image_url);
    if(wp_mkdir_p($upload_dir['path']))
      $file = $upload_dir['path'] . '/' . $filename;
    else
      $file = $upload_dir['basedir'] . '/' . $filename;
      file_put_contents($file, $image_data);

    $wp_filetype = wp_check_filetype($filename, null );
    $attachment = array(
		'post_author'=> 1,
        'post_mime_type' => $wp_filetype['type'],
        'post_title' => sanitize_file_name($filename),
        'post_content' => '',
        'post_status' => 'inherit'
    );
    $attach_id = wp_insert_attachment( $attachment, $file, $post_id );
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    $attach_data = wp_generate_attachment_metadata( $attach_id, $file );
    $res1= wp_update_attachment_metadata( $attach_id, $attach_data );
    $res2= set_post_thumbnail( $post_id, $attach_id );
}

if ( class_exists( 'WP_CLI' )) {
	WP_CLI::add_command( 'univariety_migrate_images', 'migrate_product_images' );
}

// migrate orders
function migrate_orders() {
	global $wpdb;
	date_default_timezone_set("Asia/Kolkata"); // kolkata time zone
	$startTime = date("Y-m-d h:i:sa");
	WP_CLI::log( 'Migration Started  at: ' . $startTime );
	$results = $wpdb->get_results("SELECT order_id,invoice_no,invoice_number,invoice_prefix,customer_id,firstname,lastname,email,telephone,payment_address_1,payment_address_2,payment_city,payment_zone,payment_postcode,payment_country,payment_method,payment_code,order_status_id,date_added,date_modified,comment FROM  $wpdb->dbname.oc_order");
	foreach ($results as $order_data)
	{        
	$status='failed';
	switch ($order_data->order_status_id) {
		case 17:
		  $status='completed';
		  break;
		case 18:
			$status='pending';
		break;
		case 19:
			$status='processing';
		  break;
		case 21:
			$status='cancelled';
		  break;
		case 22:
			$status='on-hold';
		  break;
		default:
		$status='failed';
	  }
	//   $user = get_userdata( $order_data->customer_id );
	// 	if ( $user === false ) {
	// 		echo "user not there\n";
	// 	} else {
			$data= array(
				'address' => array(
					'first_name' => $order_data->firstname,
					'last_name'  => $order_data->lastname,
					'company'    => '',
					'email'      => $order_data->email,
					'phone'      => $order_data->telephone,
					'address_1'  => $order_data->payment_address_1,
					'address_2'  => $order_data->payment_address_2,
					'city'       => $order_data->payment_city,
					'state'      => $order_data->payment_zone,	
					'postcode'   => $order_data->payment_postcode,
					'country'    => $order_data->payment_country,
				),
				'order_id'=> $order_data->order_id,
				'user_id'        => $order_data->customer_id,
				'order_comments' => '',
				'date_created'=> date("Y-m-d H:i:s",strtotime($order_data->date_added)),
				'date_modified'=> date("Y-m-d H:i:s",strtotime($order_data->date_modified)),
				'comment'=>$order_data->comment,
				'payment_method' => $order_data->payment_method,
				'order_status'   => array(
					'status' => $status,
					'note'   => '',
				),
				'line_items' => array(
				),
				'coupon_items' => array(
				),
				'fee_items' => array(
				),
			);
		
			try{
		
				$products=$wpdb->get_results("SELECT order_product_id,product_id,quantity,price FROM $wpdb->dbname.oc_order_product where order_id=".$order_data->order_id);
				foreach($products as $product_data)
				{
					array_push($data['line_items'],array(
						'quantity' => $product_data->quantity,
						'oc_order_product_id'=>$product_data->order_product_id, 
						'args'     => array(
							'product_id'    => $product_data->product_id,
							'variation_id'  => '',
							 'price' => $product_data->price,
							'variation'     => array(),
						)
							)
						);
				}
					$order_id=create_wc_order($data);
						// On success.
					if ( ! is_wp_error( $order_id ) ) {
					$postdate = date("Y-m-d H:i:s",strtotime($order_data->date_added));
		
					$update_post = array(
						'ID' => $order_id,
						'post_date' => $postdate,
						'post_date_gmt'=> get_gmt_from_date( $postdate )
					);
		
					wp_update_post( $update_post );	
		
						add_post_meta($order_id,'invoice_no', $order_data->invoice_no);
						add_post_meta($order_id,'invoice_number', $order_data->invoice_number);
						add_post_meta($order_id,'invoice_prefix', $order_data->invoice_prefix);
						add_post_meta($order_id,'oc_order_id', $order_data->order_id);
						add_post_meta($order_id,'oc_order_user',$order_data->customer_id);
						add_post_meta($order_id,'_order_number', $order_data->order_id);
						echo "order created : ". $order_id . PHP_EOL;
					}   
			}
			catch(Exception $e) {
				echo 'Message: ' .$e->getMessage();
			  }     
		//}       
}
	WP_CLI::success( 'order Migration Success' );
	$endTime = date("Y-m-d h:i:sa");
	WP_CLI::log( 'Migration Completed  at: ' . $endTime );
	$startTimeObject = new DateTime($startTime);
	$endTimeObject = new DateTime($endTime);
	$interval = $startTimeObject->diff($endTimeObject);
	WP_CLI::log("Completed In: ".$interval->format('%y years %m months %a days %h hours %i minutes %s seconds'));
}

function create_wc_order( $data ){
    $gateways = WC()->payment_gateways->get_available_payment_gateways();
    $order    = new WC_Order();
    // Set Billing and Shipping adresses
    foreach( array('billing_', 'shipping_') as $type ) {
        foreach ( $data['address'] as $key => $value ) {
            if( $type === 'shipping_' && in_array( $key, array( 'email', 'phone' ) ) )
                continue;
            $type_key = $type.$key;
            if ( is_callable( array( $order, "set_{$type_key}" ) ) ) {
                $order->{"set_{$type_key}"}( $value );
            }
        }
    }
    // Set other details
    $order->set_created_via( 'programatically' );
    $order->set_customer_id( $data['user_id'] );
    $order->set_currency( get_woocommerce_currency() );
    $order->set_prices_include_tax( 'yes' === get_option( 'woocommerce_prices_include_tax' ) );
    $order->set_customer_note( isset( $data['order_comments'] ) ? $data['order_comments'] : '' );
	$order->set_payment_method( isset( $gateways[ $data['payment_method'] ] ) ? $gateways[ $data['payment_method'] ] : $data['payment_method'] );
	$order->set_date_paid($data['date_created']);
	$order->set_date_completed($data['date_modified']);
	$order->set_customer_note($data['comment']);
	$order->update_meta_data( 'oc_order_id', $data['order_id'] );
	$order->update_meta_data( '_order_number', $data['order_id'] );

    // Line items
    foreach( $data['line_items'] as $line_item ) {
        $args = $line_item['args'];
        $product = wc_get_product( isset($args['variation_id']) && $args['variation_id'] > 0 ? $$args['variation_id'] : $args['product_id'] );
        $item_id=$order->add_product( $product, $line_item['quantity'], [
			'subtotal'     => $args['price'], 
			'total'        => $args['price'], 
		] );
		wc_add_order_item_meta($item_id, 'oc_order_product_id', $line_item['oc_order_product_id']);
    }
    $calculate_taxes_for = array(
        'country'  => $data['address']['country'],
        'state'    => $data['address']['state'],
        'postcode' => $data['address']['postcode'],
        'city'     => $data['address']['city']
    );
    // Coupon items
    if( isset($data['coupon_items'])){
        foreach( $data['coupon_items'] as $coupon_item ) {
			 $order->apply_coupon(sanitize_title($coupon_item['code']));
        }
    }
    // Fee items
    if( isset($data['fee_items'])){
        foreach( $data['fee_items'] as $fee_item ) {
            $item = new WC_Order_Item_Fee();
            $item->set_name( $fee_item['name'] );
            $item->set_total( $fee_item['total'] );
            $tax_class = isset($fee_item['tax_class']) && $fee_item['tax_class'] != 0 ? $fee_item['tax_class'] : 0;
            $item->set_tax_class( $tax_class ); // O if not taxable
            $item->calculate_taxes($calculate_taxes_for);
            $item->save();
            $order->add_item( $item );
        }
    }
    // Set calculated totals
    $order->calculate_totals();
    // Save order to database (returns the order ID)
    $order_id = $order->save();
    // Update order status from pending to your defined status
    if( isset($data['order_status']) ) {
        $order->update_status($data['order_status']['status'], $data['order_status']['note']);
    }
    // Returns the order ID
    return $order_id;
}

if ( class_exists( 'WP_CLI' )) {
	WP_CLI::add_command( 'univariety_migrate_orders', 'migrate_orders' );
}


function migrate_reviews() {
	global $wpdb;
	date_default_timezone_set("Asia/Kolkata"); // kolkata time zone
	$startTime = date("Y-m-d h:i:sa");
	WP_CLI::log( 'Reviews Migration Started  at: ' . $startTime );
	$results = $wpdb->get_results("SELECT * FROM $wpdb->dbname.oc_review");

	foreach ($results as $review_data)
	{        
		$comment_date     = date("Y-m-d H:i:s", strtotime($review_data->date_added) );
		$comment_date_gmt = get_date_from_gmt( $comment_date );
		$comment_id = wp_insert_comment( array(
			'comment_post_ID'      => $review_data->product_id,
			'comment_author'       => $review_data->author,
			'comment_author_email' => '',
			'comment_author_url'   => '',
			'comment_content'      => sanitize_text_field($review_data->text),
			'comment_type'         => '',
			'comment_date_gmt'		=> $comment_date_gmt,
			'comment_parent'       => 0,
			'user_id'              => $review_data->customer_id,
			'comment_author_IP'    => '',
			'comment_agent'        => '',
			'comment_date'         => $comment_date,
			'comment_approved'     => sanitize_text_field($review_data->status),
			) );	    		
		
		update_comment_meta( $comment_id, 'rating', $review_data->rating );
		// On success.
		if ( ! is_wp_error( $comment_id ) ) {
			echo "Review created : ". $comment_id . PHP_EOL;
		}                      
}
	WP_CLI::success( 'Reviews Migration Success' );
	$endTime = date("Y-m-d h:i:sa");
	WP_CLI::log( 'Migration Completed  at: ' . $endTime );

	$startTimeObject = new DateTime($startTime);
	$endTimeObject = new DateTime($endTime);
	$interval = $startTimeObject->diff($endTimeObject);
	WP_CLI::log("Completed In: ".$interval->format('%y years %m months %a days %h hours %i minutes %s seconds'));

}

if ( class_exists( 'WP_CLI' )) {
	WP_CLI::add_command( 'univariety_migrate_reviews', 'migrate_reviews' );
}


function migrate_coupons() {
	global $wpdb;
	date_default_timezone_set("Asia/Kolkata"); // kolkata time zone
	$startTime = date("Y-m-d h:i:sa");
	WP_CLI::log( 'Coupon Migration Started  at: ' . $startTime );
	$results = $wpdb->get_results("SELECT coupon_id,code,name,type,discount,logged,uni_logged,auto_apply,shipping,total,date_end,uses_total,uses_customer,status FROM $wpdb->dbname.oc_coupon");

	foreach ($results as $coupon_data)
	{        
		$coupon_code = $coupon_data->code; // Code
		$amount = $coupon_data->discount; // Amount
		$discount_type = ($coupon_data->type=='P')?'percent':'fixed_product';

		$coupon = array(
		'post_title' => $coupon_code,
		'post_content' => $coupon_data->name,
		'post_status' => ($coupon_data->status==1)?"publish":"draft",
		'post_author' => 1,
		'post_type' => 'shop_coupon');

		$new_coupon_id = wp_insert_post( $coupon );

		// Add meta
		update_post_meta( $new_coupon_id, 'discount_type', $discount_type );
		update_post_meta( $new_coupon_id, 'coupon_amount', $amount );
		update_post_meta( $new_coupon_id, 'individual_use', 'no' );
		update_post_meta( $new_coupon_id, 'product_ids', '' );
		update_post_meta( $new_coupon_id, 'exclude_product_ids', '' );
		update_post_meta( $new_coupon_id, 'minimum_amount', $coupon_data->total );
		update_post_meta( $new_coupon_id, 'usage_limit', $coupon_data->uses_total );
		update_post_meta( $new_coupon_id, 'usage_limit_per_user', $coupon_data->uses_customer );
		update_post_meta( $new_coupon_id, 'expiry_date', $coupon_data->date_end );
		update_post_meta( $new_coupon_id, 'apply_before_tax', 'yes' );
		update_post_meta( $new_coupon_id, 'free_shipping', ($coupon_data->shipping==0)?'no':'yes' );
		add_post_meta($new_coupon_id,'logged', $coupon_data->logged);
		add_post_meta($new_coupon_id,'uni_logged', $coupon_data->uni_logged);
		add_post_meta($new_coupon_id,'auto_apply', $coupon_data->auto_apply);
		add_post_meta($new_coupon_id,'oc_coupon_id', $coupon_data->coupon_id);
		// On success.
		if ( ! is_wp_error( $new_coupon_id ) ) {
			echo "coupon created : ". $new_coupon_id. PHP_EOL;
		}             
}

$coupon_history_data = $wpdb->get_results("SELECT c.code,c.discount,ch.order_id,ch.amount FROM $wpdb->dbname.oc_coupon_history ch join $wpdb->dbname.oc_coupon c on ch.coupon_id=c.coupon_id");

	foreach($coupon_history_data as $coupon_history){
		
		$order_id_data=$wpdb->get_results("SELECT post_id FROM $wpdb->dbname.unwp_postmeta where meta_key ='oc_order_id' and meta_value=". $coupon_history->order_id );
		
		
		$order_id=$order_id_data[0]->post_id;
		$order = new WC_Order( $order_id );
		if($order_id){
		$order = wc_get_order( $order_id );
		$item = new WC_Order_Item_Coupon();
		$item->set_props(array('code' => $coupon_history->code, 'discount' => $coupon_history->discount, 'discount_tax' => 0));
		$order->add_item($item);
		$order->save();
		}
		
	}

	WP_CLI::success( 'Coupons Migration Success' );
	$endTime = date("Y-m-d h:i:sa");
	WP_CLI::log( 'Migration Completed  at: ' . $endTime );

	$startTimeObject = new DateTime($startTime);
	$endTimeObject = new DateTime($endTime);
	$interval = $startTimeObject->diff($endTimeObject);
	WP_CLI::log("Completed In: ".$interval->format('%y years %m months %a days %h hours %i minutes %s seconds'));

}

if ( class_exists( 'WP_CLI' )) {
	WP_CLI::add_command( 'univariety_migrate_coupons', 'migrate_coupons' );
}


function update_orders_users( ){

	global $wpdb;
        // Iterating with a for loop through a range of numbers
        for( $order_id = 12617; $order_id <= 15577; $order_id++ ){

            // Getting the postmeta customer ID for 'order' post-type
			$user_id = get_post_meta( $order_id, 'oc_order_user', true );

			if( isset($user_id) && $user_id !=='' && $user_id !== null){
				$wc_customer_id = $wpdb->get_results("select customer_id from unwp_wc_customer_lookup where user_id=".$user_id." limit 1");
			
				echo $wc_customer_id['0']->customer_id."Customer ID\n";

				if(isset($wc_customer_id['0']->customer_id) && $wc_customer_id['0']->customer_id !=='' && $wc_customer_id['0']->customer_id !== null){
					$wpdb->query($wpdb->prepare("UPDATE unwp_wc_order_product_lookup SET customer_id=".$wc_customer_id['0']->customer_id." WHERE order_id=".$order_id));
					echo "Order Updated".$order_id;
				 }
				}			
			}	
	
		}

	
	if ( class_exists( 'WP_CLI' )) {
		WP_CLI::add_command( 'univariety_update_orders_users', 'update_orders_users' );
	}


	function update_orders_user_type(){
		global $wpdb;
		// Iterating with a for loop through a range of numbers
		for( $order_id = 12617; $order_id <= 15649; $order_id++ ){

			// Getting the user role and customer real user id
			$user_id = get_post_meta( $order_id, '_customer_user', true );

			if( isset($user_id) && $user_id !=='' && $user_id !== null){

				if($user_id == 0){
					$role = "guest";
					update_post_meta($order_id, 'user_type',$role);				
					echo $order_id ."- User Type : ".$role."\n"; 
				}
				else {
					$user = get_userdata( $user_id );
					// Get all the user roles as an array.
					$roles = $user->roles;	
					if(!empty($roles)){            
						if ( in_array( 'student_parent', $roles, true ) ) {
							$role = 'student_parent';
						} 
						else if (in_array( 'counsellor', $roles, true )){
							$role = 'counsellor';
						}
						else if (in_array( 'super_counsellor', $roles, true )){
							$role = 'super_counsellor';								
						}
					}
					update_post_meta($order_id, 'customer_type',$role);				
					echo $order_id ."- User Type : ".$role."\n"; 
				}				
				}			
			}	
	}
	
	if ( class_exists( 'WP_CLI' )) {
	WP_CLI::add_command( 'univariety_update_orders_user_type', 'update_orders_user_type' );
	}

	function update_order_product( ){
		global $wpdb;
		$missing_product_ids = $wpdb->get_results('SELECT * from new_product_ids');
		foreach($missing_product_ids as $missing_id):
				$order_ids = $wpdb->get_results('select oco.price,oco.total,oco.actual_price,oco.name,oco.order_product_id,oco.order_id,oco.product_id,op.model,op.sku from oc_product op join oc_order_product oco on op.product_id=oco.product_id where op.product_id='.$missing_id->old_id.' order by oco.order_id');
				// Iterating with a for loop through a range of numbers
				
				foreach( $order_ids as $order_id ){
					// Getting the post id  from _order_number  'order' post-type
					$post_order_id = $wpdb->get_results("select post_id from unwp_postmeta where meta_key='_order_number' and meta_value=".$order_id->order_id." limit 1");
	
					if(!empty($post_order_id)):
						$post_order_id = $post_order_id[0]->post_id;
						echo "WP Order ID ".$post_order_id.", OC Order Product ID:".$order_id->order_product_id."\n";
						
						// get order item id
						//$order_item_id = $wpdb->get_row( $wpdb->prepare("SELECT order_item_id FROM univariety_new.unwp_woocommerce_order_itemmeta where meta_key='oc_order_product_id' and meta_value=".$order_id->order_product_id));
						if( isset($post_order_id) && $post_order_id !=='' && $post_order_id !== null){
							$order_items = $wpdb->get_results("select * from unwp_woocommerce_order_items  where  order_item_type='line_item' and order_id=".$post_order_id );
							foreach($order_items as $order_item):
								echo "New Order Item ID: ".$order_item->order_item_id."\n";
								wc_update_order_item_meta($order_item->order_item_id, '_product_id', $missing_id->new_id );
								wc_update_order_item_meta($order_item->order_item_id, '_line_subtotal', $order_id->price );
								wc_update_order_item_meta($order_item->order_item_id, '_line_total', $order_id->total );
								wc_update_order_item($order_item->order_item_id, array('order_item_type'=>'line_item','order_item_name' => sanitize_text_field($order_id->name)));  
								
							endforeach;
							}	
					endif;				
				}	
		
			endforeach;
			}
		
		if ( class_exists( 'WP_CLI' )) {
			WP_CLI::add_command( 'univariety_update_order_product', 'update_order_product' );
		}
	
		function update_order_product_totals( ){
				global $wpdb;
				$order_ids = $wpdb->get_results('select oco.order_product_id,oco.quantity,oco.price,oco.total,oco.actual_price,oco.name,oco.order_product_id,oco.order_id,oco.product_id,op.model,op.sku from oc_product op join oc_order_product oco on op.product_id=oco.product_id order by oco.order_id');
						// Iterating with a for loop through a range of numbers
						
						foreach( $order_ids as $order_id ){
							// Getting the post id  from _order_number  'order' post-type
							$post_order_id = $wpdb->get_results("select post_id from unwp_postmeta where meta_key='_order_number' and meta_value=".$order_id->order_id." limit 1");
							// get order total
							$order_total = $wpdb->get_row( $wpdb->prepare('select value from oc_order_total where order_id='.$order_id->order_id.' and code="total" order by order_id'));	
							
							//$order_subtotal = $wpdb->get_row( $wpdb->prepare('select value from oc_order_total where order_id='.$order_id->order_id.' and code="sub_total" order by order_id'));	
							
							$coupon_total = $wpdb->get_row( $wpdb->prepare('select value from oc_order_total where order_id='.$order_id->order_id.' and code="coupon" order by order_id'));	
												
		
							if(!empty($post_order_id)):
								$post_order_id = $post_order_id[0]->post_id;
								echo "WP Order ID ".$post_order_id.", OC  Coupon Value :".str_replace("-","",$coupon_total->value)."\n";
								update_post_meta( $post_order_id, '_order_total', $order_total->value );
								if($coupon_total->value !=='' && $coupon_total->value !==null){
									update_post_meta( $post_order_id, '_cart_discount', str_replace("-","",$coupon_total->value) );
								}
								else {
									update_post_meta( $post_order_id, '_cart_discount', 0 );
								}							
								
								if( isset($post_order_id) && $post_order_id !=='' && $post_order_id !== null){
									$order_items = $wpdb->get_results("select * from unwp_woocommerce_order_items where order_id=".$post_order_id);
									foreach($order_items as $order_item):
										$order_item_id = $wpdb->get_row( $wpdb->prepare("select meta_value from unwp_woocommerce_order_itemmeta  where meta_key='oc_order_product_id' and order_item_id=".$order_item->order_item_id));
										
										if(!empty($order_item_id)){
											$order_item_data = $wpdb->get_row( $wpdb->prepare("select * from oc_order_product where order_product_id=".$order_item_id->meta_value));
											
											echo "New Order Item ID: ".$order_item->order_item_id."\n";
											wc_update_order_item_meta($order_item->order_item_id, '_line_subtotal', $order_item_data->price );
											wc_update_order_item_meta($order_item->order_item_id, '_line_total', $order_item_data->price*$order_item_data->quantity );
											
										}
										
									endforeach;
									}	
							endif;		
						}	
				
					}
				
				if ( class_exists( 'WP_CLI' )) {
					WP_CLI::add_command( 'univariety_update_order_product_totals', 'update_order_product_totals' );
				}

function update_order_product_item_ids(){
	global $wpdb;
	$order_item_ids = $wpdb->get_results('SELECT order_item_id,order_id FROM unwp_wc_order_product_lookup where product_id=0');
	foreach($order_item_ids as $order_item):
		$product_id = $wpdb->get_row( $wpdb->prepare('SELECT woi.meta_value as id FROM unwp_woocommerce_order_itemmeta woi join unwp_wc_order_product_lookup wopl on woi.order_item_id=wopl.order_item_id where woi.meta_key="_product_id" and woi.order_item_id='.$order_item->order_item_id));
		$wpdb->query($wpdb->prepare("UPDATE unwp_wc_order_product_lookup SET product_id=".$product_id->id." WHERE order_item_id=".$order_item->order_item_id));
		endforeach;
}
					
if ( class_exists( 'WP_CLI' )) {
	WP_CLI::add_command( 'univariety_update_order_product_item_ids', 'update_order_product_item_ids' );
}