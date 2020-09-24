jQuery(document).on('click', ".schedule-type-title", function() {
	//jQuery(this).toggleClass("active-panel");
	var scrollHeight=jQuery(this).next().prop("scrollHeight");
	if(jQuery(this).next().height())
	{
		jQuery(this).removeClass('active-panel');
		jQuery(this).next().css('height','0');
	}
	else{
		jQuery(this).addClass('active-panel');
		jQuery(this).next().css('height',scrollHeight+'px');
		jQuery("#woo_schedule_collapse_selection").css("display","block");
	}
});
 
 jQuery(document).on('change', ".dataTables_length select",function() {
 	var container = $(this).parents('.dataTables_wrapper');
 	var scrollHeight=jQuery(container).prop("scrollHeight");
 	jQuery(container).css('height',scrollHeight+'px');
});

jQuery(document).on('click', "#woo_schedule_collapse_selection", function() {
	jQuery(".dataTables_wrapper").css('height','0');
	jQuery(".schedule-type-title").removeClass("active-panel");
	jQuery("#woo_schedule_collapse_selection").css("display","none");
});

jQuery(document).on('click', ".schedule-type-title", function() {
	var heights = new Array();
	allheight=0;
	jQuery('.dataTables_wrapper').each(function() {
			heights.push(jQuery(this).height());
	});
	$.each(heights, function(index, value){
            if(value == 0)
            {
            	allheight++;
            }
    });
    if(allheight == heights.length){
    	jQuery("#woo_schedule_collapse_selection").css("display","none");
    }
});
