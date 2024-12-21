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
document.addEventListener('DOMContentLoaded', function () {

	/**
	 * Function to open a popup window with the specified URL and dimensions.
	 * @param {string} url - The URL to open in the popup.
	 * @param {number} width - The width of the popup window.
	 * @param {number} height - The height of the popup window.
	 */
	function openPopup(url, width, height) {
		window.open(url, 'popup', `resizable=yes, toolbar=no, scrollbars=yes, location=no, width=${width}, height=${height}, top=5, left=5`);
	}

	/** People popup */
	jQuery('a[data-modal_window_people]').click(function() {
	    const url = `${lumiere_vars.urlpopup_person}?mid=${jQuery(this).data('modal_window_people')}&_wpnonce=${jQuery(this).data('modal_window_nonce')}`;
	    openPopup(url, lumiere_vars.popupLarg, lumiere_vars.popupLong);
	});

	/** Movie by title popup */
	jQuery('a[data-modal_window_film]').click(function() {
	    const url = `${lumiere_vars.urlpopup_film}?film=${jQuery(this).data('modal_window_film')}&_wpnonce=${jQuery(this).data('modal_window_nonce')}`;
	    openPopup(url, lumiere_vars.popupLarg, lumiere_vars.popupLong);
	});

	/** Movie by imdb id popup */
	jQuery('a[data-modal_window_filmid]').click(function() {
	    const url = `${lumiere_vars.urlpopup_film}?mid=${jQuery(this).data('modal_window_filmid')}&_wpnonce=${jQuery(this).data('modal_window_nonce')}`;
	    openPopup(url, lumiere_vars.popupLarg, lumiere_vars.popupLong);
	});

});
