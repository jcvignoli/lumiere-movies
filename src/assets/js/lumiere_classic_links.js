/**
 * Classic link popup
 * Function are Content Security Policy (CSP) Compliant
 * Needs jquery
 *
 * FUNCTIONS:
 *	(A) build classic popup according to the classes
 *		This function on click on data "modal_window(.*)"
 *		1- extracts info from data-(.*) <a> attribute
 */
document.addEventListener(
	'DOMContentLoaded',
	function () {

		/** classic popup, people */

		jQuery( 'a[data-modal_window_people]' ).click(
			function(){
				// vars from imdb-link-transformer.php
				var tmppopupLarg = lumiere_vars.popupLarg;
				var tmppopupLong = lumiere_vars.popupLong;
				// var mid from the class data-highslidepeople to build the link
				var modal_window_people = jQuery( this ).closest( 'a' ).data( 'modal_window_people' );
				var modal_window_nonce = jQuery( this ).closest( 'a' ).data( 'modal_window_nonce' );
				
				var url_imdbperso = lumiere_vars.urlpopup_person + '?mid=' + modal_window_people + '&_wpnonce=' + modal_window_nonce;

				// classic popup
				window.open( url_imdbperso, 'popup', 'resizable=yes, toolbar=no, scrollbars=yes, location=no, width=' + tmppopupLarg + ', height=' + tmppopupLong + ', top=5, left=5' )
			}
		);

		/** classic popup, movie by title */

		jQuery( 'a[data-modal_window_film]' ).click(
			function(){
				// vars from imdb-link-transformer.php
				var tmppopupLarg = lumiere_vars.popupLarg;
				var tmppopupLong = lumiere_vars.popupLong;
				// var mid from the class data-highslidepeople to build the link
				var modal_window_film = jQuery( this ).closest( 'a' ).data( 'modal_window_film' );
				var modal_window_nonce = jQuery( this ).closest( 'a' ).data( 'modal_window_nonce' );
				
				var url_imdbperso = lumiere_vars.urlpopup_film + '?film=' + modal_window_film + '&_wpnonce=' + modal_window_nonce;

				// classic popup
				window.open( url_imdbperso, 'popup', 'resizable=yes, toolbar=no, scrollbars=yes, location=no, width=' + tmppopupLarg + ', height=' + tmppopupLong + ', top=5, left=5' );
			}
		);

		/** classic popup, movie by imdb id */

		jQuery( 'a[data-modal_window_filmid]' ).click(
			function(){
				// vars from imdb-link-transformer.php
				var tmppopupLarg = lumiere_vars.popupLarg;
				var tmppopupLong = lumiere_vars.popupLong;
				// var mid from the class data-highslidepeople to build the link
				var modal_window_filmid = jQuery( this ).closest( 'a' ).data( 'modal_window_filmid' );
				var modal_window_nonce = jQuery( this ).closest( 'a' ).data( 'modal_window_nonce' );
				
				var url_imdbperso = lumiere_vars.urlpopup_film + '?mid=' + modal_window_filmid + '&_wpnonce=' + modal_window_nonce;

				// classic popup
				window.open( url_imdbperso, 'popup', 'resizable=yes, toolbar=no, scrollbars=yes, location=no, width=' + tmppopupLarg + ', height=' + tmppopupLong + ', top=5, left=5' );
			}
		);

	}
);
