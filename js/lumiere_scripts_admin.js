/* Functions here are Content Security Policy (CSP) Compliant
*  Needs jquery
*  For admin pages						
*/

/************************************** options-widget.php
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

/************************************** help.php
*
*/
jQuery(document).ready( function($) {
	// close postboxes that should be closed
	jQuery('.if-js-closed').removeClass('if-js-closed').addClass('closed');

	// postboxes
	postboxes.add_postbox_toggles('imdblt_help');
});

/************************************** Gutenberg
*
*/

document.addEventListener('DOMContentLoaded', function () {

	/* highslide gutenberg, search */

	jQuery('a[data-gutenberg]').click(function(){
		// vars from imdb-link-transformer.php
		var url_imdbperso = lumiere_admin_vars.imdb_path + 'inc/gutenberg-search.php?gutenberg=yes';
		// highslide popup
		return hs.htmlExpand(this, { 
			allowWidthReduction: true,
			objectType: 'iframe', 
			width: tmppopupLarg, 

			headingEval: 'this.a.innerHTML', 
			wrapperClassName: 'titlebar', 
			src: url_imdbperso
		});
	});
});

/************************************** Open a classic popup in admin pages
*
*/

document.addEventListener('DOMContentLoaded', function () {

	jQuery('a[data-lumiere_admin_popup]').click(function(){
		var tmppopupLarg = 540;
		var tmppopupLong = 350;
		var url_imdbperso = lumiere_admin_vars.wordpress_admin_path + 'lumiere/search/?gutenberg=yes';
		
		// classic popup
		window.open( url_imdbperso, 'popup', 'resizable=yes, toolbar=no, scrollbars=yes, location=no, width='+tmppopupLarg+', height='+tmppopupLong+', top=5, left=5');
	});
});


