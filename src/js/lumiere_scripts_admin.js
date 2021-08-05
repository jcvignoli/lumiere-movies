/* Functions here are Content Security Policy (CSP) Compliant
*  Needs jquery
*  For admin pages
*/

/************************************** class Widget.php && General.php
*
*/

/* Functions to activate/unactivate forms checkboxes (dependending of the choice made)
*
*/

// If input with data-modificator is selected, get the data-field_to_change (id of the other field to activate or unactivate) and data-field_to_change_value (if activate (1) or unactivate (0))
document.addEventListener('DOMContentLoaded', function () {

	jQuery('input[data-modificator]').change(function(){

	    if(jQuery(this).is(":checked")){
		var optionOne = jQuery(this).closest('input').data('field_to_change');
		var optionTwo = jQuery(this).closest('input').data('field_to_change_value');
		//var finalelement = jQuery(this).closest('input').attr('id'); -> get the id of the input, but currently useless
		GereControle(optionOne, optionTwo);
	    }
	});

	jQuery('input[data-modificator2]').change(function(){
	    if(jQuery(this).is(":checked")){
		var optionOne = jQuery(this).closest('input').data('field_to_change2');
		var optionTwo = jQuery(this).closest('input').data('field_to_change_value2');
		GereControle(optionOne, optionTwo);
	    }
	});

	jQuery('input[data-modificator3]').change(function(){
	    if(jQuery(this).is(":checked")){
		var optionOne = jQuery(this).closest('input').data('field_to_change3');
		var optionTwo = jQuery(this).closest('input').data('field_to_change_value3');
		GereControle(optionOne, optionTwo);
	    }
	});

	jQuery('input[data-valuemodificator]').click(function(){
		var field_option = jQuery(this).closest('input').data('valuemodificator_field');

		if(jQuery(this).is(":checked")){
			document.getElementById (field_option).value = jQuery(this).val();
		} else {
			document.getElementById (field_option).value = jQuery(this).closest('input').data('valuemodificator_default');
		}
	});


	// Enable/disable a field according to the id passed in <input data-fieldid_to_change="">
	jQuery('input[data-checkbox_activate]').change(function(){
		var htmltag_id_to_change = jQuery(this).closest('input').data('checkbox_activate');
		jQuery('#'+htmltag_id_to_change).toggle(jQuery(this).closest('input').is(':checked'));
	});
	jQuery('input[data-checkbox_activate]').trigger('change');

	// Enable/disable a field according to the id passed in <* data-fieldid_to_change="">
	jQuery('[data-field_activate]').click(function(){

		var htmltag_id_to_change = jQuery(this).data('field_activate');

		if  (jQuery(this).is(':checked')) {
			jQuery('#'+htmltag_id_to_change).prop('disabled',false);
		} else{
			jQuery('#'+htmltag_id_to_change).prop('disabled',true);
		}
	});
	jQuery('[data-field_activate]').trigger('change');
});

// Function that activate or unactivate the other field selected previously
function GereControle(Controle, Masquer) {
var objControle = document.getElementById(Controle);
	if (Masquer=='1')
		objControle.disabled=true;
	else
		objControle.disabled=false;
	return true;
}


// Function to move values inside a select box form
// Credits go to Rick Hitchcock https://stackoverflow.com/a/28682653
// Used in options-widget.php

document.addEventListener('DOMContentLoaded', function () {
	jQuery('#movemovieup').click(function() {
		var opt = jQuery('#imdbwidgetorderContainer option:selected');

		if(opt.is(':first-child')) {
			opt.insertAfter(jQuery('#imdbwidgetorderContainer option:last-child'));
		} else {
			opt.insertBefore(opt.prev());
		}
	});

	jQuery('#movemoviedown').click(function() {
		var opt = jQuery('#imdbwidgetorderContainer option:selected');

		if(opt.is(':last-child')) {
			opt.insertBefore(jQuery('#imdbwidgetorderContainer option:first-child'));
		} else {
			opt.insertAfter(opt.next());
		}
	});


	// Get all selected and unselected options from select#imdbwidgetorderContainer
	jQuery('#imdbconfig_save').submit(function () {
		var opt=jQuery('#imdbwidgetorderContainer').find('option');
		opt.prop('selected', true);
	});

});


/************************************** options-cache.php
*
*/

/* Confirmation popup for individual refresh and delete of movies and persons */
/* confirm dialog if attribute "data-confirm" in "a" tag */
(function ($) {
  $(document).on('click', '[data-confirm]',function(e){
	if(!confirm($(this).data('confirm'))){
	  e.stopImmediatePropagation();
	  e.preventDefault();
	}
  });
})(jQuery);

// Functions to check/uncheck all selected fields
//
//

function checkAll(field){
for (i = 0; i < field.length; i++)
	field[i].checked = true ;
}


function uncheckAll(field){
for (i = 0; i < field.length; i++)
	field[i].checked = false ;
}

/* check all inputs */
	/* movies */
	(function ($) {
	  $(document).on('click', 'input[data-check-movies]',function(e){
		checkAll(document.getElementsByName('imdb_cachedeletefor_movies[]'));
	  });
	})(jQuery);
	/* people */
	(function ($) {
	  $(document).on('click', 'input[data-check-people]',function(e){
		checkAll(document.getElementsByName('imdb_cachedeletefor_people[]'));
	  });
	})(jQuery);

/* uncheck all inputs */
	/* movies */
	(function ($) {
	  $(document).on('click', 'input[data-uncheck-movies]',function(e){
		uncheckAll(document.getElementsByName('imdb_cachedeletefor_movies[]'));
	  });
	})(jQuery);
	/* people */
	(function ($) {
	  $(document).on('click', 'input[data-uncheck-people]',function(e){
		uncheckAll(document.getElementsByName('imdb_cachedeletefor_people[]'));
	  });
	})(jQuery);


/************************************** Open a query popup in any admin pages
*
*/

document.addEventListener('DOMContentLoaded', function () {

	jQuery('a[data-lumiere_admin_popup]').click(function(){
		var tmppopupLarg = 540;
		var tmppopupLong = 350;
		var url_imdbperso = lumiere_admin_vars.wordpress_admin_path + lumiere_admin_vars.gutenberg_search_url_string;
		
		// classic popup
		window.open( url_imdbperso, 'popup', 'resizable=yes, toolbar=no, scrollbars=yes, location=no, width='+tmppopupLarg+', height='+tmppopupLong+', top=5, left=5');
	});
});

