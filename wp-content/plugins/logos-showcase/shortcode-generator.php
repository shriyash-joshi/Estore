<?php
// add shortcode generator page
function lshowcase_shortcode_page_add()
{
	$menu_slug = 'edit.php?post_type=lshowcase';
	$submenu_page_title = 'Shortcode Generator';
	$submenu_title = 'Shortcode Generator';
	$capability = 'manage_options';
	$submenu_slug = 'lshowcase_shortcode';
	$submenu_function = 'lshowcase_shortcode_page';
	$defaultp = add_submenu_page($menu_slug, $submenu_page_title, $submenu_title, $capability, $submenu_slug, $submenu_function);
	add_action($defaultp, 'lshowcase_enqueue_admin_js' );
}

function lshowcase_enqueue_admin_js()
{
	wp_deregister_script( 'lshowcaseadmin' );
	wp_register_script( 'lshowcaseadmin', plugins_url( '/js/shortcode-builder.js', __FILE__) , array(
		'jquery'
	));
	wp_enqueue_script( 'lshowcaseadmin' );


wp_deregister_style( 'lshowcase-admin' );
  wp_register_style( 'lshowcase-admin', plugins_url( '/css/admin.css', __FILE__) , array() , false, false);
  wp_enqueue_style( 'lshowcase-admin' );


	wp_deregister_style( 'lshowcase-main-style' );
	wp_register_style( 'lshowcase-main-style', plugins_url( '/css/styles.css', __FILE__) , array() , false, false);
	wp_enqueue_style( 'lshowcase-main-style' );



    wp_deregister_style( 'lshowcase-fontawesome' );
  wp_register_style( 'lshowcase-fontawesome', plugins_url( '/css/font-awesome/css/fontawesome-all.css', __FILE__) , array() , false, false);
  wp_enqueue_style( 'lshowcase-fontawesome' );

	// in javascript, object properties are accessed as ajax_object.ajax_url, ajax_object.we_value

	wp_localize_script( 'lshowcaseadmin', 'ajax_object', array(
		'ajax_url' => admin_url( 'admin-ajax.php' )
	));
	wp_deregister_script( 'lshowcase-bxslider' );
	wp_register_script( 'lshowcase-bxslider', plugins_url( '/bxslider/jquery.bxslider.min.js', __FILE__) , array(
		'jquery'
	) , false, false);
	wp_enqueue_script( 'lshowcase-bxslider' );
	wp_deregister_style( 'lshowcase-bxslider-style' );
	wp_register_style( 'lshowcase-bxslider-style', plugins_url( '/bxslider/jquery.bxslider.css', __FILE__) , array() , false, false);
	wp_enqueue_style( 'lshowcase-bxslider-style' );
	wp_deregister_script( 'ls-jquery-ui' );
	wp_register_script( 'ls-jquery-ui', plugins_url( '/js/jquery-ui.min.js', __FILE__) , array(
		'jquery'
	) , false, false);
	wp_enqueue_script( 'ls-jquery-ui' );
	wp_deregister_script( 'lshowcase-tooltip' );
	wp_register_script( 'lshowcase-tooltip', plugins_url( '/js/tooltip.js', __FILE__) , array(
		'ls-jquery-ui'
	) , false, false);
	wp_enqueue_script( 'lshowcase-tooltip' );

	wp_deregister_script( 'lshowcase-jgrayscale' );
	wp_register_script( 'lshowcase-jgrayscale', plugins_url( '/js/grayscale.js', __FILE__) , array(
		'jquery'
	) , false, false);
	wp_enqueue_script( 'lshowcase-jgrayscale' );

  wp_deregister_script( 'lshowcase-hide-filter' );
  wp_register_script( 'lshowcase-hide-filter', plugins_url( '/js/filter.js', __FILE__) , array('jquery','jquery-ui-core','jquery-effects-core'), false, false);
  wp_enqueue_script( 'lshowcase-hide-filter' );

  wp_deregister_script( 'lshowcase-enhance-filter' );
  wp_register_script( 'lshowcase-enhance-filter', plugins_url( '/js/filter-enhance.js', __FILE__) , array(
    'jquery'
  ) , false, false);
  wp_enqueue_script( 'lshowcase-enhance-filter' );


  wp_deregister_script( 'lshowcase-isotope' );
  wp_register_script( 'lshowcase-isotope', plugins_url( '/js/isotope.pkgd.min.js', __FILE__ ),array('jquery',),false,false);
  wp_enqueue_script( 'lshowcase-isotope' );

  wp_deregister_script( 'lshowcase-cells-isotope' );
  wp_register_script( 'lshowcase-cells-isotope', plugins_url( '/js/cells-by-row.js', __FILE__ ),array('jquery','lshowcase-isotope'),false,false);
  wp_enqueue_script( 'lshowcase-cells-isotope' );

  wp_deregister_script( 'lshowcase-img-isotope' );
  wp_register_script( 'lshowcase-img-isotope', plugins_url( '/js/imagesloaded.pkgd.min.js', __FILE__ ),array('lshowcase-isotope'),false,false);
  wp_enqueue_script( 'lshowcase-img-isotope' );

  wp_deregister_script( 'lshowcase-isotope-filter' );
  wp_register_script( 'lshowcase-isotope-filter', plugins_url( '/js/filter-isotope.js', __FILE__ ),array('jquery','lshowcase-isotope','lshowcase-isotope'),false,false);
  wp_enqueue_script( 'lshowcase-isotope-filter' );

}

