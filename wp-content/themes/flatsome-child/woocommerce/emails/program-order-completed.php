<?php
/**
 * Assessment order email
 *
 
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * @hooked WC_Emails::email_header() Output the email header
 */
do_action( 'woocommerce_email_header', $email_heading, $email ); 

global $wpdb;

$order_assessments = $wpdb->get_results("select * from ".$wpdb->prefix."order_assessment where order_id=".$order->get_order_number()." and product_id=".$product_id);
$product = wc_get_product( $product_id );
$links_html = '';
foreach ($order_assessments as $row) {
	$link = $row->test_link;
	$label = 'Take Test';
	$showFooter = 0;
	switch ($row->test_provider) {
		case 'Univariety':
			$label = 'Signup/Signin';
			$showFooter = 1;
			break;
		case 'UnivarietyCE':
			$label = 'Get Started';
			$showFooter = ($showFooter == 1) ? 1 : 0;
			break;
		case 'Immrse':
			$label = 'Start Program';
			$showFooter = ($showFooter == 1) ? 1 : 0;
			break;
		default :
			if(!$row->test_provider && !$row->test_id) {
				$label = 'Choose Test';
				$showFooter = ($showFooter == 1) ? 1 : 0;
			}
			break;
	}
	
	$links_html .= '<tr><td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;">'.$product->get_name().'</td><td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;">'.$link.'</td><td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;"><a href="'.$link.'"><button style=" width: 120px; height: 30px; border-radius: 2px; background:rgba(80, 173, 85, 1); text-transform:uppercase; color: white; border: none;">'.$label.'</button></a></td></tr>';
}

$footerHtml = '';
if ($showFooter == 1) {
	$footerHtml = '<p style="background-color: transparent; font-family: Arial; font-size: 14px; white-space: pre-wrap;">Note: Use these links for taking tests and also for SignUp/SignIn into Univariety Account. Creating/logging into Univariety Account is essential for exploring a large database of Careers, Courses, Colleges, Scholarships and many more on our platform.</p>';
}

?>

<?php /* translators: %s: Customer first name */ ?>
<p><?php printf( esc_html__( 'Dear %s,', 'woocommerce' ), esc_html( $order->get_billing_first_name() . $order->get_billing_last_name() ) ); ?></p>
<?php /* translators: %s: Site title */ ?>

<p>
	<?php echo $email_body ; ?> 
</p>

<?php if(!empty($order_assessments)):?>
<table style="border-collapse: collapse; width: 100%; border: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;">
    <thead>
      <tr>
        <td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #EFEFEF; font-weight: bold; text-align: center; padding: 7px; color: #222222;">Product</td>
        <td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #EFEFEF; font-weight: bold; text-align: center; padding: 7px; color: #222222;">Important Links</td>
        <td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #EFEFEF; font-weight: bold; text-align: center; padding: 7px; color: #222222;">Action</td>
      </tr>
    </thead>
    <tbody><?php echo $links_html; ?>
    </tbody>   
  </table>
<?php endif;?> 
<?php echo $footerHtml; ?> 
<?php

/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if ( $additional_content ) {
	echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
}

?>

<?php
/*
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action( 'woocommerce_email_footer', $email );
