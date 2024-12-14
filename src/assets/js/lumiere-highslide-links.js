/**
 * Highslide link popup
 * Function here are Content Security Policy (CSP) Compliant
 * Needs jquery
 *
 * FUNCTIONS:
 *
 * (A) On pics click with class lum_pic_inpopup, (inside a popup) trigger an highslide popup
 * (B) On pics click with class lum_pic_link_highslide (into a post), trigger an highslide popup
 * (C)	build highslide popup according to the classes
 *		This function on click on data "modal_window(.*)"
 *		1- extracts info from data-(.*) <a> attribute
 */

document.addEventListener(
	'DOMContentLoaded',
	function () {

		/* Class for a HTML tag a, available in popups only */
		jQuery( '.lum_pic_inpopup' ).click(
			function(){
				return hs.expand(
					this,
					{ useBox: false, captionEval: "Poster of " + 'this.thumb.alt' }
				);
			}
		);

		/* Class for a HTML tag a, available into the post only */
		jQuery( '.lum_pic_link_highslide' ).click(
			function(){
				return hs.expand(
					this,
					{ useBox: false, captionEval: "Poster of " + 'this.thumb.alt' }
				);
			}
		);
	
		// Trying to close with onclick inside the div -- Doesn't work!
		jQuery( '#highslide_button_close' ).click(
			function(e){
				console.log('test');
				hs.close(e);
			}
		);

		/* highslide popup, people */

		jQuery( 'a[data-modal_window_people]' ).click(
			function(){
			
				// vars from scripts
				var tmppopupLarg = lumiere_vars.popupLarg;
				var tmppopupLong = lumiere_vars.popupLong;
				var modal_window_people = jQuery( this ).closest( 'a' ).data( 'modal_window_people' );
				var modal_window_nonce = jQuery( this ).closest( 'a' ).data( 'modal_window_nonce' );
				
				var url_imdbperso = lumiere_vars.urlpopup_person + '?mid=' + modal_window_people + '&_wpnonce=' + modal_window_nonce;

				hs.htmlExpand(
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
			
				// vars from scripts
				var tmppopupLarg = lumiere_vars.popupLarg;
				var tmppopupLong = lumiere_vars.popupLong;
				var modal_window_film = jQuery( this ).closest( 'a' ).data( 'modal_window_film' );
				var modal_window_nonce = jQuery( this ).closest( 'a' ).data( 'modal_window_nonce' );
				
				var url_imdbperso = lumiere_vars.urlpopup_film + '?film=' + modal_window_film + '&_wpnonce=' + modal_window_nonce;

				hs.htmlExpand(
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

				// vars from scripts
				var tmppopupLarg = lumiere_vars.popupLarg;
				var tmppopupLong = lumiere_vars.popupLong;
				var modal_window_filmid = jQuery( this ).closest( 'a' ).data( 'modal_window_filmid' );
				var modal_window_nonce = jQuery( this ).closest( 'a' ).data( 'modal_window_nonce' );
				
				var url_imdbperso = lumiere_vars.urlpopup_film + '?mid=' + modal_window_filmid + '&_wpnonce=' + modal_window_nonce;

				hs.htmlExpand(
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
