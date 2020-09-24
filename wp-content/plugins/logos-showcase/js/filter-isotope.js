jQuery( document ).ready( function() {
  ls_isotope_process();
});



jQuery(document).ajaxSuccess(function() { 
  ls_isotope_process();
});

function get_maxHeight() {
  var ls_maxHeight = -1;
  jQuery('.lshowcase-isotope').each(function() {
      if (jQuery(this).height() > ls_maxHeight) {
          ls_maxHeight = jQuery(this).height();
      }
  }).each(function(){
      jQuery(this).css('height',ls_maxHeight);
  });

  return ls_maxHeight;
}

function ls_isotope_process() {

  // init Isotope
  var $container = jQuery('.lshowcase-isotope-container').isotope({
    itemSelector: '.lshowcase-isotope',
     layoutMode: 'cellsByRow',
       cellsByRow: {
        columnWidth: '.lshowcase-isotope' ,
        rowHeight: get_maxHeight()
      }
    });

  // layout Isotope after each image loads
    $container.imagesLoaded().progress( function() {
      $container.isotope('layout');
    });


  jQuery('ul#ls-isotope-filter-nav li#ls-all').addClass('ls-current-li');
  

  //parent menu
  jQuery('ul#ls-isotope-filter-nav > li').on( 'click', function() {
    
    var filterValue = jQuery(this).attr('data-filter');
    jQuery('#ls-isotope-filter-nav > li').removeClass('ls-current-li');
    jQuery(this).addClass('ls-current-li');

    //stop propagation
    jQuery(this).children('ul').click(function(e) {
      e.stopPropagation();
    });

    $container.isotope({ filter: filterValue });
  
  });

  //submenus
  jQuery('ul#ls-isotope-filter-nav > li > ul > li').on('click',function(){
          
    jQuery('#ls-isotope-filter-nav li').removeClass('ls-current-li');
    jQuery(this).addClass('ls-current-li');

    filterValue = jQuery(this).attr('data-filter');
    if(filterValue == ''){
      filterValue = '*';
    }

     //stop propagation
     jQuery(this).parent().parent().click(function(e) {
        e.stopPropagation();
     });

     //add to parent the current li style only after filter ran
     jQuery(this).parent().parent().addClass('ls-current-li');
     $container.isotope({ filter: filterValue });
  });

}