add_action( 'wp_ajax_lshowcase', 'lshowcase_run_preview' );
add_action('wp_ajax_lshowcaseupdate','lshowcase_update_data');

function lshowcase_run_preview()
{
	$orderby = $_POST['porder'];
	$category = $_POST['pcategory'];
  $tag = $_POST['ptag'];
	$activeurl = $_POST['purl'];
	$style = $_POST['pstyle'];
	$interface = $_POST['pinterface'];
	$tooltip = $_POST['ptooltip'];
	$description = $_POST['pdescription'];
	$limit = $_POST['plimit'];
	$slidersettings = "";
	$img = $_POST['pimg'];
  $class = $_POST['pclass'];
  $filter = $_POST['pfilter'];
  $padding = $_POST['ppadding'];
  $ids = '';

	$html = build_lshowcase($orderby, $category, $tag, $activeurl, $style, $interface, $tooltip, $description, $limit, $slidersettings, $img, $ids, $filter, $class, 'OR', false, $padding, $margin = 0);
	echo $html;
	die(); // this is required to return a proper result
}

function lshowcase_update_data(){
  $data = json_decode(stripslashes($_POST['data']),true);
  $mod = 0;


  foreach ($data as $key => $values) {
      foreach ($values as $meta => $value) {
          update_post_meta( $key, $meta, $value);
      }
      $mod++;
  }


  echo $mod;
  die();

}

