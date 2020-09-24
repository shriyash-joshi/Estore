(function($){
    jQuery(document).on("click", ".schedule-type-title", function() {
        var e = jQuery(this).next().prop("scrollHeight");
        jQuery(this).next().height() ? (jQuery(this).removeClass("active-panel"),
        jQuery(this).next().css("height", "0")) : (jQuery(this).addClass("active-panel"),
        jQuery(this).next().css("height", e + "px"),
        jQuery("#woo_schedule_collapse_selection").css("display", "block"))
    }),
    jQuery(document).on("change", ".dataTables_length select", function() {
        var e = $(this).parents(".dataTables_wrapper")
        , t = jQuery(e).prop("scrollHeight");
        jQuery(e).css("height", t + "px")
    }),
    jQuery(document).on("click", "#woo_schedule_collapse_selection", function() {
        jQuery(".dataTables_wrapper").css("height", "0"),
        jQuery(".schedule-type-title").removeClass("active-panel"),
        jQuery("#woo_schedule_collapse_selection").css("display", "none")
    }),
    jQuery(document).on("click", ".schedule-type-title", function() {
        var e = new Array;
        allheight = 0,
        jQuery(".dataTables_wrapper").each(function() {
            e.push(jQuery(this).height())
        }),
        $.each(e, function(e, t) {
            0 == t && allheight++
        }),
        allheight == e.length && jQuery("#woo_schedule_collapse_selection").css("display", "none")
    });
})(jQuery);
