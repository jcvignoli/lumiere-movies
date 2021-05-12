/* Former inline function passed here to be Content Security Policy (CSP) Compliant
*  Needs jquery
*  For admin pages						
*/

/**** options-widget.php
*
*/




/**** options-cache.php
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

/* check all inputs */
	/* movies */
	(function ($) {
	  $(document).on('click', 'input[data-check]',function(e){
		checkAll(document.getElementsByName('imdb_cachedeletefor[]'));
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
	  $(document).on('click', 'input[data-uncheck]',function(e){
		uncheckAll(document.getElementsByName('imdb_cachedeletefor[]'));
	  });
	})(jQuery);
	/* people */
	(function ($) {
	  $(document).on('click', 'input[data-uncheck-people]',function(e){
		uncheckAll(document.getElementsByName('imdb_cachedeletefor_people[]'));
	  });
	})(jQuery);

/**** help.php
*
*/
jQuery(document).ready( function($) {
	// close postboxes that should be closed
	jQuery('.if-js-closed').removeClass('if-js-closed').addClass('closed');

	// postboxes
	postboxes.add_postbox_toggles('imdblt_help');
});
