/**
 * Functions here are Content Security Policy (CSP) Compliant
 * Needs jquery
 * For admin pages
 */

/************************************** class-widget.php && admin/submenu/class-main.php
*
*/

/** 
 * Functions to activate/unactivate forms checkboxes (depending on the choice made)
 */

// If input with data-modificator is selected, get the data-field_to_change (id of the other field to activate or unactivate) and data-field_to_change_value (if activate (1) or unactivate (0))
document.addEventListener(
	'DOMContentLoaded',
	function () {

		jQuery( 'input[data-modificator]' ).change(
			function(){

				if (jQuery( this ).is( ":checked" )) {
					var optionOne = jQuery( this ).closest( 'input' ).data( 'field_to_change' );
					var optionTwo = jQuery( this ).closest( 'input' ).data( 'field_to_change_value' );
					//var finalelement = jQuery(this).closest('input').attr('id'); -> get the id of the input, but currently useless
					GereControle( optionOne, optionTwo );
				}
			}
		);

		jQuery( 'input[data-modificator2]' ).change(
			function(){
				if (jQuery( this ).is( ":checked" )) {
					var optionOne = jQuery( this ).closest( 'input' ).data( 'field_to_change2' );
					var optionTwo = jQuery( this ).closest( 'input' ).data( 'field_to_change_value2' );
					GereControle( optionOne, optionTwo );
				}
			}
		);

		jQuery( 'input[data-modificator3]' ).change(
			function(){
				if (jQuery( this ).is( ":checked" )) {
					var optionOne = jQuery( this ).closest( 'input' ).data( 'field_to_change3' );
					var optionTwo = jQuery( this ).closest( 'input' ).data( 'field_to_change_value3' );
					GereControle( optionOne, optionTwo );
				}
			}
		);

		// Enable/disable a field according to the id passed in <input data-fieldid_to_change="">
		jQuery( 'input[data-checkbox_activate]' ).change(
			function(){
				var htmltag_id_to_change = jQuery( this ).closest( 'input' ).data( 'checkbox_activate' );
				jQuery( '#' + htmltag_id_to_change ).toggle( jQuery( this ).closest( 'input' ).is( ':checked' ) );
			}
		);
		jQuery( 'input[data-checkbox_activate]' ).trigger( 'change' );

		// Disable/enable (opposite to previous function) a field according to the id passed in <input data-fieldid_to_change="">
		jQuery( 'input[data-checkbox_deactivate]' ).change(
			function(){
				var htmltag_id_to_change = jQuery( this ).closest( 'input' ).data( 'checkbox_deactivate' );
				jQuery( '#' + htmltag_id_to_change ).toggle( jQuery( this ).closest( 'input' ).is( ':not(:checked)' ) );
			}
		);
		jQuery( 'input[data-checkbox_deactivate]' ).trigger( 'change' );

		// Enable/disable a field according to the id passed in <* data-fieldid_to_change="">
		jQuery( '[data-field_activate]' ).click(
			function(){

				var htmltag_id_to_change = jQuery( this ).data( 'field_activate' );

				if (jQuery( this ).is( ':checked' )) {
					jQuery( '#' + htmltag_id_to_change ).prop( 'disabled',false );
				} else {
					jQuery( '#' + htmltag_id_to_change ).prop( 'disabled',true );
				}
			}
		);
		jQuery( '[data-field_activate]' ).trigger( 'change' );

		/************************************** admin/submenu/class-main.php
		 *
		 */

		// For HTML select imdbpopup_modal_window, display or remove the long/larg options when bootstrap is selected
		jQuery( 'select[name=imdbpopup_modal_window]' ).on(
			'change',
			function(){
				var value = jQuery( this ).val();
				/* @since 4.0.1 removed imdb_imdbpopuplarg that is now displayed in admin main menu, added bootstrap_explain */
				if ( value === 'bootstrap' ) {
					var ele = document.getElementById( 'imdb_imdbpopuplong' );
					ele.style.display = 'none';
					var ele = document.getElementById( 'imdb_popuptheme' );
					ele.style.display = 'none';
					var ele = document.getElementById( 'bootstrap_explain' );
					ele.style.display = 'inline';

				} else {
					var ele = document.getElementById( 'imdb_imdbpopuplarg' );
					ele.style.display = 'inline';
					var ele = document.getElementById( 'imdb_imdbpopuplong' );
					ele.style.display = 'inline';
					var ele = document.getElementById( 'imdb_popuptheme' );
					ele.style.display = 'inline';
					var ele = document.getElementById( 'bootstrap_explain' );
					ele.style.display = 'none';
				}
			}
		);

	}
);

// Function that activates or unactivates the other field selected previously
function GereControle(Controle, Masquer) {
	var objControle = document.getElementById( Controle );
	if (Masquer == '1') {
		objControle.disabled = true;
	} else {
		objControle.disabled = false;
	}
	return true;
}

/************************************** admin/submenu/class-data.php
 *
 */

/** 
 * Function to move values inside a select box form
 * Credits go to Rick Hitchcock https://stackoverflow.com/a/28682653
 * @since 4.6 refactorized with git copilot to be used with various #id
 * Must use now data-container-id such as:
 * <button id="movemovieup" data-container-id="name_id_select">Move Movie Up</button>
 * <button id="movemoviedown" data-container-id="name_id_select">Move Movie Down</button>
 */
