// Function to move values inside a select box
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





