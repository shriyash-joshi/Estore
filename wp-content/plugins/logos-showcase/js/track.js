jQuery(document).ready(function() {

  	jQuery('.lshowcase-logos').each(function(i,cont){
  		var container = jQuery(this);
  		container.find('a').each(function(j,link){
  			jQuery(this).one('click',function(e,urllink){

  				var id = jQuery(this).closest('div[data-entry-id]').attr('data-entry-id');
  				var url = jQuery(this).attr('href');

  				var data = {
					action: 'lshowcase_track',
					id: id
				};


				jQuery.post(ajax_object.ajax_url, data, function(response) {

					console.log(response);

				});
			});

  		});
  	});

});