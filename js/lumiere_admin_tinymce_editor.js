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
				ed.dom.toggleClass( ed.selection.getNode(), 'lumiere_link_maker' );
				this.active( !this.active() ); //toggle the button too
				var LumTagActive = this.active();

					if (LumTagActive) {
//						return_text = imdbImg + '<span class="lumiere_link_maker">' + selected_text + '</span>';
						return_text = '<span class="lumiere_link_maker">' + selected_text + '</span>';
						ed.execCommand('mceInsertContent', 0, return_text);
					} else {
						return_text = selected_text;
						ed.selection.setContent(selected_text);
					}
				},

				onPostRender: function() {
					var _this = this;   // reference to the button itself
					ed.on('NodeChange', function(e) {
						//activate the button if this parent has this class
						var is_active = jQuery( ed.selection.getNode() ).hasClass('lumiere_link_maker');
						_this.active( is_active );
					})
				}
			});
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
