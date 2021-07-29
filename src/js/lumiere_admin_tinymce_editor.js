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

		extended_valid_elements: ["+@[data-lum_link_maker]","+@[data-lum_movie_maker]" ],

		/**
		 * Initialisation of Tinymce
		 *
		 * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
		 * @param {string} url Absolute URL to where the plugin is located.
		 */
		init : function(ed, url) {

			// Var to save temporary 'THIS' inside the button and use it later
			var temp_this = null;

			// Full URL for Querying /wp-admin/lumiere/search
			var gutenberg_url = lumiere_admin_vars.wordpress_path 
							+ lumiere_admin_vars.gutenberg_search_url 
							+ "?moviesearched=";
			
			// ICON to display before tagged words
			var lum_image = '<img src="' 
						+ lumiere_admin_vars.imdb_path 
						+ 'pics/lumiere-ico-noir13x13.png" class="lumiere_admin_tiny_img" width="13" />';

			// SPANS
			var data_popup_key = 'lum_link_maker';
			var span_popup = '<span data-' + data_popup_key + '="popup">';
			var data_movie_key = 'lum_movie_maker';
			var span_movie_attr = 'data-'+data_movie_key;
			var span_movie_begin = '<span ' + span_movie_attr + '="';
				var span_movie_title = span_movie_begin + 'movie_title">';
				var span_movie_id = span_movie_begin + 'movie_id">';

			// Initialise MENU
			var menu = [];

			// Build the MENU
			ed.addButton('lumiere_tiny', {
				title : 'Lumière! add info',
				image : lumiere_admin_vars.imdb_path + 'pics/lumiere-ico-noir13x13.png',
				type: 'menubutton',
				menu: menu,
				menu: [
				{
					text: 'Add a popup link',
					onclick : function() {
						this.active( !this.active() );
						var LumTagActive = this.active();
						var selected_text = ed.selection.getContent();
						ed.windowManager.open({
							width: 400,
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
								ed.insertContent( ' ' + span_popup + e.data.textboxName + '</span> ');
							}
						});
					},
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
											file:  gutenberg_url + query_title,
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
									ed.insertContent(' ' + span_movie_begin
										+ e.data.movieFormat
										+ '">' 
										+ e.data.movieReference 
										+ '</span> ',
									);
								}
							});
					},
				}, // end 'Movie section using movie title' menu item

				{
					text: 'Find IMDb ID',
					onclick : function() {
						var selected_text = ed.selection.getContent();
						var query_title = selected_text ? selected_text : '';
						ed.windowManager.open(
							{
							title: 'Internal Query to find IMDb ID',
							file:  gutenberg_url + query_title,
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

				onPostRender: function(e) {

					/* Save value of 'this' for later use */
					temp_this = this; 

				},

				onclick : function(e) {

					this.active( !this.active() ); 
					var LumActive = this.active();

					// Execute only if active
					//if (LumActive) {
						//console.log(old_text+' added');
					//} else {
						/* On menu click, remove the span of current selection
						 * This works only if the entire span is selected by the user
						 */
						/* Deactivated, removes the possibility to get selected text in the windows
						old_text = ed.selection.getContent();
						new_text = old_text.replace(/<span data-lum_[^>]+>(.+)<\/span>/, '$1');
						ed.selection.setContent(new_text);
						//console.log('current selection: ' + old_text + ' deleted');
						*/
					// }
				},

			}); // end add button menu

			// Remove var lum_image when saving or switching to text view
			ed.on('PostProcess', function(o){
				if (o.get) {
					// Adding two optional blanks in replacement to make sure everything is removed
					o.content = o.content.replace(/<img class="lumiere_admin_tiny_img"[^>]+>\s?\s?/g, '');
				}
			});

			// Add var lum_image to spans (on display only, not saved)
			ed.on('BeforeSetContent', function(e){
				// Build vars lum_image + spans
				var lum_image_span_popup = lum_image + '&nbsp;' + span_popup;
				var lum_image_span_movie_title = lum_image + '&nbsp;' + span_movie_title;
				var lum_image_span_movie_id = lum_image + '&nbsp;' + span_movie_id;
				// Build vars to be replace, to include 'g' which is match all cases
				var span_popup_replace = new RegExp("(" + span_popup + ")", "g");
				var span_movie_title_replace = new RegExp("(" + span_movie_title + ")", "g");
				var span_movie_id_replace = new RegExp("(" + span_movie_id + ")", "g");
				// Replace the content
				e.content = e.content.replace(span_popup_replace, lum_image_span_popup);
				e.content = e.content.replace(span_movie_title_replace, lum_image_span_movie_title);
				e.content = e.content.replace(span_movie_id_replace, lum_image_span_movie_id);
			});

			// Activate Lumière button in toolbar depending on what type of node is clicked
			ed.on('click', function(e){

				//var outer = $(e.target)[0].outerHTML;
				//console.log('11'+outer);

				/* Select only when target node includes specific 'data-key=""' attribute */
				click_popup = $( e.target ).data( data_popup_key );
				click_movie = $( e.target ).data( data_movie_key );

				/* Var retrieved from onPostRender inside 'lumiere_tiny' button  */
				var _this = temp_this; 

				if ( (click_popup) && _this.active( _this.active() ) ) {
					_this.active( _this.active(false) );
					//console.log("activated " + click_popup + _this);
				} else if ( (click_movie) && _this.active( _this.active() ) ) {
					_this.active( _this.active(false) );
					//console.log("activated " + click_movie + _this);
				} else {
					!_this.active( _this.active(true) );
					//console.log("disabled" + _this);
				};
			});


		},// end init

		setup: function (ed) { /* Doesn't work */
			// Set active buttons if user selected SPAN
			ed.on('NodeChanged', function(cm, n){
				console.log('node changed');
				cm.setActive('lumiere_tiny', n.nodeName === 'SPAN'  );
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
