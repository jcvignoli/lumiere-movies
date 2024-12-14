/**
 * BOOTSRAP popins
 * Function are Content Security Policy (CSP) Compliant
 *
 * (A) Build bootstrap modal window according to the classes clicked
 * This function is triggered on click on data attribute "modal_window(.*)"
 * 1- Extracts info from data-(.*) <a> attribute to build a link inserted into an object, then create the modal
 * 2- Add a spinner with the class .showspinner, which is removed once the object is loaded
 */
document.addEventListener(
	'DOMContentLoaded',
	function () {
		
		/** bootstrap popup **People's name**, data-modal_window_people */

		jQuery( 'a[data-modal_window_people]' ).on(
			'click',
			function(event){

				// Add the class showing the spinner after click
				jQuery('.modal-header').addClass('showspinner');
				
				// get the data-modal_window_people value
				var modal_window_people = jQuery( this ).closest( 'a' ).data( 'modal_window_people' );
				var modal_window_nonce = jQuery( this ).closest( 'a' ).data( 'modal_window_nonce' );
				
				// build the final URL, add a timestamp so no cached result and spinner will be loaded every time
				var url_imdbperso = lumiere_vars.urlpopup_person + '?mid=' + modal_window_people + '&_wpnonce=' + modal_window_nonce;

				// Build the object then activate bootstrap popup link
				jQuery( '.modal-body' ).html( '<object id="' + modal_window_people + '" name="' + modal_window_people + '" data="' + url_imdbperso + '"/>' );
				themodal = jQuery( '#theModal' + modal_window_people ).modal( 'show' );
				
				// Remove the spinner once the object is loaded
				// the bootstrap modal is normaly saved and doesn't detect a second click on the same object; so we disable the cache
				jQuery.ajax({
					url: url_imdbperso, 
					headers: {
						'Cache-Control': 'no-cache, no-store, must-revalidate', 
						'Pragma': 'no-cache', 
						'Expires': '0'
					},
					success: function () {
						jQuery('.modal-header').removeClass('showspinner');
					}
				});
			}
		);

		/** bootstrap popup **Movie by IMDb Title**, data-modal_window_film */

		jQuery( 'a[data-modal_window_film]' ).click(
			function(event){

				// Add the class showing the spinner after click
				jQuery('.modal-header').addClass('showspinner');

				// get the data-modal_window_film value
				var modal_window_film = jQuery( this ).closest( 'a' ).data( 'modal_window_film' );
				var modal_window_nonce = jQuery( this ).closest( 'a' ).data( 'modal_window_nonce' );

				// build the final URL, add a timestamp so no cached result and spinner will be loaded every time
				var url_imdbfilm = lumiere_vars.urlpopup_film + '?film=' + modal_window_film + '&_wpnonce=' + modal_window_nonce;

				// Build the object then activate bootstrap popup link
				jQuery( '.modal-body' ).html( '<object name="' + modal_window_film + '" data="' + url_imdbfilm + '"/>' );
				themodal = jQuery( '#theModal' + modal_window_film ).modal( 'show' );
				
				// Remove the spinner once the object is loaded
				// the bootstrap modal is normaly saved and doesn't detect a second click on the same object; so we disable the cache
				jQuery.ajax({
					url: url_imdbfilm, 
					headers: {
						'Cache-Control': 'no-cache, no-store, must-revalidate', 
						'Pragma': 'no-cache', 
						'Expires': '0'
					},
					success: function () {
						jQuery('.modal-header').removeClass('showspinner');
					}
				});
			}
		);

		/** bootstrap popup **Movie by IMDb ID**, data-modal_window_filmid */

		jQuery( 'a[data-modal_window_filmid]' ).click(
			function(event){

				// Add the class showing the spinner after click
				jQuery('.modal-header').addClass('showspinner');

				// get the data-modal_window_filmid value
				var modal_window_people = jQuery( this ).closest( 'a' ).data( 'modal_window_filmid' );
				var modal_window_nonce = jQuery( this ).closest( 'a' ).data( 'modal_window_nonce' );
				
				// build the final URL, add a timestamp so no cached result and spinner will be loaded every time
				var url_imdbfilmid = lumiere_vars.urlpopup_film + '?mid=' + modal_window_people + '&_wpnonce=' + modal_window_nonce;

				// Build the object then activate bootstrap popup link
				jQuery( '.modal-body' ).html( '<object name="' + modal_window_people + '" data="' + url_imdbfilmid + '"/>' );
				themodal = jQuery( '#theModal' + modal_window_people ).modal( 'show' );

				// Remove the spinner once the object is loaded
				// the bootstrap modal is normaly saved and doesn't detect a second click on the same object; so we disable the cache
				jQuery.ajax({
					url: url_imdbfilmid, 
					headers: {
						'Cache-Control': 'no-cache, no-store, must-revalidate', 
						'Pragma': 'no-cache', 
						'Expires': '0'
					},
					success: function () {
						jQuery('.modal-header').removeClass('showspinner');
					}
				});
			}
		);

	}
);
