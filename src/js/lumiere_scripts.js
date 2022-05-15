/* Function here are Content Security Policy (CSP) Compliant
*  Needs jquery
*/

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
