/* Settings for highslide */
/* var lumiere_vars.imdb_path passed from wp_localize_script to class-movie.php */

var url_highslide = lumiere_vars.imdb_path + "js/highslide/graphics/";
var popup_border_colour = lumiere_vars.popup_border_colour;

hs.allowWidthReduction = true;
hs.graphicsDir = url_highslide;
hs.showCredits = false;
hs.outlineType = 'custom';
hs.easing = 'linearTween';
hs.align = 'center';
hs.useBox = true;
hs.skin.contentWrapper =
	'<div class="highslide-header highslide-move" style="background-color:' + popup_border_colour + ';"></div>' +
	'<div class="highslide-body highslide-move" style="background-color:' + popup_border_colour + ';"></div>' +
	'<div class="highslide-footer highslide-resize" style="background-color:' + popup_border_colour + ';"><div>';

hs.registerOverlay(
/* bad javascript, inline should be avoided; next line, and the function hs.Expander below, coupled to lumiere_script's jquery, simulate a click by sending an info once a div is clicked, then retried in lumiere_script.*/
	{ html: '<div class=\"closebutton\" onclick=\"return hs.close(this)\" title=\"Close\"></div>',

		/* doesn't work { html: '<div class=\"closebutton\" title=\"Close\"></div>',*/
		position: 'top right',
		useOnHtml: true, fade: 2 }
);

/* function to send info to javascript even after the window popped up, which is not possible otherwise */

/* doesn't work
hs.Expander.prototype.onAfterExpand = function (sender) {
	jQuery('<div  class="closebutton" title="fermer"></div>').css({

		cursor: 'pointer',
	background: 'url('+url_highslide+'close.png)', // adjust the path if necessary
		zIndex: 20
	}).appendTo(sender.wrapper);
};
*/