function lshowcase_shortcode_page()
{
	settings_fields( 'lshowcase-plugin-settings' );
	$options = get_option( 'lshowcase-settings' );

  $s_settings = get_option( 'lshowcase_shortcode_settings', '' );
  $selectedv = array();

  if($s_settings!='') {
    foreach ($s_settings as $key => $value) {
      if(!isset($selectedv[$value['name']])) {
        $selectedv[$value['name']] = $value['value'];
      } else {
        $selectedv[$value['name']] = $selectedv[$value['name']].'|'.$value['value'];
      }

    }
  }

	?>

<h1>Shortcode Generator</h1>


    <table cellpadding="10" cellspacing="10">
      <tr><td valign="top">
    <div class="postbox" style="width:300px;">
    <form id="shortcode_generator" style="padding:20px;">

<p>
        <label for="orderby">Order By:</label>
        <select id="orderby" name="orderby" onChange="lshowcaseshortcodegenerate()">

          <?php
            $current_order = isset($selectedv['orderby']) ? $selectedv['orderby'] : null;
          ?>


            <option value="none" <?php selected($current_order,'none'); ?>>Default (Order Field)</option>
            <option value="title" <?php selected($current_order,'title'); ?>>Title</option>
            <option value="ID" <?php selected($current_order,'ID'); ?>>ID</option>
            <option value="date" <?php selected($current_order,'date'); ?>>Date</option>
            <option value="modified" <?php selected($current_order,'modified'); ?>>Modified</option>
            <option value="rand" <?php selected($current_order,'rand'); ?>>Random</option>
        </select></p>



 	 <p><label for="limit">Number of Images to display:</label>

 <?php $current_limit = isset($selectedv['limit']) ? $selectedv['limit'] : '0'; ?>

    <input size="3" id="limit" name="limit" type="text" value="<?php echo $current_limit; ?>" onChange="lshowcaseshortcodegenerate()" /><span class="howto"> (Leave blank or 0 to display all)</span></p>

  <?php $multiple = isset($selectedv['multiple']) ? 'checked' : ''; ?>

  <?php

$options = get_option( 'lshowcase-settings' );

  $catlabel = isset($options['lshowcase_label_categories']) ? $options['lshowcase_label_categories'] : __('Categories','lshowcase');
  $taglabel = isset($options['lshowcase_label_tags']) ? $options['lshowcase_label_tags'] : __('Tags','lshowcase');


?>

     Multiple <?php echo $catlabel; ?> Selection <input name="multiple" type="checkbox" id="multiple" onChange="lshowcaseshortcodegenerate()" value="multiple" <?php echo $multiple; ?>>

<span id="multiplemsg" class="howto"></span>



<p><label for="category"><?php echo $catlabel; ?></label>:

<?php

          $current_category = isset($selectedv['category']) ? $selectedv['category'] : null;
          if($current_category != null) {
            $current_category = explode('|',$current_category);

          }

          $ismultiple = isset($selectedv['multiple']) ? 'multiple' : '';

          ?>


        <select id="category" name="category" onChange="lshowcaseshortcodegenerate()" <?php echo $ismultiple ; ?>>
          <option <?php if(is_array($current_category) && in_array("0", $current_category)) { echo "selected"; } ?> value="0" >All</option>

  <?php


	$terms = get_terms( "lshowcase-categories" );
	$count = count($terms);
	if ($count > 0) {
		foreach($terms as $term) {
      $select_echo = '';
      if(is_array($current_category) && in_array($term->slug, $current_category)) { $select_echo = "selected"; }
			echo "<option ".$select_echo." value='" . $term->slug . "' >" . $term->name . "</option>";
		}
	}

?></select></p>


<p><label for="tag"><?php echo $taglabel; ?></label>:

<?php

          $current_tag = isset($selectedv['tag']) ? $selectedv['tag'] : null;
          if($current_tag != null) {
            $current_tag = explode('|',$current_tag);

          }

          //$ismultiple = isset($selectedv['multiple']) ? 'multiple' : '';

          ?>


        <select id="tag" name="tag" onChange="lshowcaseshortcodegenerate()" <?php //echo $ismultiple ; ?>>
          <option <?php if(is_array($current_tag) && in_array("0", $current_tag)) { echo "selected"; } ?> value="0" >All</option>

  <?php


  $terms = get_terms( "lshowcase-tags" );
  $count = count($terms);
  if ($count > 0) {
    foreach($terms as $term) {
      $select_echo = '';
      if(is_array($current_tag) && in_array($term->slug, $current_tag)) { $select_echo = "selected"; }
      echo "<option ".$select_echo." value='" . $term->slug . "' >" . $term->name . "</option>";
    }
  }

?></select></p>


 <p>
            <label for="activeurl">URL:
            </label>
        <select id="activeurl" name="activeurl" onChange="lshowcaseshortcodegenerate()">

          <?php
            $acturl = isset($selectedv['singleurl']) ? $selectedv['singleurl'] : 'new';
            ?>


          <option value="inactive" <?php selected($acturl,'inactive'); ?>>Inactive</option>
          <option value="new" <?php selected($acturl,'new'); ?>>Open in new window</option>
          <option value="new_nofollow" <?php selected($acturl,'new_nofollow'); ?>>Open in new window (nofollow)</option>
          <option value="same" <?php selected($acturl,'same'); ?>>Open in same window</option>
        </select></p>



   <p>
     <label for="style">Style:</label>

        <select id="style" name="style" onChange="lshowcaseshortcodegenerate()">

          <?php
            $style = isset($selectedv['style']) ? $selectedv['style'] : 'normal';
            ?>


          <?php
	$stylesarray = lshowcase_styles_array();
	foreach($stylesarray as $option => $key) {
?>

          <option  <?php selected($style,$option); ?> value="<?php echo $option; ?>"><?php echo $key['description']; ?></option>
          <?php
	} ?>
		</select></p>

		<p>
		     <label for="tooltip">Show Tooltip:</label>

		        <select id="tooltip" name="tooltip" onChange="lshowcaseshortcodegenerate()">

              <?php
            $tooltip = isset($selectedv['tooltip']) ? $selectedv['tooltip'] : 'false';
            ?>

		          <option <?php selected($tooltip,'false'); ?> value="false">No</option>
		          <option <?php selected($tooltip,'true'); ?> value="true">Yes - Show Title</option>
		          <option <?php selected($tooltip,'true-description'); ?> value="true-description">Yes - Show Description</option>

		</select>

		</p>

		<p>
		     <label for="description">Show Info:</label>

		        <select id="description" name="description" onChange="lshowcaseshortcodegenerate()">

               <?php
            $si = isset($selectedv['description']) ? $selectedv['description'] : 'false';
            ?>

		          <option <?php selected($si,'false'); ?> value="false">No</option>
		          <option <?php selected($si,'true'); ?>  value="true">Show Title Below</option>
		          <option <?php selected($si,'true-description'); ?>  value="true-description">Show Description Below</option>
               <option <?php selected($si,'true-above'); ?>  value="true-above">Show Title Above</option>
                <option <?php selected($si,'true-description-above'); ?> value="true-description-above">Show Description Above</option>
                 <option <?php selected($si,'true-title-above-description-below'); ?> value="true-title-above-description-below">Show Title Above Description Below</option>
		            <option <?php selected($si,'true-title-description-below'); ?> value="true-title-description-below">Show Title & Description Below</option>
                <option <?php selected($si,'true-title-hover'); ?> value="true-title-hover">Show Title on Hover</option>
                <option <?php selected($si,'true-description-hover'); ?> value="true-description-hover">Show Description on Hover</option>

		</select>

		</p>




        <p>Layout:

           <?php
            $layout = isset($selectedv['interface']) ? $selectedv['interface'] : 'grid';
            ?>

          <select id="interface" name="interface" onChange="lshowcaseshortcodegenerate()">
          <option <?php selected($layout,'grid'); ?> value="grid" selected>Normal Grid</option>
          <option <?php selected($layout,'hcarousel'); ?> value="hcarousel" >Horizontal Carousel</option>
          <option <?php selected($layout,'grid18'); ?> value="grid18" >Responsive Grid - 18 Columns</option>
          <option <?php selected($layout,'grid16'); ?> value="grid16" >Responsive Grid - 16 Columns</option>
          <option <?php selected($layout,'grid14'); ?> value="grid14" >Responsive Grid - 14 Columns</option>
          <option <?php selected($layout,'grid12'); ?> value="grid12" >Responsive Grid - 12 Columns</option>
          <option <?php selected($layout,'grid11'); ?> value="grid11" >Responsive Grid - 11 Columns</option>
          <option <?php selected($layout,'grid10'); ?> value="grid10" >Responsive Grid - 10 Columns</option>
          <option <?php selected($layout,'grid9'); ?> value="grid9" >Responsive Grid - 9 Columns</option>
          <option <?php selected($layout,'grid8'); ?> value="grid8" >Responsive Grid - 8 Columns</option>
          <option <?php selected($layout,'grid7'); ?> value="grid7" >Responsive Grid - 7 Columns</option>
          <option <?php selected($layout,'grid6'); ?> value="grid6" >Responsive Grid - 6 Columns</option>
          <option <?php selected($layout,'grid5'); ?> value="grid5" >Responsive Grid - 5 Columns</option>
          <option <?php selected($layout,'grid4'); ?> value="grid4" >Responsive Grid - 4 Columns</option>
          <option <?php selected($layout,'grid3'); ?> value="grid3" >Responsive Grid - 3 Columns</option>
          <option <?php selected($layout,'grid2'); ?> value="grid2" >Responsive Grid - 2 Columns</option>
          <option <?php selected($layout,'grid1'); ?> value="grid1" >Responsive Grid - 1 Columns</option>
          <option <?php selected($layout,'list'); ?> value="list" >List ( w/ title to the right)</option>

</select></p>

<div id="ls_padding_option">

  <?php

  $globalpadding = isset($selectedv['lshowcase_padding_option']) ? $selectedv['lshowcase_padding_option'] : '10';


  ?>
<p>
  <label for="padding"><?php echo __('Image Global Padding','lshowcase'); ?></label>
  <!-- <input id="padding" style="width:50px;" onChange="lshowcasepaddinggenerate()" value="<?php echo $globalpadding; ?>" size="20" name="padding" type="number" min="0" max="100"> -->

  <input oninput="lshowcasepaddinggenerate()" id="padding" type="range" min="0" max="50" value="<?php echo $globalpadding; ?>">
  <span style="display:inline-block" id="paddingvalue"><?php echo $globalpadding; ?>%</span>
  </p>

</div>


<div id="ls_filter_option">

   <label for="filter"><?php echo sprintf(__('Show %s Filter Menu','lshowcase'),$catlabel); ?></label>

<select id="filter" name="filter" onChange="lshowcaseshortcodegenerate()">

     <?php
            $filter = isset($selectedv['filter']) ? $selectedv['filter'] : 'false';
            ?>

              <option <?php selected($filter,'false'); ?> value="false">No</option>
              <option <?php selected($filter,'hide'); ?> value="hide">Hide Filter</option>
               <option <?php selected($filter,'isotope'); ?> value="isotope">Hide (Isotope Script)</option>
              <option <?php selected($filter,'enhance'); ?> value="enhance">Enhance Filter</option>


    </select>


</div>

<div id="ls_carousel_type">
	<p id="ls_carousel_settings_option" style="display:none;">
		<label for="">Carousel Settings: </label>
     <?php
    $carouselset = isset($selectedv['use_defaults']) ? $selectedv['use_defaults'] : '1';
            ?>
		<input name="use_defaults" id="use_defaults" type="radio" value="1" <?php
  checked($carouselset, '1' ); ?> onclick="hidecustomsettings();" />
              Default
                <input <?php
  checked($carouselset, '0' ); ?> name="use_defaults" id="use_defaults" type="radio" value="0" onclick="showcustomsettings();" />
              Custom
	</p>
	<div id="ls_carousel_settings" style="display:none; background:#FFF; padding:5px;">

<table width="100%">
  <tr>
    <?php
    $autoscroll = isset($selectedv['lshowcase_carousel_autoscroll']) ? $selectedv['lshowcase_carousel_autoscroll'] : $options['lshowcase_carousel_autoscroll'];
            ?>
    <td nowrap >Auto Scroll</td>
    <td><select name="lshowcase_carousel_autoscroll" onChange="lshowcaseshortcodegenerate()">
      <option value="true"  <?php
	selected($autoscroll, 'true' ); ?>>Yes - With Pause</option>
      <option value="ticker"  <?php
	selected($autoscroll, 'ticker' ); ?>>Yes - Non Stop</option>
      <option value="false" <?php
	selected($autoscroll, 'false' ); ?>>No</option>
    </select></td>


  </table>
  <table width="100%" id="lst_pause_time">

  <tr>
     <?php
    $pausetime = isset($selectedv['lshowcase_carousel_pause']) ? $selectedv['lshowcase_carousel_pause'] : $options['lshowcase_carousel_pause'];
            ?>
    <td nowrap >Pause Time</td>
    <td><input type="text" name="lshowcase_carousel_pause" value="<?php
	echo $pausetime; ?>" onChange="lshowcaseshortcodegenerate()" size="10" /></td>
  </tr><tr><td colspan="2"><span class="howto">The amount of time (in ms) between each auto transition</span></td>
  </tr>
  </table>
  <table width="100%" id="lst_pause_hover">
  <tr>
     <?php
    $autohover = isset($selectedv['lshowcase_carousel_autohover']) ? $selectedv['lshowcase_carousel_autohover'] : $options['lshowcase_carousel_autohover'];
            ?>
    <td nowrap >Pause on Hover</td>
    <td><select name="lshowcase_carousel_autohover" onChange="lshowcaseshortcodegenerate()">
      <option value="true" <?php
	selected($autohover, 'true' ); ?>>Yes</option>
      <option value="false" <?php
	selected($autohover, 'false' ); ?>>No</option>
    </select></td>
  </tr><tr><td colspan="2"><span class="howto">Auto scroll will pause when mouse hovers over slider</span></td>
  </tr>

  </table>
  <table width="100%" id="lst_auto_controls">

  <tr>

    <?php
    $acontrols = isset($selectedv['lshowcase_carousel_autocontrols']) ? $selectedv['lshowcase_carousel_autocontrols'] : $options['lshowcase_carousel_autocontrols'];
            ?>

    <td nowrap >Auto Controls</td>
    <td><select name="lshowcase_carousel_autocontrols" onChange="lshowcaseshortcodegenerate()">
      <option value="true" <?php
	selected($acontrols, 'true' ); ?>>Yes</option>
      <option value="false" <?php
	selected($acontrols, 'false' ); ?>>No</option>
    </select></td>
  </tr><tr><td colspan="2"><span class="howto">If active, "Start" / "Stop" controls will be added</span></td>
  </tr>

  </table>
  <table width="100%">

  <tr>
    <?php
            $tspeed = isset($selectedv['lshowcase_carousel_speed']) ? $selectedv['lshowcase_carousel_speed'] : $options['lshowcase_carousel_speed'];
            ?>
    <td nowrap >Transition Speed:</td>
    <td><input type="text" name="lshowcase_carousel_speed" value="<?php
	echo $tspeed; ?>" onChange="lshowcaseshortcodegenerate()" size="10" /></td>
  </tr><tr><td colspan="2"><span class="howto">Slide transition duration (in ms - intenger) </span></td>
  </tr>
  <tr>
     <?php
            $imargin = isset($selectedv['lshowcase_carousel_slideMargin']) ? $selectedv['lshowcase_carousel_slideMargin'] : $options['lshowcase_carousel_slideMargin'];
            ?>
    <td nowrap >Image Margin:</td>
    <td><input type="text" size="10" name="lshowcase_carousel_slideMargin" value="<?php
	echo $imargin; ?>" onChange="lshowcaseshortcodegenerate()" /></td>
  </tr><tr><td colspan="2"><span class="howto">Margin between each image (intenger)</span></td>
  </tr>
  </table>
  <table width="100%" id="lst_carousel_common_settings">
  <tr>
    <?php
            $infinite = isset($selectedv['lshowcase_carousel_infiniteLoop']) ? $selectedv['lshowcase_carousel_infiniteLoop'] : $options['lshowcase_carousel_infiniteLoop'];
            ?>
    <td nowrap >Infinite Loop:</td>
    <td><select name="lshowcase_carousel_infiniteLoop" onChange="lshowcaseshortcodegenerate()">
      <option value="true" <?php
	selected($infinite , 'true' ); ?>>Yes</option>
      <option value="false" <?php
	selected($infinite , 'false' ); ?>>No</option>
    </select></td>

  <tr>
    <?php
            $showpager = isset($selectedv['lshowcase_carousel_pager']) ? $selectedv['lshowcase_carousel_pager'] : $options['lshowcase_carousel_pager'];
            ?>
    <td nowrap >Show Pager:</td>
    <td><select name="lshowcase_carousel_pager" onChange="lshowcaseshortcodegenerate()">
      <option value="true" <?php
	selected($showpager, 'true' ); ?>>Yes</option>
      <option value="false" <?php
	selected($showpager, 'false' ); ?>>No</option>
    </select></td>
  </tr><tr><td colspan="2"><span class="howto">If Active, a pager will be added.</span></td>
  </tr>
  <tr>

     <?php
            $showcontrols = isset($selectedv['lshowcase_carousel_controls']) ? $selectedv['lshowcase_carousel_controls'] : $options['lshowcase_carousel_controls'];
            ?>

    <td nowrap >Show Controls:</td>
    <td><select name="lshowcase_carousel_controls" onChange="lshowcaseshortcodegenerate()">
      <option value="true" <?php
	selected($showcontrols, 'true' ); ?>>Yes</option>
      <option value="false" <?php
	selected($showcontrols, 'false' ); ?>>No</option>
    </select></td>
  </tr><tr><td colspan="2"><span class="howto">If Active, "Next" / "Prev" image controls will be added.</span></td>
  </tr>

<?php
if(isset($options['lshowcase_carousel_mode']) && $options['lshowcase_carousel_mode'] == 'fade') {
	?>
<tr>
    <td colspan="2" ><span style="color:red;">Atention: the transition mode in the settings is set to 'fade'. The settings below will be ignored and only 1 slide will display at a time.<span></td>

  </tr>
	<?php
}

?>

  <tr>

    <?php
            $mins = isset($selectedv['lshowcase_carousel_minSlides']) ? $selectedv['lshowcase_carousel_minSlides'] : $options['lshowcase_carousel_minSlides'];
            ?>

    <td nowrap >Minimum Slides:</td>
    <td><input size="10" type="text" name="lshowcase_carousel_minSlides" value="<?php
	echo $mins; ?>" onChange="lshowcaseshortcodegenerate()" /></td>
  </tr><tr><td colspan="2"><span class="howto">The minimum number of slides to be shown.</span></td>
  </tr>
  <tr>
    <?php
            $ms = isset($selectedv['lshowcase_carousel_maxSlides']) ? $selectedv['lshowcase_carousel_maxSlides'] : $options['lshowcase_carousel_maxSlides'];
            ?>
    <td nowrap >Maximum Slides:</td>
    <td><input size="10" type="text" name="lshowcase_carousel_maxSlides" value="<?php
	echo $ms; ?>" onChange="lshowcaseshortcodegenerate()" /></td>
  </tr><tr><td colspan="2"><span class="howto">The maximum number of slides to be shown. (place 0 to let the script calculate the maximum number of slides that fit the viewport)</span></td>
  </tr>
  <tr>
     <?php
            $stm = isset($selectedv['lshowcase_carousel_moveSlides']) ? $selectedv['lshowcase_carousel_moveSlides'] : $options['lshowcase_carousel_moveSlides'];
            ?>
    <td nowrap >Slides to move:</td>
    <td><input size="10" type="text" name="lshowcase_carousel_moveSlides" value="<?php
	echo $stm; ?>" onChange="lshowcaseshortcodegenerate()" /></td>
  </tr><tr><td colspan="2"><span class="howto">The number of slides to move on transition.  If zero, the number of fully-visible slides will be used.</span></td>
</tr>
</table>



	</div>

	<table width="100%" style="border-top:1px dashed #CCC; margin-top:20px; padding:10px;">

     <?php
            $imgsize = isset($selectedv['lshowcase_image_size_overide']) ? $selectedv['lshowcase_image_size_overide'] : '';
            ?>
<tr>
    <td nowrap >Image Size Override:</td>
    <td><input size="10" type="text" name="lshowcase_image_size_overide" value="<?php echo $imgsize; ?>" size="10" onChange="lshowcaseshortcodegenerate()" /></td>
  </tr><tr><td colspan="2"><span class="howto">Leave blank to use default values.
In case you want to override the default image size settings, use this field to put the width and height values in the following format: width,height
ex. 100,100.
Smallest value will prevail if images don't have exactly this size. Images might be scaled. (In the Responsive Grid Layout this option won't take effect)</span></td>
</tr>

