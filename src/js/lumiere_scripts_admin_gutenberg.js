/* Functions here are Content Security Policy (CSP) Compliant
*  Needs jquery
*  For gutenberg edition pages
*/

/************************************** Gutenberg
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

/* Requires highslide, don't see the point of loading highslide
document.addEventListener('DOMContentLoaded', function () {

	jQuery('a[data-gutenberg]').click(function(){
		// vars from class.core.php
		var url_imdbperso = lumiere_admin_vars.wordpress_admin_path + lumiere_admin_vars.gutenberg_search_url_string;
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
*/
