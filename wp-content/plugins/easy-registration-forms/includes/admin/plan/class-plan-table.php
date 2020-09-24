<?php
/**
 * Generates the table on the plugin plan page.
 *
 * @package    ERForms
 * @author     ERForms
 * @since      1.0.0
*/
class ERForms_Plan_Table extends ERForms_List_Table {

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
				'singular' => 'plan',
				'plural'   => 'plans',
				'ajax'     => false,
			)
		);

		// Default number of forms to show per page
		$this->per_page = apply_filters( 'erforms_plan_per_page', 20 );
	}

	/**
	 * Retrieve the table columns
	 *
	 * @since 1.0.0
	 * @return array $columns Array of all the list table columns
	 */
	public function get_columns() {
                 $options= erforms()->options->get_options();
		$columns = array(
			'cb'         => '<input type="checkbox" />',
			'plan_name'  => __( 'Plan Name', 'erforms' ),
			'type'  => __( 'Type', 'erforms' ),
                        'price'=> __('Price','erforms').erforms_currency_symbol($options['currency']),
			'created'    => __( 'Created', 'erforms' ),
		);

		return apply_filters( 'erforms_plan_table_columns', $columns );
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
	public function column_cb( $plan ) {
		return '<input type="checkbox" name="plan_id[]" value="' . absint( $plan->ID ) . '" />';
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
	public function column_default( $post, $column_name ) {
                  $plan_model= erforms()->plan;  
                  $plan= $plan_model->get_plan($post);
		  switch ( $column_name ) {
			  case 'id':
				  $value = $plan->ID;
				  break;
                              
                          case 'plan_name':
				  $value = $plan->post_title;
				  break;
                              
                          case 'type': if($plan['type']=='user'){$value= __('User Defined','erforms');} 
                                       else if($plan['type']=='product'){$value= __('Product','erforms');}
                                       break;
                          case 'price': if($plan['type']=='user') {$value=__('Custom','erforms');} 
                                        else if($plan['type']=='product') {$value= $plan['price'];}
                                        break;
			  case 'created':
				  $value = get_the_date( get_option( 'date_format' ), $post );
				  break;
                              
			  default:
				  $value = '';
			}

		return apply_filters( 'erforms_plan_table_column_value', $value, $plan, $column_name );
	}

	/**
	 * Render the Plan name column with action links.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post $form
	 *
	 * @return string
	 */
	public function column_plan_name( $plan ) {
		// Prepare variables.
		$name = ! empty( $plan->post_title ) ? $plan->post_title : $plan->post_name;
		$name = sprintf(
			'<a class="row-title" href="%s" title="%s"><strong>%s</strong></a>',
			add_query_arg(
				array(
					'view'    => 'fields',
					'plan_id' => $plan->ID,
				),
				admin_url( 'admin.php?page=erforms-plan' )
			),
			__( 'Edit this Plan', 'erforms' ),
			$name
		);

		// Build all of the row action links.
		$row_actions = array();

		// Edit
		$row_actions['edit'] = sprintf(
			'<a href="%s" title="%s">%s</a>',
			add_query_arg(
				array(
					'view'    => 'fields',
					'plan_id' => $plan->ID,
				),
				admin_url( 'admin.php?page=erforms-plan' )
			),
			__( 'Edit this Plan', 'erforms' ),
			__( 'Edit', 'erforms' )
		);
                
		// Delete
		$row_actions['delete'] = sprintf(
			'<a href="%s"  title="%s">%s</a>',
			wp_nonce_url(
				add_query_arg(
					array(
						'action'  => 'delete',
						'plan_id' => $plan->ID,
					),
					admin_url( 'admin.php?page=erforms-plans' )
				),
				'erforms_delete_plan_nonce'
			),
			__( 'Delete this Plan', 'erforms' ),
			__( 'Delete', 'erforms' )
		);

		// Build the row action links and return the value.
		$value = $name . $this->row_actions( $row_actions );

		return apply_filters( 'erforms_plan_row_actions', $value, $plan );
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
		return array();
	}

	/**
	 * Process the bulk actions.
	 *
	 * @since 1.0.0
	 */
	public function process_bulk_actions() {

		$ids = isset( $_GET['plan_id'] ) ? $_GET['plan_id'] : array();
  
		if (!is_array($ids)) {
                    $ids = array($ids);
		}

		$ids    = array_map( 'absint', $ids );
		$action = ! empty( $_REQUEST['action'] ) ? sanitize_text_field($_REQUEST['action']) : false;

		if ( empty( $ids ) || empty( $action ) ) {
			return;
		}

		// Delete one or multiple forms - both delete links and bulk actions
		if ( 'delete' === $this->current_action() ) {

			if (
				wp_verify_nonce(wp_unslash($_GET['_wpnonce']), 'bulk-plans' ) ||
				wp_verify_nonce(wp_unslash($_GET['_wpnonce']), 'erforms_delete_plan_nonce' )
			) {
				foreach ( $ids as $id ) {
					erforms()->plan->delete( $id );
				}
				?>
				<div class="notice updated">
					<p>
						<?php
						if ( count( $ids ) === 1 ) {
							_e( 'Plan was successfully deleted.', 'erforms' );
						} else {
							_e( 'Plan were successfully deleted.', 'erforms' );
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
                        
			if (wp_verify_nonce(wp_unslash($_GET['_wpnonce']), 'erforms_duplicate_form_nonce' ) ) {
				foreach ( $ids as $id ) {
					erf_plans()->plan->duplicate( $id );
				}
				?>
				<div class="notice updated">
					<p>
						<?php
						if ( count( $ids ) === 1 ) {
							_e( 'Plan was successfully duplicated.', 'erforms' );
						} else {
							_e( 'Plans were successfully duplicated.', 'erforms' );
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

	/**
	 * Message to be displayed when there are no forms.
	 *
	 * @since 1.0.0
	 */
	public function no_items() {
		printf( __( 'Whoops, you haven\'t created a plan yet.', 'erforms' ), admin_url( 'admin.php?page=erforms-plan' ) );
	}

	/**
	 * Fetch and setup the final data for the table.
	 *
	 * @since 1.0.0
	 */
	public function prepare_items() {

		// Process bulk actions if found
		$this->process_bulk_actions();

		// Setup the columns
		$columns  = $this->get_columns();

		// Hidden columns (none)
		$hidden = array();

		// Define which columns can be sorted - form name, date
		$sortable = array(
			'plan_name' => array( 'title', false ),
			'created'   => array( 'date', false ),
		);

		// Set column headers
		$this->_column_headers = array( $columns, $hidden, $sortable );

		// Get forms
		$total    = wp_count_posts('erforms_plan')->publish;
		$page     = $this->get_pagenum();
		$order    = isset( $_GET['order'] ) ? sanitize_text_field(wp_unslash($_GET['order'])) : 'DESC';
		$orderby  = isset( $_GET['orderby'] ) ? sanitize_text_field(wp_unslash($_GET['orderby'])) : 'ID';
		$per_page = $this->get_items_per_page( 'erforms_plan_per_page', $this->per_page );
		$data     = erforms()->plan->get('', array(
                                                        'orderby'        => $orderby,
                                                        'order'          => $order,
                                                        'nopaging'       => false,
                                                        'posts_per_page' => $per_page,
                                                        'paged'          => $page,
                                                        'no_found_rows'  => false,
                                                ));

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
}