</table>

<table width="100%" style="border-top:1px dashed #CCC; margin-top:20px; padding:10px;">

 <?php
            $cssclass = isset($selectedv['lshowcase_wrap_class']) ? $selectedv['lshowcase_wrap_class'] : '';
            ?>

<tr>
    <td nowrap >CSS Class:</td>
    <td><input size="10" type="text" name="lshowcase_wrap_class" id="lshowcase_wrap_class" value="<?php echo $cssclass; ?>" size="10" onChange="lshowcaseshortcodegenerate()" /></td>
  </tr><tr><td colspan="2"><span class="howto">Set a custom css class for the this layout wrapping div</span></td>
</tr>




</table>

</div>

<input type="hidden" id="current_shortcode" value="" />


</form>
    </div>
    </td><td valign="top">

     <a class="ls-remember button-primary" onclick="lshowcase_save_shortcode_settings();"><?php echo __('Remember these settings','lshowcase'); ?></a>
        <span class="lshowcase_message_area"></span>



    <h3>Shortcode</h3>
    <span class="howto">
    Use this shortcode to display the list of logos in your posts or pages! Just copy this piece of text and place it where you want it to display.
    <br> You can use the shortcode <strong>[show-logos]</strong> without parameters to use the latest saved settings.
    </span>
    <div id="shortcode_div">
       <textarea id="shortcode" style="width:100%; height:55px;"></textarea>
    </div>

    <div style="display:none;">
    <h3>PHP Function</h3>
    <span class="howto">Use this PHP function to display the list of logos directly in your theme files!</span>
    <div id="phpcode_div">

       <textarea id="phpcode" style="width:100%; height:55px;"></textarea>

    </div> </div>

    <h3> Preview</h3>

    <div id="lseditsize">
      <div class="lsdivcurrentlyediting"><span><?php echo __('Edit size for: '); ?></span><span class="lscurrentlyediting"></span></div>
      <input id="lssizerange" type="range" min="1" max="100" value="50"><span class="lscurrentsize"></span>
      <input id="lssizechanges" type="hidden" value="">

      <div class="lssavecancel">
        <div id="lssizecancel"><?php echo __('Cancel','lshowcase'); ?></div>
        <div id="lssizesave"><?php echo __('Save All Changes','lshowcase'); ?></div>
      </div>
      <div id="lssizeclose"><span class="dashicons dashicons-dismiss"></span></div>
    </div>
	<div id="preview"></div>
  <div id="hcarouselhelp"><i class="fa fa-exclamation-triangle"></i> The carousel layout uses Javascript/jQuery. If the carousel does not display when using the shortcode there is most likely a javascript error in your page code that is preventing the carousel functions from initiating properly. Usually it's a conflict with other plugins. <br> Please <a href="http://cmoreira.net/logos-showcase/carousel-issues/" target="_blank">visit this page for information on how to solve possible issues.</a></div>

     <a class="ls-remember button-primary" onclick="lshowcase_save_shortcode_settings();"><?php echo __('Remember these settings','lshowcase'); ?></a>
        <span class="lshowcase_message_area"></span>
        <span class="howto"><?php echo __('Click here so the current settings are remembered for the next time you visit the shortcode generator page.','lshowcase'); ?></span>

      </td></tr></table>




