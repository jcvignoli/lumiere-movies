/* Function here are Content Security Policy (CSP) Compliant
*  Needs jquery
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


/* FUNCTION: build highslide or classic popup according to the classes
*	This function on click on classes "link-imdblt-(.*)"
	1- extracts info from data-(.*) <a> attribute
	2- builds either a -highslide- or -classic- popup accordingly
*/

document.addEventListener(
	'DOMContentLoaded',
	function () {

		/* highslide popup, people */

		jQuery( 'a[data-modal_window_people]' ).click(
			function(){
				// vars from imdb-link-transformer.php
				var tmppopupLarg = lumiere_vars.popupLarg;
				var tmppopupLong = lumiere_vars.popupLong;
				// var mid from the class data-highslidepeople to build the link
				var misc_term = jQuery( this ).closest( 'a' ).data( 'modal_window_people' );
				//      var url_imdbperso = lumiere_vars.imdb_path + 'inc/popup-imdb_person.php?mid=' + misc_term;
				var url_imdbperso = lumiere_vars.urlpopup_person + '/?mid=' + misc_term;
				// highslide popup
				return hs.htmlExpand(
					this,
					{
						allowWidthReduction: true,
						objectType: 'iframe',
						width: tmppopupLarg,
						objectWidth: tmppopupLarg,
						objectHeight: tmppopupLong,
						headingEval: 'this.a.innerHTML',
						wrapperClassName: 'titlebar',
						src: url_imdbperso
					}
				);
			}
		);

		/* highslide popup, movie by title */

		jQuery( 'a[data-modal_window_film]' ).click(
			function(){
				// vars from imdb-link-transformer.php
				var tmppopupLarg = lumiere_vars.popupLarg;
				var tmppopupLong = lumiere_vars.popupLong;
				// var mid from the class data-highslidepeople to build the link
				var misc_term = jQuery( this ).closest( 'a' ).data( 'modal_window_film' );
				//      var url_imdbperso = lumiere_vars.imdb_path + 'inc/popup-search.php?film=' + misc_term;
				var url_imdbperso = lumiere_vars.urlpopup_film + '/?film=' + misc_term;
				// highslide popup
				return hs.htmlExpand(
					this,
					{
						allowWidthReduction: true,
						objectType: 'iframe',
						width: tmppopupLarg,
						objectWidth: tmppopupLarg,
						objectHeight: tmppopupLong,
						headingEval: 'this.a.innerHTML',
						wrapperClassName: 'titlebar',
						src: url_imdbperso
					}
				);
			}
		);

		/** highslide popup, movie by imdb id */

		jQuery( 'a[data-modal_window_filmid]' ).click(
			function(){
				// vars from imdb-link-transformer.php
				var tmppopupLarg = lumiere_vars.popupLarg;
				var tmppopupLong = lumiere_vars.popupLong;
				// var mid from the class data-highslidepeople to build the link
				var misc_term = jQuery( this ).closest( 'a' ).data( 'modal_window_filmid' );
				//      var url_imdbperso = lumiere_vars.imdb_path + 'inc/popup-search.php?film=' + misc_term;
				var url_imdbperso = lumiere_vars.urlpopup_film + '/?mid=' + misc_term;
				// highslide popup
				return hs.htmlExpand(
					this,
					{
						allowWidthReduction: true,
						objectType: 'iframe',
						width: tmppopupLarg,
						objectWidth: tmppopupLarg,
						objectHeight: tmppopupLong,
						headingEval: 'this.a.innerHTML',
						wrapperClassName: 'titlebar',
						src: url_imdbperso
					}
				);
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
