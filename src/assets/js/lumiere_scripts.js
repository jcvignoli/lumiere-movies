/**
 * Frontpage functions
 * Spinners, go back, submit form
 * Function are Content Security Policy (CSP) compliant
 *
 * FUNCTIONS:
 *
 * (a) Show a spinner when clicking on a link within a modal window
 * (b) Javascript back in history
 * (c) Submit automatically on dropdown change
 */
document.addEventListener(
	'DOMContentLoaded',
	function () {

		/**
		 * Posts/Pages
		 */
	
		/**
		 * Spinner click
		 * This will show a spinner when clicking an HTML link tag 'a' with class .lum_add_spinner
		 *
		 * JS Script: This will add a <div class="spinner"> inside a span|div|whatever with id="spinner-placeholder" and a <div id="parent-spinner">
		 * CSS Stylesheet: It uses a css to customize div class "loader"
		 * Add HTML: A a span|div|whatever id="spinner-placeholder" must be put in the HTML text (popup classes)
		 * Timeout: A 1 sec (1000 ms) timeout is integrated, so the spinner start being displayed only when needed
		 */
		jQuery(function(){
			const SPINNER_TIMEOUT = 1000;
			const spinnerHTML = '<div id="parent-spinner"><div id="spinner"></div></div>';

			jQuery('.lum_add_spinner').click(function(event) {
				// event.preventDefault(); // If prevent default, can't load a new (person) link to a popup inside a popup
				setTimeout(() => {
				    const $spinnerPlaceholder = jQuery('#spinner-placeholder'); // Cache jQuery selector
				    jQuery(spinnerHTML).prependTo($spinnerPlaceholder);
				}, SPINNER_TIMEOUT);
			});
		});

		/**
		 * popup person
		 */
		 	 
		 /* go back if clicked #lum_popup_link_back */
		jQuery("#lum_popup_link_back").on( 'click', function(){
			event.preventDefault();
			history.back();
		});
	}


);

/**
 * Taxonomy people pages
 * (a) Submit automatically on dropdown change
 */
jQuery(document).ready(function() {
	jQuery('#tag_lang').on('change', function() {
		jQuery(this).closest('form').submit();
	});
  
});
