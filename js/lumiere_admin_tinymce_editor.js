(function() {

	tinymce.create('tinymce.plugins.Lumiere_tag', {
		/**
		 * This function is meant to allow Lumière! to be used within the tinymce editor
		 * Basically, it adds the tags used by the plugin to display the movie & people popups. 
		 * It replaces the tags with a image when tinymce is used (and do the opposite when switching
		 * to a HTML view, or when posting), which is much more convenient. 
		 *
		 * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
		 * @param {string} url Absolute URL to where the plugin is located.
		 */
		 
		init : function(ed, url) {
			// retrieve data currently selected (if any)
			var imdbTag = this; 
			
			// where the picture to display berfore the tagged word is
			var imdbImg = '<img src="' + lumiere_admin_vars.imdb_path + 'pics/lumiere-ico13x13.png" class="lumiere_admin_tiny_img" width="25" />';

			// add tags to current selection
			ed.addButton('lumiere_tiny', {
				title : 'Lumière! add tags',
				image : lumiere_admin_vars.imdb_path + 'pics/lumiere-ico-noir13x13.png',
				onclick : function() {
				  var selected_text = ed.selection.getContent();
				  var return_text = '';
/*				  return_text = imdbImg + '<span class="lumiere_link_maker">' + selected_text + '</span>';
					// can't get rid of the image anymore, removed */
				  return_text = '<span class="lumiere_link_maker">' + selected_text + '</span>';
				  ed.execCommand('mceInsertContent', 0, return_text);
				}
			});
		},

		setup: function (ed) { /* these functions don't work, something is broken */
			// Replace images with imdb tag
/*			ed.on('PostProcess', function(ed, o) {
				if (o.get)*/
			ed.onPostProcess.add (function(ed, o) {
					o.content = o.content.replace(/<img[^>]+><span class="lumiere_link_maker">/g, '<span class="lumiere_link_maker">');
			});

			// Replace imdb tag with image
/*			ed.on('BeforeSetContent', function(ed, o) { */
			ed.onBeforeSetContent.add(function(ed, o) {
				var imdbImgRep = imdbImg + '<span class="lumiere_link_maker">';
				o.content = o.content.replace(/<span class="lumiere_link_maker">/g, imdbImgRep);
			});	
			
			// Set active buttons if user selected pagebreak or more break
/*			ed.on('NodeChange', function(ed, cm, n) {*/
			ed.onNodeChange.add (function(ed, cm, n) {
				cm.setActive('lumiere_tiny', n.nodeName === 'IMG' && ed.dom.hasClass(n, 'lumiere_admin_tiny_img'));
			});
		},

		/**
		 * Creates control instances based in the incoming name. This method is normally not
		 * needed since the addButton method of the tinymce.Editor class is a more easy way of adding buttons
		 * but you sometimes need to create more complex controls like listboxes, split buttons etc then this
		 * method can be used to create those.
		 *
		 * @param {String} n Name of the control to create.
		 * @param {tinymce.ControlManager} cm Control manager to use inorder to create new control.
		 * @return {tinymce.ui.Control} New control instance or null if no control was created.
		 */
		createControl : function(n, cm) {
			return null;
		},
		
		/**
		 * Returns information about the plugin as a name/value array.
		 *
		 * @return {Object} Name/value array containing information about the plugin.
		 */
		getInfo : function() {
			return {
				longname : "Lumière TinyMCE editor",
				author : 'JCV',
				authorurl : 'https://www.jcvignoli.com/blog',
				infourl : 'https://www.jcvignoli.com/en/lumiere-movies-wordpress-plugin/',
				version : "3.0"
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('lumiere_tiny', tinymce.plugins.Lumiere_tag);
})();
