function lshowcaseshortcodegenerate() {
	
	var order = document.getElementById('orderby').value;

	if(document.getElementById('multiple').checked) { 
		document.getElementById('category').setAttribute("multiple","multiple");
		//document.getElementById('tag').setAttribute("multiple","multiple");
		document.getElementById('multiplemsg').innerHTML = "<ul><li>For windows: Hold down the control (ctrl) button to select multiple categories</li><li>For Mac: Hold down the command button to select multiple categories</li></ul>";
	} else {
		document.getElementById('category').removeAttribute("multiple","multiple");
		//document.getElementById('tag').removeAttribute("multiple","multiple");
		document.getElementById('multiplemsg').innerHTML="";
	}

	var category = lshowcase_getSelectValues(document.getElementById('category'));
	var tag = lshowcase_getSelectValues(document.getElementById('tag'));

	var tag_code = '';
	if(tag!='' && tag!='0'){
		tag_code = " tag='"+tag+"'";
	}

	var url = document.getElementById('activeurl').value;
	var style = document.getElementById('style').value;
	var layout = document.getElementById('interface').value;
	var tooltip = document.getElementById('tooltip').value;
	var description = document.getElementById('description').value;
	var limit = document.getElementById('limit').value;
	var padding = document.getElementById('padding').value;

	var filter = document.getElementById('filter').value;

	var img_code = "";
	var img_php = 0;
	var carousel = "";
	var php_carousel = "";
	var class_php = "";
	var class_code = "";

	var css_class = document.getElementsByName('lshowcase_wrap_class')[0].value;


		if(css_class!='') {
			class_code = " class='"+css_class+"'"; 
			class_php = css_class;
		}

	var img = document.getElementsByName('lshowcase_image_size_overide')[0].value;
		
		if (img!="") { 
			img_code = " img='"+img+"'"; 
			img_php = img;
			} 

	if(document.getElementsByName('use_defaults')[1].checked) { 

		var autoscroll = document.getElementsByName('lshowcase_carousel_autoscroll')[0].value;
		var pausetime = document.getElementsByName('lshowcase_carousel_pause')[0].value;
		var pausehover = document.getElementsByName('lshowcase_carousel_autohover')[0].value;
		var autocontrols = document.getElementsByName('lshowcase_carousel_autocontrols')[0].value;
		var speed = document.getElementsByName('lshowcase_carousel_speed')[0].value;
		var margin = document.getElementsByName('lshowcase_carousel_slideMargin')[0].value;
		var loop = document.getElementsByName('lshowcase_carousel_infiniteLoop')[0].value;
		var pager = document.getElementsByName('lshowcase_carousel_pager')[0].value;
		var controls = document.getElementsByName('lshowcase_carousel_controls')[0].value;
		var minslides = document.getElementsByName('lshowcase_carousel_minSlides')[0].value;
		var maxslides = document.getElementsByName('lshowcase_carousel_maxSlides')[0].value;
		var slidesmove = document.getElementsByName('lshowcase_carousel_moveSlides')[0].value;

		//var mode = document.getElementsByName('lshowcase_carousel_mode')[0].value;

		carousel = "carousel='"+autoscroll+","+pausetime+","+pausehover+","+autocontrols+","+speed+","+margin+","+loop+","+pager+","+controls+","+minslides+","+maxslides+","+slidesmove+"'";
		php_carousel = autoscroll+","+pausetime+","+pausehover+","+autocontrols+","+speed+","+margin+","+loop+","+pager+","+controls+","+minslides+","+maxslides+","+slidesmove;
		
		}
	
	var shortcode = document.getElementById('shortcode');
	var shortcodehidden = document.getElementById('current_shortcode');
	var php = document.getElementById('phpcode');
	
	shortcode_string = "[show-logos orderby='"+order+"' category='"+category+"' activeurl='"+url+"' style='"+style+"' interface='"+layout+"' tooltip='"+tooltip+"' description='"+description+"' limit='"+limit+"' padding='"+padding+"' filter='"+filter+"' "+carousel + img_code + class_code + tag_code + "]";
	shortcode.innerHTML = shortcode_string;
	shortcodehidden.value = shortcode_string;
	php.innerHTML = '&lt;?php echo do_shortcode("'+shortcode_string+'"); ?&gt; ';
	
	var preview = document.getElementById('preview');


	
	
var data = {
		action: 'lshowcase',
		porder: order,
		pcategory:category,
		ptag:tag,
		purl:url,
		pstyle:style,
		pinterface:layout,
		ptooltip:tooltip,
		pdescription:description,
		plimit: limit,
		pimg: img_php,
		pfilter: filter,
		pclass: class_php,
		ppadding: padding,
	};


	
jQuery.post(ajax_object.ajax_url, data, function(response) {
		preview.innerHTML=response;
		checkslider();
		checktooltip();
		checkgrayscale();
		buileditbuttons();
	});
	
	if(layout=="hcarousel") {
		var e = document.getElementById('ls_carousel_settings_option');
        e.style.display = 'block';
        var hc = document.getElementById('hcarouselhelp');
        hc.style.display = 'block';

        var filterdiv = document.getElementById('ls_filter_option');
        filterdiv.style.display = 'none';

        var paddingdiv = document.getElementById('ls_padding_option');
        paddingdiv.style.display = 'none';

		}
	else {
		var e = document.getElementById('ls_carousel_settings_option');
        e.style.display = 'none';
        var e = document.getElementById('ls_carousel_settings');
        e.style.display = 'none';
        var hc = document.getElementById('hcarouselhelp');
        hc.style.display = 'none';
        var filterdiv = document.getElementById('ls_filter_option');
        filterdiv.style.display = 'block';
        var paddingdiv = document.getElementById('ls_padding_option');
        paddingdiv.style.display = 'block';
	}


	hidecustomcarouselsettings();
	
}

