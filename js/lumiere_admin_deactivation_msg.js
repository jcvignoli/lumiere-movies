/* Confirmation message to display when deactivating the plugin
 *
 */

(function ($) {
  $(document).on('click', '[data-slug="lumiere-movies"] .deactivate a', function(e){
	if( !confirm('You have selected to not keep your settings upon deactivation. Settings, taxonomy terms and cache will be deleted.') ){
         
		  e.stopImmediatePropagation();
		  e.preventDefault();
	}
  });
})(jQuery);


