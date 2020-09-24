<?php
/**
 * Widget
 */
class Lshowcase_Widget extends WP_Widget

{
	public

	function __construct()
	{
		$options = get_option( 'lshowcase-settings' );
		$name = $options['lshowcase_name_singular'];
		$nameplural = $options['lshowcase_name_plural'];
		$widget_ops = array(
			'classname' => 'lshowcase_widget',
			'description' => 'Display ' . $name . ' images on your website'
		);
		parent::__construct( 'lshowcase_widget', $nameplural, $widget_ops);
	}

	public

	function widget($args, $instance)
	{
		extract($args);
		$title = apply_filters( 'widget_title', empty($instance['title']) ? '' : $instance['title'], $instance, $this->id_base);
		$orderby = isset($instance['orderby']) ? strip_tags($instance['orderby']) : 'title';
		$category = isset($instance['category']) ? $instance['category'] : '0';
		$tag = isset($instance['tag']) ? $instance['tag'] : '0';
		$style = isset($instance['style']) ? $instance['style'] : 'normal';
		$interface = isset($instance['interface']) ? strip_tags($instance['interface']) : 'grid';
		$activeurl = isset($instance['activeurl']) ? $instance['activeurl'] : '1';
		$tooltip = isset($instance['tooltip']) ? $instance['tooltip'] : 'false';
		$description = isset($instance['description']) ? $instance['description'] : 'false';
		$limit = isset($instance['limit']) ? $instance['limit'] : '0';
		$padding = isset($instance['padding']) ? $instance['padding'] : '5';
		$margin = 5;
		$slidersettings = "";
		$img = 0;
		$ids = '0';
		$filter = isset($instance['filter']) ? $instance['filter'] : 'false';
		$class = isset($instance['class']) ? $instance['class'] : '';
		echo $before_widget;
		if (!empty($title)) echo $before_title . $title . $after_title;
		echo build_lshowcase($orderby, $category, $tag, $activeurl, $style, $interface, $tooltip, $description, $limit, $slidersettings,$img,$ids,$filter,$class,'OR', false, $padding, $margin);
		echo $after_widget;

		if($filter=='hide') {
			lshowcase_filter_code();
		}
		if($filter=='enhance') {
			lshowcase_enhance_filter_code();
		}

	}

	public

	function update($new_instance, $old_instance)
	{
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['orderby'] = strip_tags($new_instance['orderby']);
		$instance['category'] = $new_instance['category'];
		$instance['style'] = strip_tags($new_instance['style']);
		$instance['interface'] = strip_tags($new_instance['interface']);
		$instance['activeurl'] = $new_instance['activeurl'];
		$instance['tooltip'] = $new_instance['tooltip'];
		$instance['description'] = $new_instance['description'];
		$instance['limit'] = $new_instance['limit'];
		$instance['filter'] = $new_instance['filter'];
		$instance['class'] = $new_instance['class'];
		$instance['padding'] = $new_instance['padding'];
		return $instance;
	}

	public

