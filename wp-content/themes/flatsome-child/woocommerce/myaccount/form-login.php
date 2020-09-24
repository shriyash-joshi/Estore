<?php
/**
 * Login Form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/form-login.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 4.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! fl_woocommerce_version_check( '3.5.0' ) ) { wc_print_notices(); }

do_action( 'woocommerce_before_customer_login_form' ); ?>

<div class="account-container lightbox-inner">

	<?php if ( 'yes' === get_option( 'woocommerce_enable_myaccount_registration' ) ) : ?>

	<div class="col2-set row row-divided row-large" id="customer_login">

		<div class="col-1 large-6 col pb-0">

			<?php endif; ?>

			<div class="account-login-inner">

				<h3 class="uppercase"><?php esc_html_e( 'Login', 'woocommerce' ); ?></h3>

				<form class="woocommerce-form woocommerce-form-login login" method="post">

					<?php do_action( 'woocommerce_login_form_start' ); ?>

					<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
						<label for="username"><?php esc_html_e( 'Username or email address', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
						<input required type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="username" autocomplete="username" value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" /><?php // @codingStandardsIgnoreLine ?>
					</p>
					<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
						<label for="password"><?php esc_html_e( 'Password', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
						<input required class="woocommerce-Input woocommerce-Input--text input-text" type="password" name="password" id="password" autocomplete="current-password" />
					</p>

					<?php do_action( 'woocommerce_login_form' ); ?>

               <a id="cust-login-button" href="#cust-login" class="button primary lowercase primary" style="border-radius:6px;font-size: 14px;margin-bottom: 10px;background: rgba(25, 125, 194, 1) !important;">
                  <span> <img src="/wp-content/uploads/2020/08/bird.png"> LOGIN WITH UNIVARIETY ACCOUNT</span>
               </a>

					<p class="form-row">
						<label class="woocommerce-form__label woocommerce-form__label-for-checkbox woocommerce-form-login__rememberme">
							<input class="woocommerce-form__input woocommerce-form__input-checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever" /> <span><?php esc_html_e( 'Remember me', 'woocommerce' ); ?></span>
						</label>
						<?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
						<button type="submit" class="woocommerce-button button woocommerce-form-login__submit" name="login" value="<?php esc_attr_e( 'Log in', 'woocommerce' ); ?>"><?php esc_html_e( 'Log in', 'woocommerce' ); ?></button>
					</p>
					<p class="woocommerce-LostPassword lost_password">
						<a href="<?php echo esc_url( wp_lostpassword_url() ); ?>"><?php esc_html_e( 'Lost your password?', 'woocommerce' ); ?></a>
					</p>

					<?php do_action( 'woocommerce_login_form_end' ); ?>

				</form>
			</div>

			<?php if ( 'yes' === get_option( 'woocommerce_enable_myaccount_registration' ) ) : ?>

		</div>

		<div class="col-2 large-6 col pb-0">

			<div class="account-register-inner">

				<h3 class="uppercase"><?php esc_html_e( 'Register', 'woocommerce' ); ?></h3>

				<form method="post" class="woocommerce-form woocommerce-form-register register" <?php do_action( 'woocommerce_register_form_tag' ); ?> >

					<?php do_action( 'woocommerce_register_form_start' ); ?>

					<?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>

						<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
							<label for="reg_username"><?php esc_html_e( 'Username', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
							<input required type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="reg_username" autocomplete="username" value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" /><?php // @codingStandardsIgnoreLine ?>
						</p>

					<?php endif; ?>

					<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
						<label for="reg_email"><?php esc_html_e( 'Email address', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
						<input required type="email" class="woocommerce-Input woocommerce-Input--text input-text" name="email" id="reg_email" autocomplete="email" value="<?php echo ( ! empty( $_POST['email'] ) ) ? esc_attr( wp_unslash( $_POST['email'] ) ) : ''; ?>" /><?php // @codingStandardsIgnoreLine ?>
					</p>

					<?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>

						<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
							<label for="reg_password"><?php esc_html_e( 'Password', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
							<input required type="password" class="woocommerce-Input woocommerce-Input--text input-text" name="password" id="reg_password" autocomplete="new-password" />
						</p>

					<?php else : ?>

						<p><?php esc_html_e( 'A password will be sent to your email address.', 'woocommerce' ); ?></p>

					<?php endif; ?>

					<?php do_action( 'woocommerce_register_form' ); ?>

					<p class="woocommerce-form-row form-row">
						<?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>
						<button type="submit" class="woocommerce-Button woocommerce-button button woocommerce-form-register__submit" name="register" value="<?php esc_attr_e( 'Register', 'woocommerce' ); ?>"><?php esc_html_e( 'Register', 'woocommerce' ); ?></button>
					</p>

					<?php do_action( 'woocommerce_register_form_end' ); ?>

				</form>

			
				<div id="cust-login" class="lightbox-by-id lightbox-content lightbox-white mfp-hide" style="max-width: 600px;">
               <div class="modal-content row" style="margin-top:3%;">
                  <div class="modal-header">
                     <center>
                        <h3 class="modal-title">Univariety Login</h3>
                     </center>
                  </div>
                  <div class="modal-body col large-12 ">
                     <form class="row-inner" method="post" id="uni_form">
                        <div class="form-group">
                           <div class="col medium-10 small-12 large-10">
                              <center>
                                 <div class="text-danger" style="color:red; font-size:16px;" id="vcommon"></div>
                              </center>
                           </div>
                        </div>
                        <div class="form-group">
                           <div class="col medium-10 small-12 large-10">
                              <input type="text" name="uni_email" placeholder="Email Id" id="uni_email" class="form-control remove-valid">
                              <div class="text-danger" style="color:red;" id="vuni_email"></div>
                           </div>
                        </div>
                        <div class="form-group">
                           <div class="col medium-10 small-12 large-10">
                              <input type="password" name="uni_pass" placeholder="Password" id="uni_pass" class="form-control remove-valid">
                              <div class="text-danger" style="color:red;" id="vuni_pass"></div>
                           </div>
                        </div>
                        <div class="form-group">
                           <div class="col medium-10 small-12 large-10"><label class="control-label"><a style="text-decoration: underline;color: rgba(25, 125, 194, 1);" href="//www.univariety.com/app/forgotPassword" target="_blank"><span>Forgot Password?</span></a></label></div>
                        </div>
                        <center><button style="width:50%;" type="button" id="uni_login_button" class="button btn-primary primary"><span>Login</span></button></center>
                     </form>
                  </div>
               </div>
            </div>
			</div>
      </div>
	</div>
<?php endif; ?>

</div>

<?php do_action( 'woocommerce_after_customer_login_form' ); ?>

<script>
   jQuery("#cust-login-button").on('click',function(){
      jQuery("#uni_form").trigger("reset");
      jQuery('#vuni_email').text('');
      jQuery('#vuni_pass').text('');

   });
//code for univariety login 
jQuery('#uni_login_button').on('click',function(){
   var uniEmail=jQuery('#uni_email').val().trim();
    var uniPass=jQuery('#uni_pass').val().trim();
    var check=0;
    if(uniEmail==''){
        jQuery('#vuni_email').text('Please Enter Univariety Account Email Id');
        check=1;
    }
     if(uniPass==''){
        jQuery('#vuni_pass').text('Please Enter your Account Password');
        check=1;
    }    
     if(check==1){
        return false;
    }
   
    var data=jQuery('#uni_form').serialize();
   jQuery.ajax({
      type:"post",
      url: '<?php echo admin_url("admin-ajax.php"); ?>',
      data:data+"&action=get_uni_ogin",
      success:function(result){  
         var rex = "</div>";
         var parsedResult = result.replace(rex , "").trim();
          if(parsedResult==1){
               window.parent.location.href='/my-account/';
          }

          if(parsedResult==4){
            window.parent.location.href='/my-account/';
          }
          
          if(parsedResult==0){
             jQuery('#vcommon').text('Login Failed, Please Verify Your Account Credentials'); 
          }
      },
      error: function(xhr, ajaxOptions, thrownError) {
      alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                    console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
   }
   }); 
});
</script>