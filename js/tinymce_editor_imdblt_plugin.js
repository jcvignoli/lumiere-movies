(function() {

	tinymce.create('tinymce.plugins.ImdbQuicktags', {
		/**
		 * This function is meant to allow imdb link transformer to be used within the tinymce editor
		 * Basically, it add the tags used by the plugin to display the imdb windows. It replaces the tags 
		 * with a image when tinymce is used (and do the opposite when switching
		 * to a HTML view, or when posting), which is much more convenient. 
		 *
		 * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
		 * @param {string} url Absolute URL to where the plugin is located.
		 */
		 
		init : function(ed, url) {
			// retrieve data currently selected (if any)
			var imdbTag = this; 
			
			// where the picture to display berfore the tagged word is
			var imdbImg = '<img src="' + url + '/../pics/imdb-link.png" class="imdb-link" width="25" />';

			// add tags to current selection
			ed.addButton('imdb', {
				title : 'IMDB Link Transformer',
				image : url + '/../pics/imdb.gif',
				onclick : function() {
				  var selected_text = ed.selection.getContent();
				  var return_text = '';
				  return_text = imdbImg + '<!--imdb-->' + selected_text + '<!--/imdb-->';
				  ed.execCommand('mceInsertContent', 0, return_text);
				}
			});

			// Replace images with imdb tag
			ed.onPostProcess.add(function(ed, o) {
				if (o.get)
					o.content = o.content.replace(/<img[^>]+><!--imdb-->/g, '<!--imdb-->');
			});

			// Replace imdb tag with image
			ed.onBeforeSetContent.add(function(ed, o) {
				var imdbImgRep = imdbImg + '<!--imdb-->';
				o.content = o.content.replace(/<!--imdb-->/g, imdbImgRep);
			});	
			
			// Set active buttons if user selected pagebreak or more break
			ed.onNodeChange.add(function(ed, cm, n) {
				cm.setActive('imdb', n.nodeName === 'IMG' && ed.dom.hasClass(n, 'imdb-link'));
			});
			
		},

		/**
		 * Creates control instances based in the incomming name. This method is normally not
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
				longname : "Imdb link transformer Quicktags",
				author : 'Jcv',
				authorurl : 'http://www.ikiru.ch/blog',
				infourl : 'http://www.ikiru.ch/blog/imdb-link-transformer-wordpress-plugin/',
				version : "2.2.1"
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('imdb', tinymce.plugins.ImdbQuicktags);
})();
