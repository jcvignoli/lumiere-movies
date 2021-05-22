/* Settings for highslide */
/* var highslide_vars.imdb_path passed from wp_localize_script in imdb-link-transformer.php */

var url_highslide = highslide_vars.imdb_path + "js/highslide/graphics/";

hs.allowWidthReduction = true;
hs.graphicsDir = url_highslide;
hs.showCredits = false;
hs.outlineType = 'custom';
hs.easing = 'linearTween';
hs.align = 'center';
hs.useBox = true;
hs.registerOverlay(
/* bad javascrit, inline should be avoided; next line, and the function hs.Expander below, coupled to lumiere_script's jquery, simulate a click by sending an info once a div is clicked, then retried in lumiere_script.
	{ html: '<div class=\"closebutton\" onclick=\"return hs.close(this)\" title=\"Close\"></div>',*/

	{ html: '<div class=\"closebutton\" title=\"Close\"></div>',
	position: 'top right',
	useOnHtml: true, fade: 2 }
);

/* function to send info to javascript even after the window popped up, which is not possible otherwise */
hs.Expander.prototype.onAfterExpand = function (sender) {
    jQuery('<div  class="closebutton" title="fermer"></div>').css({

        cursor: 'pointer',
	background: 'url('+url_highslide+'close.png)', // adjust the path if necessary
        zIndex: 20
    }).appendTo(sender.wrapper);
};

