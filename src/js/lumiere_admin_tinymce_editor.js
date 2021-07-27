(function($) {

	tinymce.create('tinymce.plugins.lumiere_link_maker', {
		/**
		 * This function is meant to allow Lumière! to be used within the tinymce editor
		 * Basically, it adds the tags used by the plugin to display the movie & people popups. 
		 * It replaces the tags with a image when tinymce is used (and do the opposite when switching
		 * to a HTML view, or when posting), which is much more convenient. 
		 *
		 * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
		 * @param {string} url Absolute URL to where the plugin is located.
		 */

		extended_valid_elements: ["+@[data-lum_link_maker]","+@[data-lum_movie_maker]"],

		init : function(ed, url) {
			
			// picture to display berfore the tagged word is
			var lum_icon = '<img src="' + lumiere_admin_vars.imdb_path + 'pics/lumiere-ico-noir13x13.png" class="lumiere_admin_tiny_img" width="13" />';

			// initialise the menu
			var menu = [];

			// add tags to current selection
			ed.addButton('lumiere_tiny', {
				title : 'Lumière! add info',
				image : lumiere_admin_vars.imdb_path + 'pics/lumiere-ico-noir13x13.png',
				type: 'menubutton',
				menu: menu,
				menu: [
				{
					title : 'Add popup span',
					text: 'Turn into popup',
					onclick : function() {
						var selected_text = ed.selection.getContent();
						var content_final = '';
						this.active( !this.active() ); //toggle the button too
						var LumTagActive = this.active();

						if (LumTagActive) {

							content_final = '<span data-lum_link_maker="popup">' + selected_text + '</span>';
							ed.execCommand('mceInsertContent', 0, content_final);

						} else {

							content_final = selected_text;
							content_final = selected_text.replace( /<\/?img(.*)>/ig, '' );
							content_final = ed.selection.setContent(content_final);
							ed.execCommand('mceReplaceContent', 0, content_final);
						}
					},

					onPostRender: function() {
						var _this = this;   // reference to the button itself
						ed.on('NodeChange', function(e) {
							//activate the button if this parent has this class
							var is_active = jQuery( ed.selection).attr('data-lum_link_maker')
							_this.active( is_active );
						})
					}
				}, // end 'Add popup span' menu item

				{
					title : 'Use this title for a movie section based on title',
					text: 'Movie section using movie title',
					onclick : function() {
						var selected_text = ed.selection.getContent();
						var content_final = '';
						this.active( !this.active() );
						var LumTagMovieTitleActive = this.active();

						if (LumTagMovieTitleActive) {

							content_final = '<span data-lum_movie_maker="movie_title">' + selected_text + '</span>';
							ed.execCommand('mceInsertContent', 0, content_final);

						} else {

							content_final = selected_text;
ed.selection.setContent(content_final);
							ed.execCommand('mceReplaceContent', 0, content_final);
						}
					},

					onPostRender: function() {
						var _this = this;   // reference to the button itself
						ed.on('NodeChange', function(e) {
							//activate the button if this parent has this class
							var is_active = jQuery( ed.selection).attr('data-lum_movie_maker');
							_this.active( is_active );
						})
					}
				}, // end 'Movie section using movie title' menu item

				{
					title : 'Use this imdb id for a movie section based on title',
					text: 'Movie section using movie IMDb ID',
					onclick : function() {
						var selected_text = ed.selection.getContent();
						var content_final = '';
						this.active( !this.active() );
						var LumTagMovieIDActive = this.active();

						if (LumTagMovieIDActive) {

							content_final = '<span data-lum_movie_maker="movie_id">' + selected_text + '</span>';
							ed.execCommand('mceInsertContent', 0, content_final);

						} else {

							content_final = selected_text;
							ed.execCommand('mceReplaceContent', 0, content_final);
						}
					},

					onPostRender: function() {
						var _this = this;   // reference to the button itself
						ed.on('NodeChange', function(e) {
							//activate the button if this parent has this class
							var is_active = jQuery( ed.selection).attr('data-lum_movie_maker');
							_this.active( is_active );
						})
					}
				}, // end 'Movie section using movie IMDb ID' menu item
				],// end menu
				}, // end add button
			);
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
		},

	});


	// Register plugin
	tinymce.PluginManager.add('lumiere_tiny', tinymce.plugins.lumiere_link_maker);
})(jQuery);