document.addEventListener('DOMContentLoaded', function () {

    function moveOptionUp(containerId) {
        var $opt = jQuery('#' + containerId + ' option:selected');
        if ($opt.is(':first-child')) {
            $opt.insertAfter(jQuery('#' + containerId + ' option:last-child'));
        } else {
            $opt.insertBefore($opt.prev());
        }
    }

    function moveOptionDown(containerId) {
        var $opt = jQuery('#' + containerId + ' option:selected');
        if ($opt.is(':last-child')) {
            $opt.insertBefore(jQuery('#' + containerId + ' option:first-child'));
        } else {
            $opt.insertAfter($opt.next());
        }
    }

    function selectAllOptions(containerId) {
        jQuery('#' + containerId + ' option').prop('selected', true);
    }

    jQuery('#movemovieup').click(function () {
        var containerId = jQuery(this).data('container-id');
        moveOptionUp(containerId);
    });

    jQuery('#movemoviedown').click(function () {
        var containerId = jQuery(this).data('container-id');
        moveOptionDown(containerId);
    });

    jQuery('#imdbconfig_save').submit(function () {
        var containerId = jQuery('#movemovieup').data('container-id'); // Assuming the same container ID
        selectAllOptions(containerId);
    });
});


/************************************** admin/submenu/class-cache.php
*
*/

/* Confirmation popup for individual refresh and delete of movies and persons */
/* confirm dialog if attribute "data-confirm" in "a" tag */
(function ($) {
	$( document ).on(
		'click',
		'[data-confirm]',
		function(e){
			if ( ! confirm( $( this ).data( 'confirm' ) )) {
				e.stopImmediatePropagation();
				e.preventDefault();
			}
		}
	);
})( jQuery );

// Functions to check/uncheck all selected fields
//
//

function checkAll(field){
	for (var i = 0, fieldLenght = field.length; i < fieldLenght; i++) {
		field[i].checked = true;
	}
}


function uncheckAll(field){
	for (var i = 0, fieldLenght = field.length; i < fieldLenght; i++) {
		field[i].checked = false;
	}
}

/* check all inputs */
/******** movies */
(function ($) {
	$( document ).on(
		'click',
		'input[data-check-movies]',
		function(e){
			checkAll( document.getElementsByName( 'imdb_cachedeletefor_movies[]' ) );
		}
	);
})( jQuery );
/* people */
(function ($) {
	$( document ).on(
		'click',
		'input[data-check-people]',
		function(e){
			checkAll( document.getElementsByName( 'imdb_cachedeletefor_people[]' ) );
		}
	);
})( jQuery );

/******** uncheck all inputs */
/* movies */
(function ($) {
	$( document ).on(
		'click',
		'input[data-uncheck-movies]',
		function(e){
			uncheckAll( document.getElementsByName( 'imdb_cachedeletefor_movies[]' ) );
		}
	);
})( jQuery );
/* people */
(function ($) {
	$( document ).on(
		'click',
		'input[data-uncheck-people]',
		function(e){
			uncheckAll( document.getElementsByName( 'imdb_cachedeletefor_people[]' ) );
		}
	);
})( jQuery );

document.addEventListener(
	'DOMContentLoaded',
	function () {
		/**
		 * Clicking on a checkbox with 'data-valuemodificator' changes another field value (the field is set up in valuemodificator_field )
		 * Two values (value in value="XXX") and 'data-valuemodificator_valuedefault' will be sent to that field value according to the click (checked)
		 */
		jQuery( 'input[data-valuemodificator]' ).click(
			function(){
				var field_option = jQuery( this ).closest( 'input' ).data( 'valuemodificator_field' );

				if (jQuery( this ).is( ":checked" )) {
					document.getElementById( field_option ).value = jQuery( this ).val();
				} else {
					document.getElementById( field_option ).value = jQuery( this ).closest( 'input' ).data( 'valuemodificator_default' );
				}
			}
		);

		/**
		 * Clicking on a checkbox with 'data-valuemodificator_advanced' changes another field value (the field is set up in valuemodificator_field )
		 * Two values 'data-valuemodificator_valuecurrent' and 'data-valuemodificator_valuedefault' will be sent to that field value according to the click (checked)
		 */
		jQuery( 'input[data-valuemodificator_advanced]' ).click(
			function(){
				var field_option = jQuery( this ).closest( 'input' ).data( 'valuemodificator_field' );

				if (jQuery( this ).is( ":checked" )) {
					document.getElementById( field_option ).value = jQuery( this ).closest( 'input' ).data( 'valuemodificator_valuecurrent' );
				} else {
					document.getElementById( field_option ).value = jQuery( this ).closest( 'input' ).data( 'valuemodificator_valuedefault' );
				}
			}
		);
	}
);

/**
 ************************************ Open a query popup in any admin pages
 */
document.addEventListener(
	'DOMContentLoaded',
	function () {
		jQuery( 'a[data-lumiere_admin_search_popup]' ).click(
			function(){
				// Vars from Settings class, transmitted in script lumiere_scripts_admin_vars from class Admin
				var tmppopupLarg = lumiere_admin_vars.popupLarg;
				var tmppopupLong = lumiere_admin_vars.popupLong;
				var url_imdbperso = lumiere_admin_vars.wordpress_path + lumiere_admin_vars.admin_movie_search_url;

				// classic javascript popup
				window.open( url_imdbperso, 'popup', 'resizable=yes, toolbar=no, scrollbars=yes, location=no, width=' + tmppopupLarg + ', height=' + tmppopupLong + ', top=100, left=100' );
			}
		);
	}
);
