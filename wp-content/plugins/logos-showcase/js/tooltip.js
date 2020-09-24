
jQuery(document).ready(function($){

		//bootstrap tooltip conflict
		//var bsTooltip = $.fn.tooltip.noConflict();
		//$(document).tooltip();

		var toshow = 'title'; 		

		$('.lshowcase-tooltip img').tooltip({
		content: function () { return $(this).attr(toshow) },
		close: function( event, ui ) {
			    ui.tooltip.hover(
			        function () {
			            $(this).stop(true).fadeTo(400, 1); 
			            //.fadeIn("slow"); // doesn't work because of stop()
			        },
			        function () {
			            $(this).fadeOut("400", function(){ $(this).remove(); })
			        }
			    );
			  },
		position: {
		my: "center bottom-20",
		at: "center top",
		using: function( position, feedback ) {
		$( this ).css( position );
		$( "<div>" )
		.addClass( "lsarrow" )
		.addClass( feedback.vertical )
		.addClass( feedback.horizontal )
		.appendTo( this );
		}
		}
		});
	

});