<?php
	$options = get_option( 'lshowcase-settings' );
	$mode = isset($options['lshowcase_carousel_mode']) ? "'".$options['lshowcase_carousel_mode']."'" : "'horizontal'";
	$slidewidth = $options['lshowcase_thumb_width'];

	$autoscroll = $options['lshowcase_carousel_autoscroll'];
	$pausetime = $options['lshowcase_carousel_pause'];
	$autohover = $options['lshowcase_carousel_autohover'];
	$pager = $options['lshowcase_carousel_pager'];
	$tickerhover = $autohover;
	$ticker = 'false';
	$usecss = 'true';
	$auto = 'true';

	if ($autoscroll == 'false') {
		$auto = 'false';
	}

	if ($autoscroll=='ticker') {
		$ticker = 'true';
		$tickerhover = $autohover;
		$autoscroll = 'true';
		$pager = 'false';
		$auto = 'false';

		if ($tickerhover=='true') {
			$usecss = 'false';
		}
	}

	$autocontrols = $options['lshowcase_carousel_autocontrols'];
	$speed = $options['lshowcase_carousel_speed'];
	$slidemargin = $options['lshowcase_carousel_slideMargin'];
	$loop = $options['lshowcase_carousel_infiniteLoop'];
	$controls = $options['lshowcase_carousel_controls'];
	$minslides = $options['lshowcase_carousel_minSlides'];
	$maxslides = $options['lshowcase_carousel_maxSlides'];
	$moveslides = $options['lshowcase_carousel_moveSlides'];



