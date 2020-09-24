<?php
	        if(get_field('program_type') !=='batch' && get_field('program_type') !=='external'){
                echo do_shortcode('[add_to_cart_normal]');
            }
            if(get_field('program_type') =='batch') {
                echo do_shortcode('[block id="assessment-box-in-batch-program"]');
			}

			if(get_field('program_type') =='external') {
                echo do_shortcode('[block id="external-product-right-side-block"]');
			}

			if(get_field('cta_form_button_right') ) {
				echo do_shortcode('[block id="program-cta-right-sidebar-form"]');
			}			
        
			if( get_field('show_give_us_a_call_widget')){
				echo do_shortcode('[block id="premium-sidebar-give-a-call"]');
			}

			if( get_field('show_contact_form_widget') ){
				echo do_shortcode('[block id="pro-program-right-sidebar"]');
			}	
			
			if( get_field('show_syllabus_attachment')){
				echo do_shortcode('[block id="pro-program-sidebar-more-information"]');
			}			

			if(get_field('show_dilemmas')){
				if( get_field('dilemmas_addressed') ){
					echo do_shortcode('[product_dilemmas]');	
				} 
			}					
			if( get_field('show_course_info_widget') ){
				echo do_shortcode('[block id="simple-program-right-side-attributes"]');
			}	

			if( get_field('show_student_benefits')){
				echo do_shortcode('[block id="students-benefited-block"]');
			}

			if(get_field('show_certificate_widget')){
				echo do_shortcode('[block id="premium-product-certificate-sidebar"]');
			}

	?>

    <?php 	echo do_shortcode('[block id="get-brochure-cta-form"]'); ?>
<script>
		
		document.addEventListener( 'wpcf7mailsent', function( event ) {
			<?php if(get_field('cta_form_button_right')):?>
			    if ( '15904' == event.detail.contactFormId ) {
					window.open('<?php echo get_field('cta_right_sidebar_attachment') ;?>', '_blank');
			    }
			<?php endif;?>
			<?php if(get_field('overview_cta_button_attachment')):?>
				  if ( '15783' == event.detail.contactFormId ) {
					window.open('<?php echo get_field('overview_cta_button_attachment_file') ;?>', '_blank');
			    }			
			<?php endif;?>
			
		}, false );	 

</script>