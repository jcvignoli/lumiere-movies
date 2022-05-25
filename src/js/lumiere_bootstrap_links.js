/**
 * Function here are Content Security Policy (CSP) Compliant
 * Needs jquery
 */

/**
 * autofocus
 *
 */

/**** popups
*
*/

/* class in the popup images, movies and persons, useBox to false */
if (jQuery( ".highslide_pic_popup" )) {
	jQuery( '.highslide_pic_popup' ).click(
		function(){
			return hs.expand(
				this,
				{ useBox: false, captionEval: "Poster of " + 'this.thumb.alt'
				}
			);
		}
	);
};

/* Class in class.movie.php, useBox to true */
if (jQuery( ".highslide_pic" )) {
	jQuery( '.highslide_pic' ).click(
		function(){
			return hs.expand(
				this,
				{ useBox: true, captionEval: "Poster of " + 'this.thumb.alt'
				}
			);
		}
	);
};


/* FUNCTION: build bootstrap popup according to the classes
*	This function on click on classes "link-imdblt-(.*)"
	1- extracts info from data-(.*) <a> attribute
*/

document.addEventListener(
	'DOMContentLoaded',
	function () {

		/* bootstrap popup, people */

		jQuery( 'a[data-bootstrappeople]' ).on('click',
			function(){

				// vars from class Settings sent to javascript
				var tmppopupLarg = lumiere_vars.popupLarg;
				var tmppopupLong = lumiere_vars.popupLong;

				// var from the html code
				var misc_term = jQuery( this ).closest( 'a' ).data( 'bootstrappeople' );

				// build the final URL
				var url_imdbperso = lumiere_vars.urlpopup_person + '/?mid=' + misc_term;

				// Open bootstrap popup link
				jQuery( '#theModal' ).modal( 'show' ).find( '.modal-content' ).load( url_imdbperso );

			}
		);

		/* bootstrap popup, movie by title */

		jQuery( 'a[data-bootstrapfilm]' ).click(
			function(){

				// vars from class Settings sent to javascript
				var tmppopupLarg = lumiere_vars.popupLarg;
				var tmppopupLong = lumiere_vars.popupLong;

				// var from the html code
				var misc_term = jQuery( this ).closest( 'a' ).data( 'bootstrapfilm' );

				// build the final URL
				var url_imdbfilm = lumiere_vars.urlpopup_film + '/?film=' + misc_term;

				// Open bootstrap popup link
				jQuery( '#theModal' ).modal( 'show' ).find( '.modal-content' ).load( url_imdbfilm );

			}
		);

		/** bootstrap popup, movie by imdb id */

		jQuery( 'a[data-bootstrap-id]' ).click(
			function(){

				// vars from class Settings sent to javascript
				var tmppopupLarg = lumiere_vars.popupLarg;
				var tmppopupLong = lumiere_vars.popupLong;

				// var mid from the class data-highslidepeople to build the link
				var misc_term = jQuery( this ).closest( 'a' ).data( 'bootstrap-id' );

				// build the final URL
				var url_imdbfilmid = lumiere_vars.urlpopup_film + '/?mid=' + misc_term;

				// Open bootstrap popup link
				jQuery( '#theModal' ).modal( 'show' ).find( '.modal-content' ).load( url_imdbfilmid );

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
