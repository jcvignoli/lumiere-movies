/**
 * Functions here help achieve Content Security Policy (CSP) compliance
 * Need jquery
 * Only loaded in gutenberg block edition pages
 */

/**
 * Slightly modified Lumiere jQuery script opening a popup when clicking on 'a[data-lumiere_admin_search_popup]'
 * To make it work, we track all clicks on <div>, and keep only if it has data-lumiere_admin_search_popup, then we open a popup
 */
document.addEventListener(
	'DOMContentLoaded',
	() => {
		jQuery( 'div' ).click(
			(event) => {
				if ( jQuery(event.target).data('lumiere_admin_search_popup') ){
					// Vars from Settings class, transmitted in script lumiere_scripts_admin_vars from class Admin
					var tmppopupLarg = lumiere_admin_vars.popupLarg;
					var tmppopupLong = lumiere_admin_vars.popupLong;
					var url_imdbperso = lumiere_admin_vars.wordpress_admin_path + lumiere_admin_vars.gutenberg_search_url_string;

					// Classic javascript popup
					window.open( url_imdbperso, 'popup', 'resizable=yes, toolbar=no, scrollbars=yes, location=no, width=' + tmppopupLarg + ', height=' + tmppopupLong + ', top=100, left=100' );
				}
			}
		);
	}
);
