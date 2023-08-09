/**
 * Executed in POPUPS
 * Function must be Content Security Policy (CSP) compliant
 */

/**
 * popup-imdb_person.php
 */
 var histobackid = document.getElementById("historyback");
if ( histobackid != null ) {
	histobackid.addEventListener("click", () => {
	  history.back();
	});
}
/**
 * popups all
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