	function form($instance)
	{
		$instance = wp_parse_args((array)$instance, array(
			'title' => '',
			'orderby' => 'menu_order',
			'category' => '0',
			'style' => 'normal',
			'interface' => 'grid',
			'activeurl' => '1',
			'tooltip' => 'false',
			'description' => 'false',
			'limit' => '0',
			'filter' => 'false',
			'class' => '',
			'padding' => '5',
		));
		$title = strip_tags($instance['title']);
		$orderby = strip_tags($instance['orderby']);
		$category = $instance['category'];
		$style = strip_tags($instance['style']);
		$interface = strip_tags($instance['interface']);
		$activeurl = $instance['activeurl'];
		$tooltip = $instance['tooltip'];
		$description = $instance['description'];
		$padding = $instance['padding'];
		$limit = $instance['limit'];
		$filter = $instance['filter'];
		$class = $instance['class'];
?>
        <p><label for="<?php
		echo $this->get_field_id( 'title' ); ?>">Title:</label>
        <input class="widefat" id="<?php
		echo $this->get_field_id( 'title' ); ?>" name="<?php
		echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php
		echo esc_attr($title); ?>" /></p>


<p>
        <label for="<?php
		echo $this->get_field_id( 'orderby' ); ?>">Order By:<br />
        </label>
        <select id="<?php
		echo $this->get_field_id( 'orderby' ); ?>" name="<?php
		echo $this->get_field_name( 'orderby' ); ?>">
            <option value="menu_order" <?php
		selected($orderby, 'menu_order' ); ?>>Default</option>
            <option value="name" <?php
		selected($orderby, 'name' ); ?>>Title</option>
            <option value="ID" <?php
		selected($orderby, 'ID' ); ?>>ID</option>
            <option value="date" <?php
		selected($orderby, 'date' ); ?>>Date</option>
            <option value="modified" <?php
		selected($orderby, 'modified' ); ?>>Modified</option>
            <option value="rand" <?php
		selected($orderby, 'rand' ); ?>>Random</option>
        </select></p>

              <p><label for="<?php
		echo $this->get_field_id( 'limit' ); ?>">Number of Images to display:</label><br />

        <input size="3" id="<?php
		echo $this->get_field_id( 'limit' ); ?>" name="<?php
		echo $this->get_field_name( 'limit' ); ?>" type="text" value="<?php
		echo esc_attr($limit); ?>" /><span class="howto"> (Leave blank or 0 to display all)</span></p>


<p><label for="<?php
		echo $this->get_field_id( 'category' ); ?>">Category</label>
     :
       <br />
        <select id="<?php
		echo $this->get_field_id( 'category' ); ?>" name="<?php
		echo $this->get_field_name( 'category' ); ?>">
          <option value="0" <?php
		selected($category, '0' ); ?>>All</option>

  <?php
		$terms = get_terms( "lshowcase-categories" );
		$count = count($terms);
		if ($count > 0) {
			foreach($terms as $term) {
				echo "<option value='" . $term->slug . "'" . selected($category, $term->slug) . ">" . $term->name . "</option>";
			}
		}

?></select></p>






          <p>
            <label for="<?php
		echo $this->get_field_id( 'activeurl' ); ?>">URL:<br />
            </label>
        <select id="<?php
		echo $this->get_field_id( 'activeurl' ); ?>" name="<?php
		echo $this->get_field_name( 'activeurl' ); ?>">
          <option value="inactive" <?php
		selected($activeurl, 'inactive' ); ?>>Inactive</option>
          <option value="new" <?php
		selected($activeurl, 'new' ); ?>>Open in new window</option>
		<option value="new_nofollow" <?php
		selected($activeurl, 'new_nofollow' ); ?>>Open in new window (nofollow)</option>
          <option value="same" <?php
		selected($activeurl, 'same' ); ?>>Open in same window</option>
        </select></p>



   <p>
     <label for="<?php
		echo $this->get_field_id( 'style' ); ?>">Style:</label>
        <br />
        <select id="<?php
		echo $this->get_field_id( 'style' ); ?>" name="<?php
		echo $this->get_field_name( 'style' ); ?>">

          <?php
		$stylesarray = lshowcase_styles_array();
		foreach($stylesarray as $option => $key) {
?>

          <option value="<?php
			echo $option; ?>" <?php
			selected($style, $option); ?>><?php
			echo $key['description']; ?></option>
          <?php
		} ?>

</select></p>

        <p>Layout:
          <br />
          <select id="<?php
		echo $this->get_field_id( 'interface' ); ?>" name="<?php
		echo $this->get_field_name( 'interface' ); ?>">
          <option value="hcarousel" <?php
		selected($interface, 'hcarousel' ); ?>>Horizontal Carousel</option>
          <option value="grid" <?php
		selected($interface, 'grid' ); ?>>Normal Grid</option>
		<option value="grid18" <?php
		selected($interface, 'grid18' ); ?>>Responsive Grid - 18 Columns</option>
		<option value="grid16" <?php
		selected($interface, 'grid16' ); ?>>Responsive Grid - 16 Columns</option>
		<option value="grid14" <?php
		selected($interface, 'grid14' ); ?>>Responsive Grid - 14 Columns</option>
          <option value="grid12" <?php
		selected($interface, 'grid12' ); ?>>Responsive Grid - 12 Columns</option>
          <option value="grid11" <?php
		selected($interface, 'grid11' ); ?>>Responsive Grid - 11 Columns</option>
          <option value="grid10" <?php
		selected($interface, 'grid10' ); ?>>Responsive Grid - 10 Columns</option>
          <option value="grid9" <?php
		selected($interface, 'grid9' ); ?>>Responsive Grid - 9 Columns</option>
          <option value="grid8" <?php
		selected($interface, 'grid8' ); ?>>Responsive Grid - 8 Columns</option>
          <option value="grid7" <?php
		selected($interface, 'grid7' ); ?>>Responsive Grid - 7 Columns</option>
          <option value="grid6" <?php
		selected($interface, 'grid6' ); ?>>Responsive Grid - 6 Columns</option>
          <option value="grid5" <?php
		selected($interface, 'grid5' ); ?>>Responsive Grid - 5 Columns</option>
          <option value="grid4" <?php
		selected($interface, 'grid4' ); ?>>Responsive Grid - 4 Columns</option>
          <option value="grid3" <?php
		selected($interface, 'grid3' ); ?>>Responsive Grid - 3 Columns</option>
          <option value="grid2" <?php
		selected($interface, 'grid2' ); ?>>Responsive Grid - 2 Columns</option>
          <option value="grid1" <?php
		selected($interface, 'grid1' ); ?>>Responsive Grid - 1 Columns</option>
		 <option <?php selected($interface,'list'); ?> value="list" >List ( w/ title to the right)</option>


</select></p>

       <p>
     <label for="<?php
		echo $this->get_field_id( 'tooltip' ); ?>">Show Tooltip:</label>
        <br />
        <select id="<?php
		echo $this->get_field_id( 'tooltip' ); ?>" name="<?php
		echo $this->get_field_name( 'tooltip' ); ?>">
          <option value="true" <?php
		selected($tooltip, 'true' ); ?>>Yes - Title</option>

 		<option value="true-description" <?php
		selected($tooltip, 'true-description' ); ?>>Yes - Description</option>

          <option value="false" <?php
		selected($tooltip, 'false' ); ?>>No</option>

</select></p>

 <p>
     <label for="<?php
		echo $this->get_field_id( 'description' ); ?>">Show Info:</label>
        <br />
        <select id="<?php
		echo $this->get_field_id( 'description' ); ?>" name="<?php
		echo $this->get_field_name( 'description' ); ?>">
          <option value="true" <?php
		selected($description, 'true' ); ?>>Title Below</option>

 		<option value="true-description" <?php
		selected($description, 'true-description' ); ?>>Description Below</option>

		<option value="true-above" <?php
		selected($description, 'true-above' ); ?>>Title Above</option>

		<option value="true-description-above" <?php
		selected($description, 'true-description-above' ); ?>>Description Above</option>

		<option value="true-title-above-description-below" <?php
		selected($description, 'true-title-above-description-below' ); ?>>Title Above & Description Below</option>

		<option value="true-title-description-above" <?php
		selected($description, 'true-title-description-below' ); ?>>Title & Description Below</option>

		  <option <?php selected($description,'true-title-hover'); ?> value="true-title-hover">Show Title on Hover</option>
          <option <?php selected($description,'true-description-hover'); ?> value="true-description-hover">Show Description on Hover</option>


          <option value="false" <?php
		selected($description, 'false' ); ?>>No</option>



</select></p>

<p>

	<label for="<?php
		echo $this->get_field_id( 'padding' ); ?>"><?php echo __('Global Image Padding','lshowcase'); ?></label><br>
	 <input id="<?php
		echo $this->get_field_id( 'padding' ); ?>" name="<?php
		echo $this->get_field_name( 'padding' ); ?>" type="range" min="0" max="50" oninput="lshowcaseShowVal()" value="<?php echo $padding; ?>">
	<span class="lshowcase_padding_value"><?php echo $padding; ?>%</span>

	</p>

	<script type="text/javascript">
	function lshowcaseShowVal(field){
		jQuery(".lshowcase_padding_value").each(function(){
			var value = jQuery(this).prev().val();
			jQuery(this).html(value+'%');
		});
	}
	</script>

<p>
     <label for="<?php
		echo $this->get_field_id( 'filter' ); ?>">Show Filter Menu:</label>
        <br />
        <select id="<?php
		echo $this->get_field_id( 'filter' ); ?>" name="<?php
		echo $this->get_field_name( 'filter' ); ?>">
          <option value="false" <?php
		selected($filter, 'false' ); ?>>No</option>

 		 <option value="hide" <?php
		selected($filter, 'hide' ); ?>>Hide Filter</option>
		 <option value="enhance" <?php
		selected($filter, 'enhance' ); ?>>Enhance Filter</option>

</select></p>
 <p><label for="<?php
		echo $this->get_field_id( 'class' ); ?>">CSS Class:</label>
        <input class="widefat" id="<?php
		echo $this->get_field_id( 'class' ); ?>" name="<?php
		echo $this->get_field_name( 'class' ); ?>" type="text" value="<?php
		echo esc_attr($class); ?>" /></p>

        <?php
	}
}

add_action( 'widgets_init', 'register_lshowcase_widget' );
/**
 * Register widget
 *
 * This functions is attached to the 'widgets_init' action hook.
 */

function register_lshowcase_widget()
{
	register_widget( 'Lshowcase_Widget' );
}
?>