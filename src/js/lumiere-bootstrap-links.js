/**
 * Function here are Content Security Policy (CSP) Compliant
 * Doesn't need jquery since Bootstrap v5.0
 */

/**
 * FUNCTION: build bootstrap popup according to the classes
 *	This function on click on data "modal_window(.*)"
 *	1- extracts info from data-(.*) <a> attribute
 */
document.addEventListener(
	'DOMContentLoaded',
	function () {

		/** bootstrap popup, people */

		jQuery( 'a[data-modal_window_people]' ).on(
			'click',
			function(){

				// vars from class Settings sent to javascript
				var tmppopupLarg = lumiere_vars.popupLarg;
				var tmppopupLong = lumiere_vars.popupLong;

				// var from the html code
				var misc_term = jQuery( this ).closest( 'a' ).data( 'modal_window_people' );

				// build the final URL
				var url_imdbperso = lumiere_vars.urlpopup_person + '?mid=' + misc_term;

				// Open bootstrap popup link
				jQuery( '.modal-body' ).html( '<object data="' + url_imdbperso + '"/>' );
				jQuery( '#theModal' + misc_term ).modal( 'show' );

			}
		);

		/** bootstrap popup, movie by title */

		jQuery( 'a[data-modal_window_film]' ).click(
			function(){

				// vars from class Settings sent to javascript
				var tmppopupLarg = lumiere_vars.popupLarg;
				var tmppopupLong = lumiere_vars.popupLong;

				// var from the html code
				var misc_term = jQuery( this ).closest( 'a' ).data( 'modal_window_film' );

				// build the final URL
				var url_imdbfilm = lumiere_vars.urlpopup_film + '?film=' + misc_term;

				// Open bootstrap popup link
				jQuery( '.modal-body' ).html( '<object data="' + url_imdbfilm + '"/>' );
				jQuery( '#theModal' + misc_term ).modal( 'show' );

			}
		);

		/** bootstrap popup, movie by imdb id */

		jQuery( 'a[data-modal_window_filmid]' ).click(
			function(){

				// vars from class Settings sent to javascript
				var tmppopupLarg = lumiere_vars.popupLarg;
				var tmppopupLong = lumiere_vars.popupLong;

				// var mid from the class data-highslidepeople to build the link
				var misc_term = jQuery( this ).closest( 'a' ).data( 'modal_window_filmid' );

				// build the final URL
				var url_imdbfilmid = lumiere_vars.urlpopup_film + '?mid=' + misc_term;

				// Open bootstrap popup link
				jQuery( '.modal-body' ).html( '<object data="' + url_imdbfilmid + '"/>' );
				jQuery( '#theModal' + misc_term ).modal( 'show' );

			}
		);

	}
);

/**
 * Deactivate the spinner after loading
 */
jQuery( document ).ready(
	function() {
		jQuery( '.spinner-border' ).hide();
	}
);
