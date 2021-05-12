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

function GereControleInverse(Controleur, Controle, Masquer) {
var objControleur = document.getElementById(Controleur);
var objControle = document.getElementById(Controle);
	if (Masquer=='1')
		objControle.style.visibility=(objControleur.checked==false)?'visible':'hidden';
	else
		objControle.disabled=(objControleur.checked==false)?false:true;
	return true;
}

function checkAll(field){
for (i = 0; i < field.length; i++)
	field[i].checked = true ;
}


function uncheckAll(field){
for (i = 0; i < field.length; i++)
	field[i].checked = false ;
}

function imdb_pilot_imdbfill_GereControle(valeurSelect, Controleur, Controle, Masquer) {
var objControleur = document.getElementById(Controleur);
var objControle = document.getElementById(Controle);
	if (valeurSelect == 'FULL_ACCESS') {
		if (Masquer=='1')
			objControle.style.visibility=(objControleur.checked==true)?'visible':'hidden';
		else
			objControle.disabled=(objControleur.checked==true)?false:true;
		return true;
	} 
}
