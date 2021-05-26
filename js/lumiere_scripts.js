/* Function here are Content Security Policy (CSP) Compliant
*  Needs jquery						
*/

/**** imdb-movie.inc.php
*
*/

jQuery('a#highslide-pic').click(function(){
	return hs.expand(this, { useBox: false 
	});
});

/* FUNCTION: build highslide or classic popup according to the classes
*	This function on click on classes "link-imdblt-(.*)"
	1- extracts info from data-(.*) <a> attribute
	2- builds either a -highslide- or -classic- popup accordingly
*/ 

document.addEventListener('DOMContentLoaded', function () {

	/* highslide popup, people */

	jQuery('a[data-highslidepeople]').click(function(){
		// vars from imdb-link-transformer.php
		var tmppopupLarg = lumiere_vars.popupLarg;
		var tmppopupLong = lumiere_vars.popupLong;
		// var mid from the class data-highslidepeople to build the link
		var misc_term = jQuery(this).closest('a').data('highslidepeople');
		var url_imdbperso = lumiere_vars.imdb_path + 'inc/popup-imdb_person.php?mid=' + misc_term;
		// highslide popup
		return hs.htmlExpand(this, { 
			allowWidthReduction: true,
			objectType: 'iframe', 
			width: tmppopupLarg, 
			objectWidth: tmppopupLarg, 
			objectHeight: tmppopupLong, 
			headingEval: 'this.a.innerHTML', 
			wrapperClassName: 'titlebar', 
			src: url_imdbperso
		});
	});

	/* highslide popup, movie */

	jQuery('a[data-highslidefilm]').click(function(){
		// vars from imdb-link-transformer.php
		var tmppopupLarg = lumiere_vars.popupLarg;
		var tmppopupLong = lumiere_vars.popupLong;
		// var mid from the class data-highslidepeople to build the link
		var misc_term = jQuery(this).closest('a').data('highslidefilm');
		var url_imdbperso = lumiere_vars.imdb_path + 'inc/popup-search.php?film=' + misc_term;
		// highslide popup
		return hs.htmlExpand(this, { 
			allowWidthReduction: true,
			objectType: 'iframe', 
			width: tmppopupLarg, 
			objectWidth: tmppopupLarg, 
			objectHeight: tmppopupLong, 
			headingEval: 'this.a.innerHTML', 
			wrapperClassName: 'titlebar', 
			src: url_imdbperso
		});
	});

	/* classic popup, people */

	jQuery('a[data-classicpeople]').click(function(){
		// vars from imdb-link-transformer.php
		var tmppopupLarg = lumiere_vars.popupLarg;
		var tmppopupLong = lumiere_vars.popupLong;
		// var mid from the class data-highslidepeople to build the link
		var misc_term = jQuery(this).closest('a').data('classicpeople');
		var url_imdbperso = lumiere_vars.imdb_path + 'inc/popup-imdb_person.php?mid=' + misc_term;
		
		// classic popup
		window.open( url_imdbperso, 'popup', 'resizable=yes, toolbar=no, scrollbars=yes, location=no, width='+tmppopupLarg+', height='+tmppopupLong+', top=5, left=5')
	});

	/* classic popup, movie */

	jQuery('a[data-classicfilm]').click(function(){
		// vars from imdb-link-transformer.php
		var tmppopupLarg = lumiere_vars.popupLarg;
		var tmppopupLong = lumiere_vars.popupLong;
		// var mid from the class data-highslidepeople to build the link
		var misc_term = jQuery(this).closest('a').data('classicfilm');
		var url_imdbperso = lumiere_vars.imdb_path + 'inc/popup-search.php?film=' + misc_term;
		
		// classic popup
		window.open( url_imdbperso, 'popup', 'resizable=yes, toolbar=no, scrollbars=yes, location=no, width='+tmppopupLarg+', height='+tmppopupLong+', top=5, left=5');
	});
});

/**** popup-imdb_person.php
*
*/

jQuery('.historyback').click(function(event){
	 event.preventDefault();
	window.history.back();
});


/**** popups all
*
*/

// send close command on click on X of highslide popup
// this is a trick to make highslide CSP compliant
/* doesn't work
document.addEventListener('DOMContentLoaded', function () {
    	jQuery(document).click(function(event) {
		hs.close(event.target);
	});
});
*/
