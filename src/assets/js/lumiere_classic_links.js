/**
 * Build classical links popup
 * Function here are Content Security Policy (CSP) Compliant
 * Needs jquery
 */

/**** popups
*
*/

/**
 * FUNCTION: build classic popup according to the classes
 *	This function on click on data "modal_window(.*)"
 *	1- extracts info from data-(.*) <a> attribute
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
				var misc_term = jQuery( this ).closest( 'a' ).data( 'modal_window_people' );
				var url_imdbperso = lumiere_vars.urlpopup_person + '?mid=' + misc_term;

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
				var misc_term = jQuery( this ).closest( 'a' ).data( 'modal_window_film' );
				var url_imdbperso = lumiere_vars.urlpopup_film + '?film=' + misc_term;

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
				var misc_term = jQuery( this ).closest( 'a' ).data( 'modal_window_filmid' );
				var url_imdbperso = lumiere_vars.urlpopup_film + '?mid=' + misc_term;

				// classic popup
				window.open( url_imdbperso, 'popup', 'resizable=yes, toolbar=no, scrollbars=yes, location=no, width=' + tmppopupLarg + ', height=' + tmppopupLong + ', top=5, left=5' );
			}
		);

	}
);

/**** popup-imdb_person.php
*
*/

jQuery( '.historyback' ).click(
	function(event){
		event.preventDefault();
		window.history.back();
	}
);


/**** popups all
*
*/

// executed only if div id lumiere_loader is found

if (document.getElementById( "lumiere_loader" )) {
	document.onreadystatechange = function() {

		if (document.readyState !== "complete") {
			document.querySelector(
				"body"
			).style.visibility = "hidden";
			document.querySelector(
				"#lumiere_loader"
			).style.visibility = "visible";
		} else {
			document.querySelector(
				"#lumiere_loader"
			).style.display = "none";
			document.querySelector(
				"body"
			).style.visibility = "visible";
		}
	}
};
