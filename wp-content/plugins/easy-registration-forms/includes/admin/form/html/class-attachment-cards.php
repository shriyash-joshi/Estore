<?php
/**
 * Generates card view for Attachment Manager page
 *
 * @package    ERForms
 * @author     ERForms
 * @since      1.0.0
*/
class ERForms_Attachment_Cards extends ERForms_List_Cards {

	/**
	 * Number of attachments to show per page.
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
				'singular' => 'attachment',
				'plural'   => 'attachments',
				'ajax'     => false,
			)
		);

		// Default number of attachments to show per page
		$this->per_page = apply_filters( 'erforms_attachments_per_page', 10 );
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
	public function column_cb( $submission ) {
		return '';
	}
        
        public function before_first_card(){
            
        }
        
	/**
	 * Message to be displayed when there are no attachments.
	 *
	 * @since 1.0.0
	 */
	public function no_items() {
		_e( 'Whoops, no attachments yet.', 'erforms' );
	}

	/**
	 * Fetch and setup the final data
	 *
	 * @since 1.0.0
	 */
	public function prepare_items() {
                $_SERVER['REQUEST_URI'] = remove_query_arg( '_wp_http_referer', $_SERVER['REQUEST_URI'] );
		$form_id= absint($_GET['form_id']);
                $this->process_bulk_actions();

		$page     = $this->get_pagenum();
		$order    = isset( $_GET['order'] ) ? sanitize_text_field($_GET['order']) : 'DESC';
		$orderby  = isset( $_GET['orderby'] ) ? sanitize_text_field($_GET['orderby']) : 'ID';
		$per_page = $this->get_items_per_page( 'erforms_attachments_per_page', $this->per_page );
                $meta_query_args = array(
                                    'relation'=>'AND',
                                    array(
                                        'key' => 'erform_attachments',
                                        'compare' => 'EXISTS'
                                    ),
                                    array(
                                        'key'=>'erform_form_id',
                                        'value'=>$form_id
                                    )
                                );

                $post_query = array(
                    'orderby' => 'date',
                    'order' => 'DESC',
                    'nopaging'       => false,
                    'posts_per_page' => $per_page,
                    'paged'          => $page,
                    'no_found_rows'=>false,
                    'meta_query' => $meta_query_args
                );
		$data = erforms()->submission->get('',$post_query);
                // Fetch total attachments
                unset($post_query['posts_per_page']);
                $post_query['nopaging']= true;
                $total_data = erforms()->submission->get('',$post_query);
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
        
        public function card_actions($form){
                $options= erforms()->options->get_options();
        }
        
        
        public function card_body($post){
                $submission= erforms()->submission->get_submission($post->ID);
		$out = '<div class="erf-card-content">';
                $cover_image_attr = null;
                $attachment_count= count($submission['attachments']);
                foreach ($submission['attachments'] as $attachment) {
                    if (wp_attachment_is_image($attachment['f_val'])) {
                        $cover_image_attr = wp_get_attachment_image_src($attachment['f_val']);
                        break;
                    }
                }
                if (!empty($cover_image_attr)) {
                    $out .= '<div class="erf-attachment"><a href="?page=erforms-dashboard&form_id='.$submission['form_id'].'&submission_id='.$submission['id'].'&tab=submission_attachments">'
                            .'<img src="'.esc_url(erforms_get_attachment_url($attachment['f_val'],$submission['id'])).'" />'.
                        '</a></div>';
                }
                else
                {
                    $out .= '<div class="erf-attachment erf-attachment-missing"><a href="?page=erforms-dashboard&form_id='.$submission['form_id'].'&submission_id='.$submission['id'].'&tab=submission_attachments">'
                            . '<img src="' .ERFORMS_PLUGIN_URL .'/assets/admin/images/file-attachment.png"></a></div>';
                }
                if(!empty($submission['user'])){
                    $out .= '<div class="erf-submission-attachment">'.
                            '<strong>'.__('Submission From: ','erforms').'</strong>'.
                            '<a target="_blank" href="'.get_edit_user_link($submission['user']['ID']).'">'.$submission['user']['user_email'].'</a>'.
                            '</div>';
                }
                else
                {
                    $out .= '<div class="erf-submission-attachment">'.__('Submission From: Anonymous user','erforms').'</div>';
                }
                
                $out .= '<div class="erf-attachment-submission-view"><a target="_blank" href="?page=erforms-submission&view=submission&submission_id='.$submission['id'].'">'.__('Submission','erforms').'</a></div>';
                $out .= '<div class="erf-attachment-submission-view">'.sprintf(__('Total Attachments: %d','erforms'),$attachment_count).'</a></div>';
                
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
	}
}

