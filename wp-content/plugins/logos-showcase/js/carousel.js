	var ls_curr_slider = [];

	//initial trigger
	jQuery(document).ready(function($){

		ls_trigger_sliders();
		jQuery('html').on('mousedown', '.bx-viewport a', function() {
			var url = jQuery(this).attr('href');
			if( url !== ''){
				window.open( url, jQuery(this).attr('target') );
			}
		});

	});

	//to load after an ajax
	jQuery(document).ajaxSuccess(function($) {

		//ls_trigger_sliders();
		//console.log('I am loading');
		//console.log(lssliderparam);

	});




	//to trigger the slider reload on orientation change.
	jQuery(document).ready(function($){


		//jQuery(window).on('resize orientationchange', function($) {
			jQuery(window).on('null', function($) {

			clearTimeout(jQuery.data(this, 'resizeTimer'));
		    jQuery.data(this, 'resizeTimer', setTimeout(function() {



		       	for (var i = 0; i < ls_curr_slider.length; i++) {


					if (typeof ls_curr_slider[i] != 'undefined') {


					  ls_curr_slider[i].destroySlider();
					  ls_curr_slider[i].unbind();
					  ls_curr_slider[i] = undefined;

					}

				}

				for (var key in lssliderparam) {
					jQuery(lssliderparam[key]['divid']).css("opacity", 1).parent().prop( "style", null );

				}

				jQuery('.lshowcase-logos').each(function(){
					jQuery(this).attr('style','');
				});

				ls_trigger_sliders();

		    }, 500));

		});


	});



	//function to trigger sliders
	function ls_trigger_sliders() {

		for (var key in lssliderparam) {

			 var auto = (lssliderparam[key]['auto'] === 'true');
			 var pause = parseInt(lssliderparam[key]['pause']);
			 var autohover = (lssliderparam[key]['autohover'] === 'true');
			 var ticker = (lssliderparam[key]['ticker'] === 'true');
			 var tickerhover = (lssliderparam[key]['tickerhover'] === 'true');
			 var usecss = (lssliderparam[key]['usecss'] === 'true');
			 var autocontrols = (lssliderparam[key]['autocontrols'] === 'true');
			 var speed = parseInt(lssliderparam[key]['speed']);
			 var slidemargin = parseInt(lssliderparam[key]['slidemargin']);
			 var infiniteloop = (lssliderparam[key]['infiniteloop'] === 'true');
			 var pager = (lssliderparam[key]['pager'] === 'true');
			 var controls = (lssliderparam[key]['controls'] === 'true');
			 var slidewidth = parseFloat(lssliderparam[key]['slidewidth']);
			 var minslides = parseInt(lssliderparam[key]['minslides']);
			 var maxslides = parseInt(lssliderparam[key]['maxslides']);
			 var moveslides = parseInt(lssliderparam[key]['moveslides']);
			 var tmode = lssliderparam[key]['mode']; //options: 'horizontal', 'vertical', 'fade'
			 var direction = lssliderparam[key]['direction'];

			 var next = '';
			 var prev = '';

			 if(lssliderparam[key]['next']!=''){
			 	next = lssliderparam[key]['next'];
			 }
			 if(lssliderparam[key]['prev']!=''){
			 	prev = lssliderparam[key]['prev'];
			 }


			 //remove pager space by setting pager to false for ticker
			 if(ticker == true) {
			 	controls = false;
			 }

			 var sliderDiv = jQuery(lssliderparam[key]['divid']);
			 //sometimes the div is passed wrong, so we built a temp fix here:
			 //if(sliderDiv==false) {
			 	//sliderDiv = $('.lshowcase-wrap-carousel-1');
			 //}

			 //To improve responsive behaviour we force the limit of 1 slide in small viewports
			 if(maxslides==0 && ticker == false) {

			 	var view_width = sliderDiv.parent().width();

			 	if(controls == true ) { view_width = view_width-70; }

				 var slider_real = slidemargin + slidewidth;
				 maxslides = Math.floor(view_width/slider_real);

				 //not to ignore the minimum value
				 if(maxslides < minslides) {
				 	maxslides = minslides;
				 }

				 if(moveslides==0) {
				 	moveslides = maxslides;
				 }

			 }

			 if(maxslides==0 && ticker != false) {
			 	maxslides = 99;
			 }


			//fix bug of 1 slider only infinite loop not working
	        //another solution could be adding the slidemargin to
	        //the slideWidth and align the images in the center
	        if (maxslides <= 1 && ticker == false) {
	            //set margin to null
	            slidemargin = 0;
	            //make sure max slides is set to 1
	            maxslides = 1;
	            //make sure moveslides is set to 1
	            moveSlides = 1;

	        }

			sliderDiv.fadeIn('fast');


		    ls_curr_slider[key] = sliderDiv.bxSlider({
		    auto: auto,
			pause: pause,
			autoHover: autohover,
			ticker: ticker,
			tickerHover: tickerhover,
			useCSS: usecss,
			autoControls: autocontrols,
			mode: tmode,
			speed: speed,
			slideMargin: slidemargin,
			infiniteLoop: infiniteloop,
		    pager: pager,
			controls: controls,
		    slideWidth: slidewidth,
		    minSlides: minslides,
		    maxSlides: maxslides,
		    moveSlides: moveslides,
		    autoDirection: direction,
		    nextText: next,
		    prevText: prev,
		    //captions: true,
            //Make Slider Start again after controls get clicked (useful for auto slide)
            /*
            onSlideAfter: function() {
                ls_curr_slider[key].stopAuto();
                ls_curr_slider[key].startAuto();
            },
            */
		    onSliderLoad: function(currentIndex){

		    	var sl = sliderDiv.parent().parent();

		    		var marg = '0 35px';

		    		if(controls == false ) { marg = 'none'; }

				   sl.css({
						margin: marg
						});

				   sl.parent().css({
						maxWidth: sl.width()+80
						});


				   //to align elements in the center in ticker
				   /*
					We change the class, becasue the lshowcase-slide has a float:none!important that breaks
					the ticker code.
					Note: Seems safari doesn't trigger the ticker = true if there are other sliders non-ticker in the page
				   */

				   if(ticker == true) {

				   	sliderheight = sliderDiv.parent().height();

		                if(sliderheight>0) {
		                	sliderDiv.find(".lshowcase-slide")
		                	.addClass('lshowcase-ticker-slide')
		                	.removeClass('lshowcase-slide')
		                	.css("height",sliderheight + 'px');
		                }

				   }


				   /* Tooltip code *
				   /* Fix for cloned elements. Run tooltip code only after the slider is loaded

				   var toshow = 'title';

					jQuery('.lshowcase-tooltip').tooltip({
					content: function () { return jQuery(this).attr(toshow) },
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



				   /* End tooltip code */

		    }

			});

		}

		//Use Custom Controls
		if( jQuery('#ls-slider-prev').length )

		{

			//custom controls
			jQuery("#ls-slider-prev").click(function(){
			    ls_curr_slider[0].goToPrevSlide();
			    ls_curr_slider[1].goToPrevSlide();
			    ls_curr_slider[0].stopAuto();
			    ls_curr_slider[1].stopAuto();
			});

			jQuery("#ls-slider-next").click(function(){
			    ls_curr_slider[0].goToNextSlide();
			    ls_curr_slider[1].goToNextSlide();
			    ls_curr_slider[0].stopAuto();
			    ls_curr_slider[1].stopAuto();
			});

		}




	}




