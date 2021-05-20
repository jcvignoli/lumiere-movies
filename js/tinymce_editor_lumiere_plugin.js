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
			var imdbImg = '<img src="' + php_vars.imdb_path + 'pics/imdb-link.png" class="imdb-link" width="25" />';

			// add tags to current selection
			ed.addButton('lumiere_tiny', {
				title : 'Lumière! tags add',
				image : php_vars.imdb_path + 'pics/lumiere-ico13x13.png',
				onclick : function() {
				  var selected_text = ed.selection.getContent();
				  var return_text = '';
				  return_text = imdbImg + '<!--imdb-->' + selected_text + '<!--/imdb-->';
				  ed.execCommand('mceInsertContent', 0, return_text);
				}
			});


			
		},

		setup: function (ed) {
			// Replace images with imdb tag
			ed.on('PostProcess', function(ed, o) {
				if (o.get)
					o.content = o.content.replace(/<img[^>]+><!--imdb-->/g, '<!--imdb-->');
			});

			// Replace imdb tag with image
			ed.on('BeforeSetContent', function(ed, o) {
				var imdbImgRep = imdbImg + '<!--imdb-->';
				o.content = o.content.replace(/<!--imdb-->/g, imdbImgRep);
			});	
			
			// Set active buttons if user selected pagebreak or more break
			ed.on('NodeChange', function(ed, cm, n) {
				cm.setActive('imdblumiere_tiny', n.nodeName === 'IMG' && ed.dom.hasClass(n, 'imdb-link'));
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
				longname : "Lumière Movies RichEditor",
				author : 'JCV',
				authorurl : 'https://www.jcvignoli.com/blog',
				infourl : 'https://www.jcvignoli.com/lumiere-movies-wordpress-plugin/',
				version : "3.0"
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('lumiere_tiny', tinymce.plugins.Lumiere_tag);
})();
