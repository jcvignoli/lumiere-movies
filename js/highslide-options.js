/* Settings for highslide */
/* var php_vars.imdb_path passed from wp_localize_script in imdb-link-transformer.php */

var url_highslide = php_vars.imdb_path + "/js/highslide/graphics/";

hs.allowWidthReduction = true;
hs.graphicsDir = url_highslide;
hs.showCredits = false;
hs.outlineType = 'custom';
hs.easing = 'linearTween';
hs.align = 'center';
hs.useBox = true;
hs.registerOverlay(
	{ html: '<div class=\"closebutton\" onclick=\"return hs.close(this)\" title=\"Close\"></div>',
	position: 'top right',
	useOnHtml: true, fade: 2 }
);

