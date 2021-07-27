/**
*
* This TinyMCE function createds a visual editor for Lumière! plugin
* It adds an option in the toolbar with Lumière! tools to:
* 1/ Add a link creating a popup in posts
* 2/ Add a movie section based either on movie's title or IMDb ID
* 3/ Open a search window to find IMDb ID
* 
* @author        Lost Highway <https://www.jcvignoli.com/blog>
* @copyright (c) 2021, Lost Highway
*
* @version       2.0
*/
(function($) {

	tinymce.create('tinymce.plugins.lumiere_link_maker', {

		/**
		 * Initialisation of Tinymce
		 *
		 * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
		 * @param {string} url Absolute URL to where the plugin is located.
		 */
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
					text: 'Add a popup',
					image : lumiere_admin_vars.imdb_path + 'pics/lumiere-ico-noir13x13.png',
					onclick : function() {
						var selected_text = ed.selection.getContent();
						ed.windowManager.open({
							width: 300,
							height: 150,
							  title: 'Lumière! Add a link to a popup',
							  body: [
							      {
								   type: 'textbox',
								   name: 'textboxName',
								   label: 'Movie title',
								   value: selected_text,
								},
							],
							onsubmit: function (e) {
								target = '';
								if(e.data.blank === true) {
									target += 'newtab="on"';
								}
								ed.insertContent(' <span data-lum_link_maker="popup">' + e.data.textboxName + '</span> ');
							}
						});
					},
					onPostRender: function() {
						var _this = this;   // reference to the button itself
						ed.on('NodeChange', function(e) {
							//activate the button if this parent has this class
							var is_active = jQuery( ed.selection).attr('data-lum_link_maker');
							_this.active( is_active );
						})
					}
				}, // end 'Add popup span' menu item

				{
					text: 'Add a movie section',
					onclick : function() {
						var selected_text = ed.selection.getContent();
						ed.windowManager.open({
							width: 450,
							height: 200,
							title: 'Lumière! Add a movie section inside the post',
							body: [
								// Movie title/id
								{
									type: 'textbox',
									name: 'movieReference',
									label: 'Movie title/IMDb ID',
									value: selected_text,

								},
								// Dropdown list to select type of movie input
								{
									type: 'listbox', 
				image : lumiere_admin_vars.imdb_path + 'pics/lumiere-ico-noir13x13.png',
									name: 'movieFormat', 
									label: 'My movie is inserted by:', 
									'values': [
											{text: 'By title', value: 'movie_title'},
											{text: 'By id', value: 'movie_id'}
										]
								},
								// Button to open search window
								{
									type: 'button',
									name: 'IMDbID',
									label: 'Find IMDb ID',
									text: 'Open Search Window',
									onclick : function() {
									ed.windowManager.open(
										{
											title: 'Internal Query to find IMDb ID',
											file:  lumiere_admin_vars.wordpress_path + lumiere_admin_vars.gutenberg_search_url,
											width: 500,
											height: 400, 
											 // Whether to use modal dialog instead of separate browser window.
											inline: 1,
										},

										//  Parameters and arguments we want available to the window.
										{
											editor: ed,
											jquery: $,
											valid_value: 'value',
										}
									);// end windowmanager IMDbID
									},// end onclick function
							      },// end button IMDbID
							],// end all buttons
							onsubmit: function (e) {
								target = '';
								if(e.data.blank === true) {
									target += 'newtab="on"';
								}
									ed.insertContent(' <span data-lum_movie_maker="'
										+ e.data.movieFormat
										+ '">' 
										+ e.data.movieReference 
										+ '</span> ',
									);
								}
							});
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
					text: 'Find IMDb ID',
					onclick : function() {
					     ed.windowManager.open(
							{
							title: 'Internal Query to find IMDb ID',
							file:  lumiere_admin_vars.wordpress_path + lumiere_admin_vars.gutenberg_search_url,
							width: 600,
							height: 500,
							// Whether to use modal dialog instead of separate browser window.
							inline: 1,
							},

							//  Parameters and arguments we want available to the window.
							{
								editor: ed,
								jquery: $,
								valid_value: 'value',
							}
						);
					},
				}, // end 'Find IMDb ID'

				],// end menu
			}); // end add button menu
		},// end init
		
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
				version : "4.0"
			};
		},

	});


	// Register plugin
	tinymce.PluginManager.add('lumiere_tiny', tinymce.plugins.lumiere_link_maker);
})(jQuery);
