<?php
/**
 * Generates card view for Form Manager page
 *
 * @package    ERForms
 * @author     ERForms
 * @since      1.0.0
*/
class ERForms_Form_Cards extends ERForms_List_Cards {

	/**
	 * Number of forms to show per page.
	 *
	 * @since 1.0.0
	 */
	public $per_page;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Bring globals into scope for parent.
		global $status, $page;

		// Utilize the parent constructor to build the main class properties.
		parent::__construct(
			array(
				'singular' => 'form',
				'plural'   => 'forms',
				'ajax'     => false,
			)
		);

		// Default number of forms to show per page
		$this->per_page = apply_filters( 'erforms_overview_per_page', 10 );
	}

	/**
	 * Render the checkbox column.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post $form
	 *
	 * @return string
	 */
	public function column_cb( $form ) {
		return '<input type="checkbox" name="form_id[]" value="' . absint( $form->ID ) . '" />';
	}

	/**
	 * Message to be displayed when there are no forms.
	 *
	 * @since 1.0.0
	 */
	public function no_items() {
		printf( __( 'Whoops, you haven\'t created a form yet.', 'erforms' ), admin_url( 'admin.php?page=erforms-dashboard' ) );
	}

	/**
	 * Fetch and setup the final data
	 *
	 * @since 1.0.0
	 */
	public function prepare_items() {
                $_SERVER['REQUEST_URI'] = remove_query_arg( '_wp_http_referer', $_SERVER['REQUEST_URI'] );
		
                $this->process_bulk_actions();

		// Define which columns can be sorted - form name, date
		$sortable = array(
			'form_name' => array( 'title', false ),
			'created'   => array( 'date', false ),
		);
                $search = isset($_GET['filter_key']) ? sanitize_text_field(urldecode(wp_unslash($_GET['filter_key']))) : '';
		// Get forms
		
		$page     = $this->get_pagenum();
		$order    = isset( $_GET['order'] ) ? sanitize_text_field(wp_unslash($_GET['order'])) : 'DESC';
		$orderby  = isset( $_GET['orderby'] ) ? sanitize_text_field(wp_unslash($_GET['orderby'])) : 'ID';
		$per_page = $this->get_items_per_page( 'erforms_forms_per_page', $this->per_page );
                $post_query= array(
                                                        'orderby'        => $orderby,
                                                        'order'          => $order,
                                                        'nopaging'       => false,
                                                        'posts_per_page' => $per_page,
                                                        'paged'          => $page,
                                                        'no_found_rows'  => false,
                                                        's'=>$search,
                              );
                
		$data     = erforms()->form->get('',$post_query);
                
                // Fetch total forms
                unset($post_query['posts_per_page']);
                $post_query['nopaging']= true;
                $total_data = erforms()->form->get('',$post_query);
                $total    = count($total_data);
                
		// Giddy up
		$this->items = $data;

		// Finalize pagination
		$this->set_pagination_args(
			array(
				'total_items' => $total,
				'per_page'    => $per_page,
				'total_pages' => ceil( $total / $per_page ),
			)
		);
	}
        
        public function before_first_card(){
            echo '<div class="erf-form-card-wrap erf-add-form-card">
                            <div class="erf-form-card"><div class="erf-card-content">
                            	<a href="javascript:void(0)" id="erf_overview_add_form"><span class="dashicons dashicons-plus"></span></a>
                            	<span class="erf-add-pop">' . __('Add New Form','erforms') .'</span>

                  </div></div></div>';
        }
        
        public function card_actions($form){
                $options= erforms()->options->get_options();
		$out = '<a href="javascript:void(0)" class="erf-card-menu erf-form-card-menu" id="erf-card-menu"><span class="menu-icon"></span></a>';
                $out .= '<ul class="erf-card-actions erform-hidden">';
                $out .= '<li class="erf-action"><a href="'.admin_url('/admin.php?page=erforms-dashboard&form_id='.$form->ID.'&tab=build').'"><img src="'.ERFORMS_PLUGIN_URL.'/assets/admin/images/edit-card.png"><span>'.__('Fields','erforms').'</span></a></li>';
                $out .= '<li class="erf-action"><a href="'.admin_url('/admin.php?page=erforms-dashboard&form_id='.$form->ID).'"><img  src="'.ERFORMS_PLUGIN_URL.'/assets/admin/images/settings-card.png"><span>'.__('Dashboard','erforms').'</span></a></li>';
                $out .= '<li class="erf-action"><a target="_blank" href="'.add_query_arg(array('erform_id'=>$form->ID),get_permalink($options['preview_page'])).'"><img src="'.ERFORMS_PLUGIN_URL.'/assets/admin/images/preview-card.png"><span>'.__('Preview','erforms').'</span></a></li>';
                $duplicate_url= wp_nonce_url(
				add_query_arg(
					array(
						'action'  => 'duplicate',
						'form_id' => $form->ID,
					),
					admin_url( 'admin.php?page=erforms-overview' )
				),
				'erforms_duplicate_form_nonce'
			);
                $out .= '<li class="erf-action"><a href="'.$duplicate_url.'"><img src="'.ERFORMS_PLUGIN_URL.'/assets/admin/images/duplicate-card.png"><span>'.__('Duplicate','erforms').'</span></a></li>';
                $delete_url= wp_nonce_url(
				add_query_arg(
					array(
						'action'  => 'delete',
						'form_id' => $form->ID,
					),
					admin_url( 'admin.php?page=erforms-overview' )
				),
				'erforms_delete_form_nonce'
			);
                $out .= '<li class="erf-action"><a class="erf_overview_delete_form_btn erf-delete" data-delete-url="'.$delete_url.'"><img src="'.ERFORMS_PLUGIN_URL.'/assets/admin/images/delete-card.png"><span>'.__('Delete','erforms').'</span></a></li>';
                
                $out .= '</ul>';
		return $out;
	
        }
        
        
        public function card_body($post){
		$out = '<div class="erf-card-content">';
                $form= erforms()->form->get_form($post->ID);
                $out .= '<div class="erf-form-type-indicator erf-pop-wrap">';
                  if($form['type']=='reg'){
                    $out .=  '<span class="erf-form-type-icon dashicons dashicons-admin-users"></span><span class="erf-pop-title">'. __('Registration Form','erforms') .'</span>';
                  }else{

                    $out .= '<span class="erf-form-type-icon dashicons dashicons-smartphone"></span><span class="erf-pop-title">'. __('Contact Form','erforms') .'</span>';
                  }
                $out .= '</div>';
                $out .= '<div class="post-title erf-card-title erf-pop-wrap"><a href="'.admin_url('/admin.php?page=erforms-dashboard&form_id='.$post->ID).'" title="' . $post->post_title .'">'.$post->post_title.'</a></div>';
                $shortcode = '[erforms id="'.$post->ID.'"]';
                $out .= "<div class='erf-short-code-wrap erf-pop-wrap'><input type='text' class='erf-shortcode' value='"."$shortcode"."' readonly><span style='display: none;' class='copy-message'>Copied to Clipboard</span><span class='erf-pop-title'>" . __('Click to copy','erforms') ."</span></div>";
                $total= erforms()->submission->get_submissions_by_form($form['id']);  
                $out .= '<div class="erf-submission-count"><a href="'.admin_url("/admin.php?page=erforms-submissions&erform_id=".$form['id']).'"> '.count($total).' <span class="erf-detail-title"><small>'.__('Submissions','erforms').'</small></span></a></div>';
                $out .= '</div>';
                return $out;
	
        }
        
        public function process_bulk_actions() {

		$ids = isset( $_GET['form_id'] ) ? $_GET['form_id'] : array();
  
		if ( ! is_array( $ids ) ) {
			$ids = array( $ids );
		}

		$ids    = array_map( 'absint', $ids );
		$action = ! empty( $_REQUEST['action'] ) ? sanitize_text_field($_REQUEST['action']) : false;

		if ( empty( $ids ) || empty( $action ) ) {
			return;
		}

		// Delete one or multiple forms - both delete links and bulk actions
		if ( 'delete' === $this->current_action() ) {

			if (
				wp_verify_nonce(wp_unslash($_GET['_wpnonce']), 'bulk-forms' ) ||
				wp_verify_nonce(wp_unslash($_GET['_wpnonce']), 'erforms_delete_form_nonce' )
			) {
				foreach ( $ids as $id ) {
					erforms()->form->delete( $id );
				}
				?>
				<div class="notice updated">
					<p>
						<?php
						if ( count( $ids ) === 1 ) {
							_e( 'Form was successfully deleted.', 'erforms' );
						} else {
							_e( 'Forms were successfully deleted.', 'erforms' );
						}
						?>
					</p>
				</div>
				<?php
			} else {
				?>
				<div class="notice updated">
					<p>
						<?php _e( 'Security check failed. Please try again.', 'erforms' ); ?>
					</p>
				</div>
				<?php
			}
		}

		// Duplicate form - currently just delete links (no bulk action at the moment)
		if ( 'duplicate' === $this->current_action() ) {
                        
			if ( wp_verify_nonce(wp_unslash($_GET['_wpnonce']), 'erforms_duplicate_form_nonce' ) ) {
				foreach ( $ids as $id ) {
					erforms()->form->duplicate( $id );
				}
				?>
				<div class="notice updated">
					<p>
						<?php
						if ( count( $ids ) === 1 ) {
							_e( 'Form was successfully duplicated.', 'erforms' );
						} else {
							_e( 'Forms were successfully duplicated.', 'erforms' );
						}
						?>
					</p>
				</div>
				<?php
			} else {
				?>
				<div class="notice updated">
					<p>
						<?php _e( 'Security check failed. Please try again.', 'erforms' ); ?>
					</p>
				</div>
				<?php
			}
		}
	}
}

