jQuery('document').ready(function()
{
	jQuery( document.body ).on('added_to_cart', wctbp_rload_shop_page)
});
function wctbp_rload_shop_page(event)
{
	setTimeout(function(){ window.location.reload(true);}, 300);
}