<?php
do_action('pmxe_addons_html');
?>
<table class="wpallexport-layout wpallexport-step-1">
	<tr>
		<td class="left">
			<div class="wpallexport-wrapper">	
				<h2 class="wpallexport-wp-notices"></h2>
				<div class="wpallexport-header">
					<div class="wpallexport-logo"></div>
					<div class="wpallexport-title">
						<p><?php _e('WP All Export', 'wp_all_export_plugin'); ?></p>
						<h2><?php _e('Export to XML / CSV', 'wp_all_export_plugin'); ?></h2>					
					</div>
					<div class="wpallexport-links">
						<a href="http://www.wpallimport.com/support/" target="_blank"><?php _e('Support', 'wp_all_export_plugin'); ?></a> | <a href="http://www.wpallimport.com/documentation/" target="_blank"><?php _e('Documentation', 'wp_all_export_plugin'); ?></a>
					</div>
				</div>			

				<div class="clear"></div>				
				
				<?php if ($this->errors->get_error_codes()): ?>
					<?php $this->error() ?>
				<?php endif ?>						

		        <form method="post" class="wpallexport-choose-file" enctype="multipart/form-data" autocomplete="off">
		        	
		        	<div class="wpallexport-upload-resource-step-one rad4">						
						
						<div class="clear"></div>											
						
						<div class="wpallexport-import-types">
							<h2><?php _e('First, choose what to export.', 'wp_all_export_plugin'); ?></h2>							
							<a class="wpallexport-import-from wpallexport-url-type <?php echo 'advanced' != $post['export_type'] ? 'selected' : '' ?>" rel="specific_type" href="javascript:void(0);">
								<span class="wpallexport-icon"></span>
								<span class="wpallexport-icon-label"><?php _e('Specific Post Type', 'wp_all_export_plugin'); ?></span>
							</a>
							<a class="wpallexport-import-from wpallexport-file-type <?php echo 'advanced' == $post['export_type'] ? 'selected' : '' ?>" rel="advanced_type" href="javascript:void(0);">
								<span class="wpallexport-icon"></span>
								<span class="wpallexport-icon-label"><?php _e('WP_Query Results', 'wp_all_export_plugin'); ?></span>
							</a>
						</div>


						<input type="hidden" value="<?php echo $post['export_type']; ?>" name="export_type"/>
						
						<div class="wpallexport-upload-type-container" rel="specific_type">			
							
							<div class="wpallexport-file-type-options">
								
								<?php
									$custom_types = get_post_types(array('_builtin' => true), 'objects') + get_post_types(array('_builtin' => false, 'show_ui' => true), 'objects') + get_post_types(array('_builtin' => false, 'show_ui' => false), 'objects'); 
									foreach ($custom_types as $key => $ct) {
										if (in_array($key, array('attachment', 'revision', 'nav_menu_item', 'import_users', 'shop_webhook', 'acf-field', 'acf-field-group'))) unset($custom_types[$key]);
									}
									$custom_types = apply_filters( 'wpallexport_custom_types', $custom_types );
									global $wp_version;
									$sorted_cpt = array();
									foreach ($custom_types as $key => $cpt){

										$sorted_cpt[$key] = $cpt;

										// Put users & comments & taxonomies after Pages
										if ( ! empty($custom_types['page']) && $key == 'page' || empty($custom_types['page']) && $key == 'post' ){

											$sorted_cpt['taxonomies'] = new stdClass();
											$sorted_cpt['taxonomies']->labels = new stdClass();
											$sorted_cpt['taxonomies']->labels->name = __('Taxonomies','wp_all_export_plugin');

											$sorted_cpt['comments'] = new stdClass();
											$sorted_cpt['comments']->labels = new stdClass();
											$sorted_cpt['comments']->labels->name = __('Comments','wp_all_export_plugin');

											$sorted_cpt['users'] = new stdClass();
											$sorted_cpt['users']->labels = new stdClass();
											$sorted_cpt['users']->labels->name = __('Users','wp_all_export_plugin');
											break;
										}
									}
									$order = array('shop_order', 'shop_coupon', 'shop_customer', 'product');
									foreach ($order as $cpt){
										if (!empty($custom_types[$cpt])) $sorted_cpt[$cpt] = $custom_types[$cpt];
									}

									uasort($custom_types, "wp_all_export_cmp_custom_types");

									foreach ($custom_types as $key => $cpt) {
										if (empty($sorted_cpt[$key])){
											$sorted_cpt[$key] = $cpt;
										}
									}

                                if (  class_exists('WooCommerce') ){
                                    $reviewElement = new stdClass();
                                    $reviewElement->labels = new stdClass();
                                    $reviewElement->labels->name = __('WooCommerce Reviews', PMXE_Plugin::LANGUAGE_DOMAIN);

                                    $sorted_cpt = $this->insertAfter($sorted_cpt, 'product', 'shop_review', $reviewElement);
                                }

								?>								

								<select id="file_selector">
									<option value=""><?php _e('Choose a post type...', 'wp_all_export_plugin'); ?></option>									
					            	<?php if (count($sorted_cpt)): $unknown_cpt = array(); ?>
										<?php foreach ($sorted_cpt as $key => $ct):?>
											<?php

                        // Remove unused post types
                        if( in_array($key, array('wp_block', 'customize_changeset', 'custom_css', 'scheduled_action', 'scheduled-action', 'user_request', 'oembed_cache'))) {
                           continue;
                        }
												$image_src = 'dashicon-cpt';																								
												$cpt_label = $ct->labels->name;												

												if (  in_array($key, array('post', 'page', 'product', 'import_users', 'shop_order', 'shop_coupon', 'shop_customer', 'users', 'comments', 'taxonomies') ) )
												{
													$image_src = 'dashicon-' . $key;	 
												}
												else if($key == 'shop_review') {
                                                    $image_src = 'dashicon-review';
                                                }
												else
												{
													$unknown_cpt[$key] = $ct;
													continue;
												}
																				
											?>
											<option value="<?php echo $key;?>" data-imagesrc="dashicon <?php echo $image_src; ?>" <?php if ($key == $post['cpt']) echo 'selected="selected"'; ?>><?php echo $cpt_label; ?></option>
										<?php endforeach ?>
									<?php endif ?>
									<?php if ( ! empty($unknown_cpt)):  ?>
										<?php foreach ($unknown_cpt as $key => $ct):?>
											<?php
											$image_src = 'dashicon-cpt';																								
											$cpt_label = $ct->labels->name;												
											?>
											<option value="<?php echo $key;?>" data-imagesrc="dashicon <?php echo $image_src; ?>" <?php if ($key == $post['cpt']) echo 'selected="selected"'; ?>><?php echo $cpt_label; ?></option>
										<?php endforeach ?>
									<?php endif;?>
								</select>								
								<input type="hidden" name="cpt" value="<?php echo $post['cpt']; ?>"/>									
								<div class="taxonomy_to_export_wrapper">
									<input type="hidden" name="taxonomy_to_export" value="<?php echo $post['taxonomy_to_export'];?>">
									<select id="taxonomy_to_export">
										<option value=""><?php _e('Select taxonomy', 'wp_all_export_plugin'); ?></option>
										<?php $options = wp_all_export_get_taxonomies(); ?>
										<?php foreach ($options as $slug => $name):?>
											<option value="<?php echo $slug;?>" <?php if ($post['taxonomy_to_export'] == $slug):?>selected="selected"<?php endif;?>><?php echo $name;?></option>
										<?php endforeach;?>
									</select>
								</div>
							</div>
						</div>	

						<div class="wpallexport-upload-type-container" rel="advanced_type">						
							<div class="wpallexport-file-type-options">
								
								<select id="wp_query_selector">
									<option value="wp_query" <?php if ('wp_query' == $post['wp_query_selector']) echo 'selected="selected"'; ?>><?php _e('Post Type Query', 'wp_all_export_plugin'); ?></option>
									<option value="wp_user_query" <?php if ('wp_user_query' == $post['wp_query_selector']) echo 'selected="selected"'; ?>><?php _e('User Query', 'wp_all_export_plugin'); ?></option>
									<?php 
									global $wp_version;					
									if ( version_compare($wp_version, '4.2.0', '>=') ):										
									?>
									<option value="wp_comment_query" <?php if ('wp_comment_query' == $post['wp_query_selector']) echo 'selected="selected"'; ?>><?php _e('Comment Query', 'wp_all_export_plugin'); ?></option>
									<?php 
									endif;
									?>
								</select>
								<input type="hidden" name="wp_query_selector" value="<?php echo $post['wp_query_selector'];?>">
								<textarea class="wp_query" rows="10" cols="80" name="wp_query" placeholder="'post_type' => 'post', 'post_status' => array( 'pending', 'draft', 'future' )" style="width: 600px; margin-bottom: 15px;"><?php echo esc_html($post['wp_query']); ?></textarea>
								
							</div>
							
						</div>

                        <div class="wpallexport-free-edition-notice wpallexport-user-export-notice" >
                            <p>
                                <?php _e('The User Export Add-On Pro is required to Export Users', PMXE_Plugin::LANGUAGE_DOMAIN); ?>
                            </p>

                            <a href="http://www.wpallimport.com/portal/" target="_blank" class="upgrade_link"><?php _e('Click here to download the User Export Add-On', PMXE_Plugin::LANGUAGE_DOMAIN);?></a>

                        </div>

                        <div class="wpallexport-free-edition-notice wpallexport-customer-export-notice" >
                            <p>
                                <?php _e('The User Export Add-On Pro is required to Export WooCommerce Customers', PMXE_Plugin::LANGUAGE_DOMAIN); ?>
                            </p>

                            <a href="http://www.wpallimport.com/portal/" target="_blank" class="upgrade_link"><?php _e('Click here to download the User Export Add-On', PMXE_Plugin::LANGUAGE_DOMAIN);?></a>

                        </div>

						<div class="wp_all_export_preloader"></div>							

						<input type="hidden" class="hierarhy-output" name="filter_rules_hierarhy" value="<?php echo esc_html($post['filter_rules_hierarhy']);?>"/>
						<input type="hidden" class="wpallexport-preload-post-data" value="<?php echo $preload;?>">
					</div>			

					<div class="wpallexport-filtering-wrapper rad4">
						<div class="ajax-console" id="filtering_result">
							
						</div>
					</div>




                    <div id="wpallexport-filtering-container" class="wpallexport-upload-resource-step-two rad4 wpallexport-collapsed closed">
							
					</div>

					<p class="wpallexport-submit-buttons" <?php if ('advanced' == $post['export_type']) echo 'style="display:block;"';?>>
						<input type="hidden" name="custom_type" value="" />
						<input type="hidden" name="is_submitted" value="1" />
						<input type="hidden" name="auto_generate" value="0" />

						<?php wp_nonce_field('choose-cpt', '_wpnonce_choose-cpt'); ?>											

						<span class="wp_all_export_continue_step_two"></span>						

					</p>
					
					<table><tr><td class="wpallexport-note"></td></tr></table>
				</form>
				
				<a href="http://soflyy.com/" target="_blank" class="wpallexport-created-by"><?php _e('Created by', 'wp_all_export_plugin'); ?> <span></span></a>
				
			</div>
		</td>		
	</tr>
</table>