?>
<script type="text/javascript">



	function checkslider()
	{


		 var layout = document.getElementById( 'interface' ).value;



		if(document.getElementsByName('use_defaults')[1].checked) {


			var slidewidth = <?php echo $slidewidth; ?>;

			var imgwo = document.getElementsByName('lshowcase_image_size_overide')[0].value;
			if (imgwo!="") {

				 var imgwarray = imgwo.split(",");
				 slidewidth = parseInt(imgwarray[0]);
			};

			var autoscroll = document.getElementsByName('lshowcase_carousel_autoscroll')[0].value;
			var pause = parseInt(document.getElementsByName('lshowcase_carousel_pause')[0].value);

			var autohover = (document.getElementsByName('lshowcase_carousel_autohover')[0].value === 'true');
			var pager = (document.getElementsByName('lshowcase_carousel_pager')[0].value === 'true');



			var tickerhover = autohover;
			var ticker = false;
			var usecss = true;
			var auto = true;

			var mode = <?php echo $mode; ?>;

			if (autoscroll == 'false') {
				auto = false;
			}

			if (autoscroll=='ticker') {
				ticker = true;
				tickerhover = autohover;
				pager = false;
				auto = false;

				if (tickerhover==true) {
					usecss = false;
				}
			}


			var autocontrols = (document.getElementsByName('lshowcase_carousel_autocontrols')[0].value === 'true');
			var speed = parseInt(document.getElementsByName('lshowcase_carousel_speed')[0].value);
			var slidemargin = parseInt(document.getElementsByName('lshowcase_carousel_slideMargin')[0].value);
			var infiniteloop = (document.getElementsByName('lshowcase_carousel_infiniteLoop')[0].value === 'true');

			var controls = (document.getElementsByName('lshowcase_carousel_controls')[0].value === 'true');
			var minslides = parseInt(document.getElementsByName('lshowcase_carousel_minSlides')[0].value);
			var maxslides = parseInt(document.getElementsByName('lshowcase_carousel_maxSlides')[0].value);
			var moveslides = parseInt(document.getElementsByName('lshowcase_carousel_moveSlides')[0].value);

		}


		else {


			 var mode = <?php echo $mode; ?>;
			 var slidewidth = <?php echo $slidewidth; ?>;
			 var auto = <?php echo $auto; ?>;
			 var pause = <?php echo $pausetime; ?>;
			 var autohover = <?php echo $autohover; ?>;
			 var ticker = <?php echo $ticker; ?>;
			 var tickerhover = <?php echo $tickerhover; ?>;
			 var usecss = <?php echo $usecss; ?>;
			 var autocontrols = <?php echo $autocontrols; ?>;
			 var speed = <?php echo $speed; ?>;
			 var slidemargin = <?php echo $slidemargin; ?>;
			 var infiniteloop = <?php echo $loop; ?> ;
			 var pager = <?php echo $pager; ?>;
			 var controls = <?php echo $controls; ?>;
			 var minslides = <?php echo $minslides; ?>;
			 var maxslides = <?php echo $maxslides; ?>;
			 var moveslides = <?php echo $moveslides; ?>;
		}

	if(layout=="hcarousel" ) {

		 var sliderDiv = jQuery( '.lshowcase-wrap-carousel-0' );

		 if(maxslides==0) {

			 	var view_width = sliderDiv.parent().width();

			 	if(controls == true ) { view_width = view_width-70; }

				 var slider_real = slidemargin + slidewidth;
				 maxslides = Math.floor(view_width/slider_real);

			 }

		sliderDiv.css({display:'block'});

    <?php

      $next = isset($options['lshowcase_next_arrow']) ? $options['lshowcase_next_arrow'] : '<i class="fas fa-chevron-circle-right"></i>';
      $prev = isset($options['lshowcase_prev_arrow']) ? $options['lshowcase_prev_arrow'] : '<i class="fas fa-chevron-circle-left"></i>';


    ?>

    var next = '<?php echo $next; ?>';
    var prev = '<?php echo $prev; ?>';

		sliderDiv.bxSlider({

			auto: auto,
			pause: pause,
			autoHover: autohover,
			ticker: ticker,
			tickerHover: tickerhover,
			useCSS: usecss,
			autoControls: autocontrols,
			mode: mode,
			speed: speed,
			slideMargin: slidemargin,
			infiniteLoop: infiniteloop,
		    pager: pager,
			controls: controls,
      nextText: next,
        prevText: prev,
		    slideWidth: slidewidth,
		    minSlides: minslides,
		    maxSlides: maxslides,
		    moveSlides: moveslides,
		    autoDirection: 'next',	//change to 'prev' if you want to reverse order
		    onSliderLoad: function(currentIndex){

		    	var sli = jQuery('.lshowcase-logos .bx-wrapper');
		    	var marg = '0 35px';

		    	if(controls == false ) { marg = 'none'; }

		    	sli.css({
				margin: marg
				});

		    	jQuery('.lshowcase-logos').css({
				maxWidth: sli.width()+80
				});

           //to align elements in the center in ticker
           /*
          We change the class, becasue the lshowcase-slide has a float:none!important that breaks
          the ticker code.
           */
             if(ticker) {

              sliderheight = sliderDiv.parent().height();
              console.log(sliderheight);

                      if(sliderheight>0) {
                        sliderDiv.find(".lshowcase-slide")
                        .addClass('lshowcase-ticker-slide')
                        .removeClass('lshowcase-slide')
                        .css("height",sliderheight + 'px');
                      }

             }
		    	}

			});
		}


	}


	function checktooltip() {

	var tooltip = document.getElementById( 'tooltip' ).value;

	if(tooltip=="true" || tooltip=="true-description") {

			jQuery( '.lshowcase-tooltip' ).tooltip({
			content: function () { return jQuery(this).attr('title') },
    close: function( event, ui ) {
          ui.tooltip.hover(
              function () {
                  jQuery(this).stop(true).fadeTo(400, 1);
                  //.fadeIn("slow"); // doesn't work because of stop()
              },
              function () {
                  jQuery(this).fadeOut("400", function(){ jQuery(this).remove(); })
              }
          );
        },
			position: {
			my: "center bottom-20",
			at: "center top",
			using: function( position, feedback ) {
			jQuery( this ).css( position );
			jQuery( "<div>" )
			.addClass( "lsarrow" )
			.addClass( feedback.vertical )
			.addClass( feedback.horizontal )
			.appendTo( this );
			}
			}
			});
		}

	}

	function checkgrayscale() {


		jQuery(".lshowcase-jquery-gray").fadeIn(500);

		// clone image
		jQuery('.lshowcase-jquery-gray').each(function(){
			var el = jQuery(this);
			el.css({"position":"absolute"}).wrap("<div class='img_wrapper' style='display: inline-block'>").clone().addClass('ls_img_grayscale').css({"position":"absolute","z-index":"998","opacity":"0"}).insertBefore(el).queue(function(){
				var el = jQuery(this);
				el.parent().css({"width":this.width,"height":this.height});
				el.dequeue();
			});
			this.src = check_ls_grayscale(this.src);
		});

		// Fade image
		jQuery('.lshowcase-jquery-gray').mouseover(function(){
			jQuery(this).parent().find('img:first').stop().animate({opacity:1}, 1000);
		})
		jQuery('.ls_img_grayscale').mouseout(function(){
			jQuery(this).stop().animate({opacity:0}, 1000);
		});

	}

	// Grayscale effect with canvas method
	function check_ls_grayscale(src){

		var canvas = document.createElement('canvas');
		var ctx = canvas.getContext('2d');
		var imgObj = new Image();
		imgObj.src = src;
		canvas.width = imgObj.width;
		canvas.height = imgObj.height;
		ctx.drawImage(imgObj, 0, 0);
		var imgPixels = ctx.getImageData(0, 0, canvas.width, canvas.height);
		for(var y = 0; y < imgPixels.height; y++){
			for(var x = 0; x < imgPixels.width; x++){
				var i = (y * 4) * imgPixels.width + x * 4;
				var avg = (imgPixels.data[i] + imgPixels.data[i + 1] + imgPixels.data[i + 2]) / 3;
				imgPixels.data[i] = avg;
				imgPixels.data[i + 1] = avg;
				imgPixels.data[i + 2] = avg;
			}
		}
		ctx.putImageData(imgPixels, 0, 0, 0, 0, imgPixels.width, imgPixels.height);
		return canvas.toDataURL();

    }



	 </script>
     <?php
}

add_action( 'wp_ajax_lshowcase_save_shortcode_data', 'lshowcase_save_shortcode_data');

 function lshowcase_save_shortcode_data() {

    if(isset($_POST['options'])) {
      update_option('lshowcase_shortcode_settings', $_POST['options'] );
      update_option('lshowcase_shortcode', $_POST['shortcode'] );
    }

 }

?>