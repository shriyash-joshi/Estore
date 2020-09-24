
jQuery.noConflict();
	
	jQuery('#ls-enhance-filter-nav #ls-all').addClass('ls-current-li');
	jQuery("#ls-enhance-filter-nav > li").click(function(){
	    ls_show_enhance(this.id);
	}).children().click(function(e) {
	  return false;
	});

	jQuery("#ls-enhance-filter-nav > li > ul > li").click(function(){
	    ls_show_enhance(this.id);
	});


//FILTER CODE
function ls_show_enhance(category) {	

	if (category == "ls-all") {
        jQuery('#ls-enhance-filter-nav > li').removeClass('ls-current-li');
        jQuery('#ls-enhance-filter-nav #ls-all').addClass('ls-current-li');
        jQuery('.lshowcase-filter-enhance').addClass('ls-current').removeClass('ls-not-current');
		}
	
	else {
		jQuery('#ls-enhance-filter-nav > li').removeClass('ls-current-li');
   		jQuery('#ls-enhance-filter-nav #' + category).addClass('ls-current-li'); 
   		jQuery('.lshowcase-filter-enhance.' + category).addClass('ls-current');
 		jQuery('.lshowcase-filter-enhance:not(.'+category+')').removeClass('ls-current').addClass('ls-not-current');
	}

	//hack to solve menu left open on touch devices
	/*

		jQuery('ul li ul li.ls-current-li')
		.parent()
		.hide()
		.parent()
		.on('click', function(){ 
			jQuery(this).addClass('ls-current-li')
			.children().show(); 
		});

	*/
	
}



jQuery(document).ajaxSuccess(function() {
	jQuery('#ls-enhance-filter-nav #ls-all').addClass('ls-current-li');
	jQuery("#ls-enhance-filter-nav > li").click(function(){
	    ls_show_enhance(this.id);
	}).children().click(function(e) {
	  return false;
	});

	jQuery("#ls-enhance-filter-nav > li > ul > li").click(function(){
	    ls_show_enhance(this.id);
	});
});