function buileditbuttons(){

	var editsize = jQuery('#lseditsize');
	var range = editsize.find('#lssizerange');
	var currentlyediting = editsize.find('.lscurrentlyediting');
	var currentsize = editsize.find('.lscurrentsize');
	var cancel = editsize.find('#lssizecancel');
	var sizechanges = editsize.find('#lssizechanges');
	var savechanges = editsize.find('#lssizesave');
	var close = editsize.find('#lssizeclose');

	var changes = {};

	close.on('click',function(){
		editsize.slideUp();
	});

	cancel.on('click',function(){
		jQuery('#preview img').each(function(){
			var orw = jQuery(this).attr('data-original-width');
			if(orw) {
				jQuery(this).animate(
					{ 'max-width':orw }
				);
			}
		});

		editsize.slideUp();
		sizechanges.val('');
		changes = {};
	});

	var requestRunning = false;
	savechanges.on('click',function(){

		if(requestRunning){
			return;
		}

		requestRunning = true;

		var thisdata = {
			action: 'lshowcaseupdate',
			data: sizechanges.val(),
		}

		jQuery.post(ajax_object.ajax_url, thisdata, function(response) {
			
			sizechanges.val('');
			changes = {};

			var tempyes = jQuery('<span class="dashicons dashicons-yes"></span>').fadeIn('slow',function(){
				setTimeout(function(x) {
					savechanges.find('.dashicons').fadeOut('slow');
					requestRunning = false;
				}, 1000); 
			});

			savechanges.append(tempyes);
			console.log('what?');

			jQuery('#preview img').each(function(){
				jQuery(this).attr('data-original-width',jQuery(this).css('max-width'));
			});

		});
	});

	jQuery('#preview img').each(function(){

		var thisimg = jQuery(this);
		var thisid = thisimg.attr('data-entry-id');

		var editbtn = jQuery("<div>Edit Size</div>").addClass('lseditbutton').on('click',function(e){
			
			e.preventDefault();

			editsize.slideDown();

			var thissize = thisimg.css('max-width');

			var wattr = thisimg.attr('data-original-width');
			if (!wattr) {
				thisimg.attr('data-original-width',thissize);
			} 

			if(thissize=='none'){
				thissize = '100%';
			}

			if(thissize){
				
				range.val(parseInt(thissize));
				currentlyediting.html(thisimg.attr('data-original-title'));
				currentsize.html(thissize);

				range.unbind('input').on('input',function(){
					var percent = jQuery(this).val()+'%';
					thisimg.css('max-width',percent)
					currentsize.html(percent);

					changes[thisid] = {'_lspercentage':jQuery(this).val()};

					sizechanges.val(JSON.stringify(changes));

				});

			}
		});

		if (thisimg.parent("a").length) {

			thisimg.parent("a").wrap( "<div class='lsimgeditcontainer'></div>" ).after(editbtn);

		} else {

			thisimg.wrap( "<div class='lsimgeditcontainer'></div>" ).after(editbtn);

		}

	});

}

