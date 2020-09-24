jQuery(document).ready(function() {

  	ls_build_hide_filter();

  	//In case you want all entries to hide when the page loads
	//jQuery('.ls-05_project-5').hide();
	//To load a particular category
	//jQuery('#ls-01-sales-team').click();

	jQuery('html').on('mousedown', '.bx-viewport .lshowcase-slide a', function() {
		var url = jQuery(this).attr('href');
		if( url !== ''){
			window.open( url, jQuery(this).attr('target') );
		}
	});

});

function ls_build_hide_filter() {


	jQuery.noConflict();

	jQuery('#ls-filter-nav #ls-all').addClass('ls-current-li');
	jQuery("#ls-filter-nav > li").click(function(){
	    ls_show(this.id);
	}).children().click(function(e) {
	  return false;
	});

	jQuery("#ls-filter-nav > li > ul > li").click(function(){
	    ls_show(this.id);
	});


	//solve menu left open on mobile
	window.have_touch = !!('ontouchstart' in window) || !!('onmsgesturechange' in window);


	if(window.have_touch){

		jQuery('ul#ls-filter-nav li ul li').on('click',function(){

			jQuery(this).parent().hide().parent().on('click', function(){
					jQuery(this).children().show();
				});

		});

	}



}


//FILTER CODE
function ls_show(category) {



	if (category == "ls-all") {
        jQuery('#ls-filter-nav li').removeClass('ls-current-li');
        jQuery('#ls-filter-nav #ls-all').addClass('ls-current-li');
        jQuery('.lshowcase-filter-active').show(1400,'easeInOutExpo');
		}

	else {

		jQuery('#ls-filter-nav li').removeClass('ls-current-li');
   		jQuery('#ls-filter-nav #' + category).addClass('ls-current-li');

   		if(jQuery('#'+category).parent().parent().is('li')) {
   			jQuery('#' + category).parent().parent().addClass('ls-current-li');
   		}

		jQuery('.lshowcase-filter-active.' + category).show(1400,'easeInOutExpo');
		jQuery('.lshowcase-filter-active:not(.'+ category+')').hide(800,'easeInOutExpo');



	}

}


jQuery(document).ajaxSuccess(function() {

  	ls_build_hide_filter();

});