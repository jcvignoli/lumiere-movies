/**
*
* This TinyMCE function createds a visual editor for Lumière! plugin
* It adds an option in the toolbar with Lumière! tools to:
* 1/ Add a link creating a popup in posts
* 2/ Add a movie section based either on movie's title or IMDb ID
* 3/ Open a search window to find IMDb ID
* 
* It temporary adds a Lumière! icon to spot the Lumière! title or ID currently utilised 
*	for the plugin (the icon is not saved in the database)
*
* @author        Lost Highway <https://www.jcvignoli.com/blog>
* @copyright (c) 2021, Lost Highway
*
* @version       4.0
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
			var lum_image = '<img src="' + lumiere_admin_vars.imdb_path + 'pics/lumiere-ico-noir13x13.png" class="lumiere_admin_tiny_img" width="13" />';

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
								ed.insertContent( ' <span data-lum_link_maker="popup">' + e.data.textboxName + '</span> ');
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
						var query_title = selected_text ? selected_text : '';
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
											file:  lumiere_admin_vars.wordpress_path 
											+ lumiere_admin_vars.gutenberg_search_url 
											+ "?moviesearched=" + query_title,
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
						var selected_text = ed.selection.getContent();
						var query_title = selected_text ? selected_text : '';
						ed.windowManager.open(
							{
							title: 'Internal Query to find IMDb ID',
							file:  lumiere_admin_vars.wordpress_path 
									+ lumiere_admin_vars.gutenberg_search_url 
									+ "?moviesearched=" + query_title,
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
						);
					},
				}, // end 'Find IMDb ID'

				],// end menu
			}); // end add button menu

			// Remove var lum_image
			ed.on('PostProcess', function(o){
				if (o.get)
					o.content = o.content.replace(/<img class="lumiere_admin_tiny_img"[^>]+>/g, '');
			});

			// Add var lum_image to spans (only on display)
			ed.on('BeforeSetContent', function(o){
				var lum_image_span_popup = lum_image + ' <span data-lum_link_maker="popup">';
				var lum_image_span_movie_title = lum_image + ' <span data-lum_movie_maker="movie_title">';
				var lum_image_span_movie_id = lum_image + ' <span data-lum_movie_maker="movie_id">';
				o.content = o.content.replace(/<span data-lum_link_maker=[^>]+>/g, lum_image_span_popup);
				o.content = o.content.replace(/<span data-lum_movie_maker="movie_title">/g, lum_image_span_movie_title);
				o.content = o.content.replace(/<span data-lum_movie_maker="movie_id">/g, lum_image_span_movie_id);
			});	
			
			// Set active buttons if user selected pagebreak or more break
			ed.on('NodeChange', function(cm, n){
				cm.setActive('lumiere_tiny', n.nodeName === 'SPAN' && jQuery( ed.selection).attr('data-lum_link_maker') );
			});

		},// end init

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