function lshowcasepaddinggenerate() {
	var padding = jQuery('#padding').val();
	jQuery('#paddingvalue').html(padding+'%');

	sh = jQuery('#shortcode');

	sh.val(sh.val().replace(/padding='([0-9]|[1-9][0-9])'/g, "padding='"+padding+"'"));

	jQuery('.lshowcase-wrap-responsive, .lshowcase-box-normal').each(function(){
		jQuery(this).css('padding',padding+'%');
	});
}


 function hidecustomsettings() {
		var e = document.getElementById('ls_carousel_settings');
        e.style.display = 'none';

        var f = document.getElementById('ls_carousel_settings_option');
        f.style.display = 'none';

        lshowcaseshortcodegenerate();
    }

function showcustomsettings() {
		
	if(document.getElementsByName('use_defaults')[1].checked) { 		
		var e = document.getElementById('ls_carousel_settings');
		e.style.display = 'block';
		
	}
	lshowcaseshortcodegenerate();

}


function hidecustomcarouselsettings() {

	var autoscroll = document.getElementsByName('lshowcase_carousel_autoscroll')[0].value;

	var div_pause_time = document.getElementById('lst_pause_time');
	var div_pause_hover = document.getElementById('lst_pause_hover');
	var div_auto_controls = document.getElementById('lst_auto_controls');
	var div_carousel_settings = document.getElementById('lst_carousel_common_settings');

	div_pause_time.style.display = 'none';
	div_pause_hover.style.display = 'none';
	div_auto_controls.style.display = 'none';
	div_carousel_settings.style.display = 'none';

	if(autoscroll=="true") {
							div_pause_time.style.display = 'block'; 
							div_pause_hover.style.display = 'block'; 
							div_auto_controls.style.display = 'block'; 
							div_carousel_settings.style.display = 'block'; 
							}

	if(autoscroll=="false") {
							div_carousel_settings.style.display = 'block';  
							}

	if(autoscroll=="ticker") {
							div_pause_hover.style.display = 'block'; 
							div_pause_time.style.display = 'none';
							div_auto_controls.style.display = 'none';
							div_carousel_settings.style.display = 'none';
							}

}

// Return an array of the selected opion values
// select is an HTML select element
function lshowcase_getSelectValues(select) {
  var result = "";
  var options = select && select.options;
  var opt;

  for (var i=0, iLen=options.length; i<iLen; i++) {
    opt = options[i];

    if (opt.selected) {
      result += opt.value + ",";
    }
  }
  
  result = result.substring(0, result.length - 1);
  return result;
}

function lshowcase_save_shortcode_settings() {

	//var formdata = JSON.stringify(jQuery('#shortcode_generator').serializeArray());
	var formdata = jQuery('#shortcode_generator').serializeArray();

	console.log(formdata);
	
	var shortcode = document.getElementById('current_shortcode').value;
	console.log(shortcode);

	var data = {
			action: 'lshowcase_save_shortcode_data',
			shortcode: shortcode,
			options: formdata
		};
		
	jQuery.post(ajax_object.ajax_url, data, function(response) {
			
			//console.log(response);

			var message_div = jQuery('.lshowcase_message_area');

			message_div.show();

			message_div.html('<div class="updated">Options Saved!</div>');

			message_div.delay(4000).fadeOut('slow');



		});

}


showcustomsettings();