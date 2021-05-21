/* Settings for highslide */
/* var highslide_vars.imdb_path passed from wp_localize_script in imdb-link-transformer.php */

var url_highslide = highslide_vars.imdb_path + "/js/highslide/graphics/";

hs.allowWidthReduction = true;
hs.graphicsDir = url_highslide;
hs.showCredits = false;
hs.outlineType = 'custom';
hs.easing = 'linearTween';
hs.align = 'center';
hs.useBox = true;
hs.registerOverlay(
	{ html: '<div class=\"closebutton\" onclick=\"return hs.close(this)\" data-highslide_close=\"yes\" title=\"Close\"></div>',
	position: 'top right',
	useOnHtml: true, fade: 2 }
);

/* function to send info to javascript even after the window popped up, which is not possible otherwise
hs.Expander.prototype.onAfterExpand = function (sender) {
    jQuery('<div class="closebutton" data-highslide_close="yes" title="Close"></div>').css({
        position: 'absolute',
        top: '-15px',
        right: '-15px',
        height: '30px',
        width: '30px',
        cursor: 'pointer',
        zIndex: 20
    }).appendTo(sender.wrapper);
};
*/
