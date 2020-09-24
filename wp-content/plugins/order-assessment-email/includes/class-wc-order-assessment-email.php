<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * A custom Expedited Order WooCommerce Email class
 *
 * @since 0.1
 * @extends \WC_Email
 */
class WC_Order_Assessment_Email extends WC_Email {


	/**
	 * Set email defaults
	 *
	 * @since 0.1
	 */
	public function __construct() {

		// set ID, this simply needs to be a unique name
		$this->id = 'wc_order_assessment';
		// this is the title in WooCommerce Email settings
		$this->title = 'Order Assessments';
		$this->description = 'Program assessment links emails are sent to customers when their orders are marked completed';

	    // default values
		$this->heading = 'Your course details';
		$this->subject = 'Your course details';
		$this->product_id = '';

		// these define the locations of the templates that this email should use, we'll just use the new order template since this email is similar
		$this->template_html  = 'emails/program-order-completed.php';
		$this->template_plain = 'emails/plain/admin-new-order.php';
		// Trigger after completed orders
		add_action( 'order_assessment_email_notification', array( $this, 'trigger' ),10,4 );

		// Call parent constructor to load any other defaults not explicity defined here
		parent::__construct();

	}


	/**
	 * Determine if the email should actually be sent and setup email merge variables
	 *
	 * @since 0.1
	 * @param int $order_id
	 */
	public function trigger( $order, $product_id, $subject , $email_body) {

		if ( ! $order )
			return;
				
		$this->setup_locale();

		$this->subject = $subject;
		$this->product_id = $product_id;
  
		if ( is_a( $order, 'WC_Order' ) ) {
		  $this->object                         = $order;
		  $this->product_id						= $product_id;
		  $this->email_body						= $email_body;
		  $this->recipient                      = $this->object->get_billing_email();
		  $this->placeholders['{order_date}']   = wc_format_datetime( $this->object->get_date_created() );
		  $this->placeholders['{order_number}'] = $order->get_id();
		}
		if ( $this->is_enabled() && $this->get_recipient() ) {
		 $order->add_order_note( "Emails Triggered" );
		 $this->send( $this->get_recipient(), $subject , $this->get_content(), $this->get_headers(), $this->get_attachments() );
		}
  
		$this->restore_locale();
	
	}


	/**
     * Get email subject.
     *
     * @since  3.1.0
     * @return string
     */
    public function get_default_subject() {
		return __( 'Your {site_title} course enrollment is completed', 'woocommerce' );
	  }
  
	  /**
	   * Get email heading.
	   *
	   * @since  3.1.0
	   * @return string
	   */
	  public function get_default_heading() {
		return __( 'Thank you for enrolling', 'woocommerce' );
	  }
  
	  /**
	   * Get content html.
	   *
	   * @return string
	   */
	  public function get_content_html() {
		return wc_get_template_html(
		  $this->template_html,
		  array(
			'order'              => $this->object,
			'product_id'		 => $this->product_id, 
			'email_body'		 => $this->email_body, 	
			'email_heading'      => $this->get_heading(),
			'additional_content' => $this->get_additional_content(),
			'sent_to_admin'      => false,
			'plain_text'         => false,
			'email'              => $this,
		  )
		);
	  }
  
	  /**
	   * Get content plain.
	   *
	   * @return string
	   */
	  public function get_content_plain() {
		return wc_get_template_html(
		  $this->template_plain,
		  array(
			'order'              => $this->object,
			'email_heading'      => $this->get_heading(),
			'additional_content' => $this->get_additional_content(),
			'sent_to_admin'      => false,
			'plain_text'         => true,
			'email'              => $this,
		  )
		);
	  }
  
	  /**
	   * Default content to show below main email content.
	   *
	   * @since 3.7.0
	   * @return string
	   */
	  public function get_default_additional_content() {
		return __( 'Thanks for shopping with us.', 'woocommerce' );
	  }


} 
