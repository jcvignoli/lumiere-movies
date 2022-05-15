/**
 * Build classical links popup 
 * Function here are Content Security Policy (CSP) Compliant
 * Needs jquery
 */

/**** popups
*
*/


/* FUNCTION: build classic popup according to the classes
*	This function on click on classes "link-imdblt-(.*)"
	1- extracts info from data-(.*) <a> attribute
	2- builds -classic- popup accordingly
*/

document.addEventListener(
	'DOMContentLoaded',
	function () {

		/** classic popup, people */

		jQuery( 'a[data-classicpeople]' ).click(
			function(){
				// vars from imdb-link-transformer.php
				var tmppopupLarg = lumiere_vars.popupLarg;
				var tmppopupLong = lumiere_vars.popupLong;
				// var mid from the class data-highslidepeople to build the link
				var misc_term = jQuery( this ).closest( 'a' ).data( 'classicpeople' );
				//      var url_imdbperso = lumiere_vars.imdb_path + 'inc/popup-imdb_person.php?mid=' + misc_term;
				var url_imdbperso = lumiere_vars.urlpopup_person + misc_term + '/?mid=' + misc_term;

				// classic popup
				window.open( url_imdbperso, 'popup', 'resizable=yes, toolbar=no, scrollbars=yes, location=no, width=' + tmppopupLarg + ', height=' + tmppopupLong + ', top=5, left=5' )
			}
		);

		/** classic popup, movie by title */

		jQuery( 'a[data-classicfilm]' ).click(
			function(){
				// vars from imdb-link-transformer.php
				var tmppopupLarg = lumiere_vars.popupLarg;
				var tmppopupLong = lumiere_vars.popupLong;
				// var mid from the class data-highslidepeople to build the link
				var misc_term = jQuery( this ).closest( 'a' ).data( 'classicfilm' );
				//      var url_imdbperso = lumiere_vars.imdb_path + 'inc/popup-search.php?film=' + misc_term;
				var url_imdbperso = lumiere_vars.urlpopup_film + misc_term + '/?film=' + misc_term;

				// classic popup
				window.open( url_imdbperso, 'popup', 'resizable=yes, toolbar=no, scrollbars=yes, location=no, width=' + tmppopupLarg + ', height=' + tmppopupLong + ', top=5, left=5' );
			}
		);

		/** classic popup, movie by imdb id */

		jQuery( 'a[data-classicfilm-id]' ).click(
			function(){
				// vars from imdb-link-transformer.php
				var tmppopupLarg = lumiere_vars.popupLarg;
				var tmppopupLong = lumiere_vars.popupLong;
				// var mid from the class data-highslidepeople to build the link
				var misc_term = jQuery( this ).closest( 'a' ).data( 'classicfilm-id' );
				//      var url_imdbperso = lumiere_vars.imdb_path + 'inc/popup-search.php?film=' + misc_term;
				var url_imdbperso = lumiere_vars.urlpopup_film + misc_term + '/?mid=' + misc_term;

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

// send close command on click on X of highslide popup
// this is a trick to make highslide CSP compliant
/* doesn't work
document.addEventListener('DOMContentLoaded', function () {
		jQuery(document).click(function(event) {
		hs.close(event.target);
	});
});
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
