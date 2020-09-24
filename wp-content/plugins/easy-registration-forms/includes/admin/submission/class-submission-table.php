<?php
/**
 * Generates the table on the plugin submission page.
 *
 * @package    ERForms
 * @author     ERForms
 * @since      1.0.0
*/

class ERForms_Submission_Table extends ERForms_List_Table {

	/**
	 * Number of forms to show per page.
	 *
	 * @since 1.0.0
	 */
	public $per_page;
        
        /*
         * Default form
         */
        public $default_form;
        
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
				'singular' => 'submission',
				'plural'   => 'submissions',
				'ajax'     => false,
			)
		);

		// Default number of forms to show per page
		$this->per_page = apply_filters( 'erforms_submission_per_page', 20 );
	}

	/**
	 * Retrieve the table columns
	 *
	 * @since 1.0.0
	 * @return array $columns Array of all the list table columns
	 */
	public function get_columns() {
                $form= $this->get_form();
                $column_count= 1; 
                $columns= array('cb'=>'<input type="checkbox" />');
                $columns['id']="ID";
                $field_columns= array();
                if(!empty($form)){
                    $form = erforms()->form->get_form($form->ID);
                    if(!empty($form['fields'])){
                        foreach($form['fields'] as $field){
                            $field= (object) $field;
                            if($column_count>4)
                                break;

                            if(in_array($field->type, array('password','splitter')) || in_array($field->type, array('file','button','paragraph')))
                                    continue;
                            $field_columns[$field->label]= $field->label;
                            $column_count++;
                        }
                    }
                }
                if(!empty($form['plan_enabled'])){
                    $payment_status= __('Payment Status','erforms');
                    $field_columns['payment_status']= $payment_status;
                }
                $field_columns= apply_filters('erf_submission_'.$form['id'].'_table_columns',$field_columns); // For old user support who are using filters
                $field_columns = apply_filters('erf_admin_sub_columns',$field_columns,$form); // System will overwrite fields if columns are configured.
                $columns= array_merge($columns,$field_columns);
                $columns['created']= __( 'Created', 'erforms' );
		return $columns; 
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

		return '<input type="checkbox" class="erf-sub-cb" name="submission_id[]" value="' . absint( $form->ID ) . '" />';
	}

	/**
	 * Renders the columns.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post $form
	 * @param string $column_name
	 *
	 * @return string
	 */
	public function column_default( $submission_id, $column_name ) {
                $item_val='';
                $column_name= strtolower($column_name);
                $submission = erforms()->submission->get_submission($submission_id);
                $formatter = new ERForms_Submission_Formatter('html', $submission);
                $submission = $formatter->format();
                $form= erforms()->form->get_form($submission['form_id']);
                if($column_name=='unique id'){
                    $item_val= !empty($submission['unique_id']) ? $submission['unique_id'] : '';
                }
                foreach($submission['fields_data'] as $field_data){
                    if(strtolower($field_data['f_label'])==$column_name){
                        if(is_array($field_data['f_val']))
                            $item_val= implode (',', $field_data['f_val']);
                        else
                            $item_val= $field_data['f_val'];
                    }
                }
                if($column_name=='payment_status' && !empty($form['plan_enabled']) && !empty($submission['plans'])){
                    $item_val = !empty($submission['payment_status']) ? ucwords($submission['payment_status']) : 'NA';
                }
                if(empty($item_val)){
                    switch ( $column_name ) {
			  case 'id':
				  $item_val =$submission['id'];
				  break;

                          case 'created': 
				  $item_val = $submission['created_date'];
				  break;

                    }
                }
		

		return apply_filters( 'erforms_submission_table_column_value', $item_val, $column_name );
	}

	/**
	 * Render the form name column with action links.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post $submission
	 *
	 * @return string
	 */
	public function column_id( $submission ) {
		// Prepare variables.
		$name = '#'.$submission->ID;
                // Check for any labels(tags)
                $tags= erforms()->label->tags_by_submission($submission->ID);
                $tag_html= '<div class="submissions-tags flex-s-e">';
                foreach($tags as $tag){
                    $tag_html .= '<div class="erf-label-'.sanitize_title($tag).'">&nbsp;</div>';
                }
                $tag_html .= '</div>';
		$name = sprintf(
			'<a class="row-title" href="%s" title="%s"><strong>%s</strong></a>'.$tag_html,
			add_query_arg(
				array(
					'view'    => 'submission',
					'submission_id' => $submission->ID,
				),
				admin_url( 'admin.php?page=erforms-submission' )
			),
			__( 'Edit this form', 'erforms' ),
			$name
		);

		// Build all of the row action links.
		$row_actions = array();
                
		// Edit
		$row_actions['edit'] = sprintf(
			'<a href="%s" title="%s">%s</a>',
			add_query_arg(
				array(
					'view'    => 'submission',
					'submission_id' => $submission->ID,
				),
				admin_url( 'admin.php?page=erforms-submission' )
			),
			__( 'Edit this form', 'erforms' ),
			__( 'View', 'erforms' )
		);

		// Delete
		$row_actions['delete'] = sprintf(
			'<a class="erf_sub_delete" href="%s" title="%s">%s</a>',
			wp_nonce_url(
				add_query_arg(
					array(
						'action'  => 'delete',
						'submission_id' => $submission->ID,
                                                'erform_id'=> $this->default_form->ID
					),
					admin_url( 'admin.php?page=erforms-submissions' )
				),
				'erforms_delete_submission_nonce'
			),
			__( 'Delete this submission', 'erforms' ),
			__( 'Delete', 'erforms' )
		);

		// Build the row action links and return the value.
		$value = $name . $this->row_actions( $row_actions );

		return apply_filters( 'erforms_submission_row_actions', $value, $submission) ;
	}

	/**
	 * Define bulk actions available for our table listing.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = array(
			'delete' => __( 'Delete', 'erforms' ),
		);
                
		return $actions;
	}

	/**
	 * Process the bulk actions.
	 *
	 * @since 1.0.0
	 */
	public function process_bulk_actions() { 
		$ids = isset( $_GET['submission_id'] ) ? $_GET['submission_id'] : array();
		if (!is_array($ids)){
                    $ids = array( $ids );
		}
		$ids    = array_map('absint', $ids );
		$action = !empty($_REQUEST['action']) ? sanitize_text_field($_REQUEST['action']) : false;

		if ( empty( $ids ) || empty( $action ) ) {
			return;
		}
               
		// Delete one or multiple forms - both delete links and bulk actions
		if ( 'delete' === $this->current_action() && (wp_verify_nonce(wp_unslash($_GET['_wpnonce']),'erforms_delete_submission_nonce') || wp_verify_nonce(wp_unslash($_GET['_wpnonce']),'bulk-submissions'))) {
                    foreach ( $ids as $id ) {
                            erforms()->submission->delete($id);
                    }
                    ?>
                    <div class="notice updated">
                            <p>
                                    <?php
                                    if ( count( $ids ) === 1 ) {
                                            _e( 'Submission was successfully deleted.', 'erforms' );
                                    } else {
                                            _e( 'Submission were successfully deleted.', 'erforms' );
                                    }
                                    ?>
                            </p>
                    </div>
                    <?php
			
		}
	}

	/**
	 * Message to be displayed when there are no forms.
	 *
	 * @since 1.0.0
	 */
	public function no_items() {
		printf( __( 'Whoops, you haven\'t a submission yett.') );
	}

	/**
	 * Fetch and setup the final data for the table.
	 *
	 * @since 1.0.0
	 */
	public function prepare_items() {
                wp_enqueue_script('jquery-ui-datepicker');
                wp_enqueue_style('jquery-datepicker',ERFORMS_PLUGIN_URL.'assets/admin/css/jquery-datepicker.css');
                $_SERVER['REQUEST_URI'] = remove_query_arg( '_wp_http_referer', $_SERVER['REQUEST_URI'] );
		// Process bulk actions if found
		$this->process_bulk_actions();

		// Setup the columns
		$columns  = $this->get_columns();
                
		// Hidden columns (none)
		$hidden = array();

		// Define which columns can be sorted - form name, date
		$sortable = array();

		// Set column headers
		$this->_column_headers = array( $columns, $hidden, $sortable );
                
		// Get forms
		$order    = isset( $_GET['order'] ) ? sanitize_text_field(wp_unslash($_GET['order'])) : 'DESC';
                $page     = $this->get_pagenum();
		$orderby  = isset( $_GET['orderby'] ) ? sanitize_text_field(wp_unslash($_GET['orderby'])) : 'ID';
		$per_page = $this->get_items_per_page( 'erforms_submission_per_page', $this->per_page );
		$form= $this->get_form();
                if(!empty($form)){
                    $value = isset($_GET['filter_key']) ? sanitize_text_field(urldecode($_GET['filter_key'])) : '';
                    $user= get_user_by('email',$value);
                    $meta_input= array();
                    if(!empty($user)){
                        $meta_input= array(
                            'relation'=>'AND',
                            array(
                                    'key'     => 'erform_form_id',
                                    'value'   => $form->ID
                            ),
                            array(
                                    'key'=>'erform_user',
                                    'value'=> $user->ID
                                )
                         );
                    }
                    else if($value){
                        $meta_input= array(
                            'relation'=>'AND',
                            array(
                                    'key'     => 'erform_form_id',
                                    'value'   => $form->ID
                            ),
                            array(
                                    'value'=> $value,
                                    'compare'=>'LIKE'
                                )
                         );
                    }
                    else{
                        $meta_input= array(
                            array(
                                    'key'     => 'erform_form_id',
                                    'value'   => $form->ID
                            )
                         );
                    }
                    $post_query= array(
			'orderby'        => $orderby,
			'order'          => $order,
			'nopaging'       => false,
			'posts_per_page' => $per_page,
			'paged'          => $page,
			'no_found_rows'  => false,
                        'meta_query' => $meta_input,
                        'post_type'=>'erforms_submission'
                    );
                    $label_filter = isset($_GET['label_filter']) ? absint(urldecode($_GET['label_filter'])) : '';
                    if(!empty($label_filter)){
                        $post_query['tax_query']= array(
                                                    array('taxonomy' => erforms()->label->get_tax_type(),
                                                          'field'    => 'term_id',
                                                          'terms'    => $label_filter)
                                                  );
                    }
                    
                    $from_date= isset($_GET['from_date']) ? sanitize_text_field(urldecode($_GET['from_date'])) : '';
                    $end_date=   isset($_GET['end_date']) ? sanitize_text_field(urldecode($_GET['end_date'])) : '';
                    if(!empty($from_date) && !empty($end_date)){
                        $post_query['date_query'] = array(
                                    array(
                                        'after'     => $from_date,
                                        'before'    => $end_date,
                                        'inclusive' => true,
                                    ),
                                );
                    }
                    $data_query = new WP_Query($post_query);
                    $data= $data_query->posts;
                    $total_post_query= array('post_type'=>'erforms_submission','nopaging'=>true);
                    $total_post_query['meta_query'] = $meta_input;
                    $total_post_query['date_query']= isset($post_query['date_query']) ? $post_query['date_query'] : array();
                    /*if(!empty($value))
                    {
                        $total_post_query['erf_meta_content_s']= $value;
                    }*/

                    $total_query= new WP_Query($total_post_query);
                    $submissions    = $total_query->posts;
                    $total= count($submissions);

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
                } else{
                    $data= array();
                }
                
	}
        
        public function extra_tablenav($which){
            if($which==='top'){
                $dd_name= 'erform_id';
            }
            else{
               return;
            }    
            $value = isset($_GET['filter_key']) ? sanitize_text_field(urldecode($_GET['filter_key'])) : '';
            $from_date= isset($_GET['from_date']) ? sanitize_text_field(urldecode($_GET['from_date'])) : '';
            $end_date=   isset($_GET['end_date']) ? sanitize_text_field(urldecode($_GET['end_date'])) : '';      
        ?>    
            <div class="alignleft actions">
		<label class="screen-reader-text" for="erforms_selected_form"><?php __( 'Change Form;' ) ?></label>
		<select name="<?php echo esc_attr($dd_name); ?>" id="<?php echo esc_attr($dd_name); ?>">
			<option value=""><?php _e( 'Change Form' ) ?></option>
                        <?php if(isset($this->default_form->ID)): ?>
                            <?php erforms_dropdown_forms($this->default_form->ID); ?>
                        <?php endif; ?>
		</select>
               
                <?php if($which=='top'): ?>
                            <input type="text" placeholder="<?php _e('Search','erforms'); ?>" name="filter_key" id="filter_key" value="<?php echo esc_attr($value); ?>" />  
                            <?php $labels= erforms()->label->get_labels(); 
                                  $label_filter='';  
                                  if(!empty($labels)):  
                                    $label_filter = isset($_GET['label_filter']) ? absint(urldecode($_GET['label_filter'])) : '';
                            ?>
                            <select name="label_filter" id="label_filter">
                                <option value=""><?php _e('Select Label','erforms'); ?></option>
                                <?php foreach($labels as $label) : ?>
                                    <option <?php echo $label_filter==$label['id'] ? 'selected' : ''; ?> value="<?php echo esc_attr($label['id']); ?>"><?php echo $label['name']; ?></option>
                                <?php endforeach; ?>    
                            </select> 
                            <?php endif; ?>
                            <input type="text" value="<?php echo esc_attr($from_date); ?>" class="erf-filter-date" placeholder="<?php _e('From Date','erforms'); ?>" name="from_date" />
                            <input type="text" value="<?php echo esc_attr($end_date); ?>" class="erf-filter-date" placeholder="<?php _e('End Date','erforms'); ?>" name="end_date" />
                <?php endif; ?>
	<?php
			submit_button( __( 'Filter','erforms'), '', 'erforms_select_form', false );
                        $export_url= add_query_arg( array(
                            'erform_id' => $this->default_form->ID,
                            'search' => $value,
                            'action' => 'erf_submission_export',
                            'label_filter' => $label_filter,
                            'from_date' => $from_date,
                            'end_date' => $end_date
                        ),admin_url('admin-ajax.php?'));
                        echo '<a href="'.$export_url.'" class="button">'.__('Export','erforms').'</a>';
                        echo '<a href="?page=erforms-dashboard&form_id='.$this->default_form->ID.'" class="button button-primary">'.__('Go to Form','erforms').'</a>';
		echo '</div>';
                echo '<script>jQuery(document).ready(function(){$=jQuery; $(".erf-filter-date").datepicker({dateFormat: "mm/dd/yy"})})</script>';
        }
        
        public function get_form(){
            $form_id = isset($_GET['erform_id']) ? absint($_GET['erform_id']) : 0;
            $form= false;
               // if(empty($form_id))
                 //   $form_id = absint($_GET['erform_id2']);
                
                if(empty($form_id)){
                    $forms = erforms()->form->get('',array('orderby'=>'ID','order'=>'DESC'));
                    if(!empty($forms)){
                        $form = $forms[0];
                    }
                }
                else
                    $form = erforms()->form->get($form_id);
            $this->default_form= $form;    
            return $form;
        }
        
        /**
	 * Generates content for a single row of the table
	 *
	 * @since 3.1.0
	 *
	 * @param object $item The current item
	 */
	public function single_row( $item ) {
            $cls = '';
            $submission = erforms()->submission->get_submission($item->ID);
            if($submission && !empty($submission['user'])){
                $is_active= erforms()->user->get_meta($submission['user']['ID'], 'active');
                if($is_active==="0"){
                    $cls = 'erf_inactive_user';
                }
            }
            echo '<tr class="'.$cls.'">';
            $this->single_row_columns( $item );
            echo '</tr>';
	}
      
}
