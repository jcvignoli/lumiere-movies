/**
 * Executed in POPUPS
 * Function must be Content Security Policy (CSP) compliant
 * Bootstrap, Classic and Highslide
 *
 * FUNCTIONS:
 *
 * (A) Show a spinner when clicking on a link within a modal window
 * (b) Javascript back in history
 */
document.addEventListener(
	'DOMContentLoaded',
	function () {
	
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
		​	jQuery('.lum_add_spinner').click(function(){
				setTimeout(() => {
					jQuery('<div id="parent-spinner"><div id="spinner"></div></div>').prependTo(jQuery('#spinner-placeholder'));
				}, 1000);
			});​
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


