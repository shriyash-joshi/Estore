<?php

/**
 * ERForms widget.
 *
 * @package    ERForms
 * @author     ERForms
 * @since      1.0
 */
class ERForms_Form_Widget extends WP_Widget {

	/**
	 * Holds widget settings defaults, populated in constructor.
	 *
	 * @since 1.0.2
	 * @var array
	 */
	protected $defaults;

	/**
	 * Constructor
	 *
	 * @since 1.0.2
	 */
	function __construct() {

		// Widget defaults.
		$this->defaults = array(
			'title'      => '',
			'form_id'    => '',
			'show_title' => false,
			'show_desc'  => false,
		);

		// Widget Slug.
		$widget_slug = 'erforms-form-widget';

		// Widget basics.
		$widget_ops = array(
			'classname'   => $widget_slug,
			'description' => __( 'Display a form.', 'Widget', 'erforms' ),
		);

		// Widget controls.
		$control_ops = array(
			'id_base' => $widget_slug,
		);

		// load widget
		parent::__construct( $widget_slug, __( 'ERForms', 'Widget', 'erforms' ), $widget_ops, $control_ops );
	}

	/**
	 * Outputs the HTML for this widget.
	 *
	 * @since 1.0.2
	 *
	 * @param array $args An array of standard parameters for widgets in this theme.
	 * @param array $instance An array of settings for this widget instance.
	 */
	function widget( $args, $instance ) {

		// Merge with defaults.
		$instance = wp_parse_args( (array) $instance, $this->defaults );

		echo $args['before_widget'];

		// Title
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}

		// Form
		if ( ! empty( $instance['form_id'] ) ) {
			echo do_shortcode('[erforms id="'.$instance['form_id'].'"]');
		}

		echo $args['after_widget'];
	}

	/**
	 * Deals with the settings when they are saved by the admin. Here is
	 * where any validation should be dealt with.
	 *
	 * @since 1.0.2
	 *
	 * @param array $new_instance An array of new settings as submitted by the admin.
	 * @param array $old_instance An array of the previous settings.
	 *
	 * @return array The validated and (if necessary) amended settings
	 */
	function update( $new_instance, $old_instance ) {

		$new_instance['title']      = wp_strip_all_tags( $new_instance['title'] );
		$new_instance['form_id']    = absint( $new_instance['form_id'] );
		return $new_instance;
	}

	/**
	 * Displays the form for this widget on the Widgets page of the WP Admin area.
	 *
	 * @since 1.0.2
	 *
	 * @param array $instance An array of the current settings for this widget.
	 *
	 * @return void
	 */
	function form( $instance ) {

		// Merge with defaults.
		$instance = wp_parse_args( (array) $instance, $this->defaults );
                ?>
                <p>
			<label for="<?php echo esc_attr($this->get_field_id( 'title' )); ?>">
				<?php _e( 'Title:', 'Widget', 'erforms' ); ?>
			</label>
			<input type="text"
					id="<?php echo esc_attr($this->get_field_id( 'title' )); ?>"
					name="<?php echo esc_attr($this->get_field_name( 'title' )); ?>"
					value="<?php echo esc_attr( $instance['title'] ); ?>" class="widefat"/>
		</p>
                
                <p>
			<label for="<?php echo esc_attr($this->get_field_id('form_id')); ?>">
				<?php _ex( 'Form:', 'Widget', 'erforms' ); ?>
			</label>
			<select id="<?php echo esc_attr($this->get_field_id('form_id')); ?>" name="<?php echo esc_attr($this->get_field_name('form_id')); ?>" class="widefat">
				<?php
				$forms = erforms()->form->get();
				if (!empty($forms)) 
                                {
                                    echo '<option value="" selected disabled>' . __( 'Select your form', 'Widget', 'erforms' ) . '</option>';
                                    foreach ( $forms as $form ) {
						echo '<option value="' . esc_attr( $form->ID ) . '" ' . selected( $instance['form_id'], $form->ID, false ) . '>' . esc_html( $form->post_title ) . '</option>';
					}
				} else {
					echo '<option value="">' . _x( 'No forms', 'Widget', 'erforms' ) . '</option>';
				}
				?>
			</select>
		</p>
                
                
        <?php        
	}
}

/**
 * Register ERForms plugin widgets.
 */
function erforms_register_widgets() {
	register_widget( 'ERForms_Form_Widget' );
}

add_action( 'widgets_init', 'erforms_register_widgets' );
