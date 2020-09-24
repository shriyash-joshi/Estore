/*
Inpired by Darcy Clarke code.
*/

	jQuery(document).ready(function($){
		
		$(".lshowcase-jquery-gray").fadeTo( "slow", 1 );
		
		// clone image
		$('.lshowcase-jquery-gray').each(function(){
			var el = $(this);
			el.css({"position":"absolute"}).wrap("<div class='img_wrapper' style='display: inline-block'>").clone().addClass('ls_img_grayscale').css({"position":"absolute","z-index":"998","opacity":"0"}).insertBefore(el).queue(function(){
				var el = $(this);
				el.parent().css({"width":this.width,"height":this.height});
				el.dequeue();
			});
			this.src = ls_grayscale(this.src);
		});
		
		// Fade image 
		$('.lshowcase-jquery-gray').mouseover(function(){
			$(this).parent().find('img:first').stop().animate({opacity:1}, 1000);
		})
		$('.ls_img_grayscale').mouseout(function(){
			$(this).stop().animate({opacity:0}, 1000);
		});		
	});
	
	// Grayscale effect with canvas method
	function ls_grayscale(src){
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