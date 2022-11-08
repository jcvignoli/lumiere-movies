/* Confirmation message to display when deactivating the plugin
 * Needs jQuery
 */

(function ($) {
	$( document ).on(
		'click',
		'[data-slug="lumiere-movies"] .deactivate a',
		function(e){
			if ( ! confirm( 'You have selected to not keep your settings upon uninstall. Settings, taxonomy terms and cache will be removed forever if you delete Lumière plugin.' ) ) {

				e.stopImmediatePropagation();
				e.preventDefault();
			}
		}
	);
})( jQuery );
