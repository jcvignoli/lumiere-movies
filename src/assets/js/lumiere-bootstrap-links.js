/**
 * Function here are Content Security Policy (CSP) Compliant
 */

/**
 * FUNCTION: build bootstrap popup according to the classes
 * This function on click on data "modal_window(.*)"
 * 1- Extracts info from data-(.*) <a> attribute
 * 2- Add a spinner with the class showspin, and removed it after a timeout
 */

timeout = 1000;

document.addEventListener(
	'DOMContentLoaded',
	function () {

		/** bootstrap popup, IMDb people's name */

		jQuery( 'a[data-modal_window_people]' ).on(
			'click',
			function(){

				// var from the html code
				var misc_term = jQuery( this ).closest( 'a' ).data( 'modal_window_people' );

				// build the final URL
				var url_imdbperso = lumiere_vars.urlpopup_person + '?mid=' + misc_term;

				// Open bootstrap popup link
				jQuery('.modal-header').addClass('showspin');
				jQuery( '.modal-body' ).html( '<object id="' + misc_term + '" name="' + misc_term + '" data="' + url_imdbperso + '"/>' );
				themodal = jQuery( '#theModal' + misc_term ).modal( 'show' );
				
				themodal.on('shown.bs.modal', function() {
					setTimeout(() => {
						jQuery('.modal-header').removeClass('showspin');
					}, timeout );
				});

			}
		);

		/** bootstrap popup, movie by IMDb Title */

		jQuery( 'a[data-modal_window_film]' ).click(
			function(){

				// var from the html code
				var misc_term = jQuery( this ).closest( 'a' ).data( 'modal_window_film' );

				// build the final URL
				var url_imdbfilm = lumiere_vars.urlpopup_film + '?film=' + misc_term;

				// Open bootstrap popup link
				jQuery('.modal-header').addClass('showspin');
				jQuery( '.modal-body' ).html( '<object name="' + misc_term + '" data="' + url_imdbfilm + '"/>' );
				themodal = jQuery( '#theModal' + misc_term ).modal( 'show' );
				
				themodal.on('shown.bs.modal', function() {
					setTimeout(() => {
						jQuery('.modal-header').removeClass('showspin');
					}, timeout );
				});
			}
		);

		/** bootstrap popup, movie by IMDb ID */

		jQuery( 'a[data-modal_window_filmid]' ).click(
			function(){

				// var mid from the class data-highslidepeople to build the link
				var misc_term = jQuery( this ).closest( 'a' ).data( 'modal_window_filmid' );

				// build the final URL
				var url_imdbfilmid = lumiere_vars.urlpopup_film + '?mid=' + misc_term;

				// Open bootstrap popup link
				jQuery('.modal-header').addClass('showspin');
				jQuery( '.modal-body' ).html( '<object name="' + misc_term + '" data="' + url_imdbfilmid + '"/>' );
				themodal = jQuery( '#theModal' + misc_term ).modal( 'show' );

				themodal.on('shown.bs.modal', function() {
					setTimeout(() => {
						jQuery('.modal-header').removeClass('showspin');
					}, timeout );
				});
			}
		);

	}
);
