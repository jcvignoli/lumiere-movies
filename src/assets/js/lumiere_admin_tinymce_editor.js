/**
 * Lumière! TinyMCE Plugin
 *
 * This plugin adds a visual editor for the Lumière! WordPress plugin.
 * It provides tools to:
 * 1. Add a link creating a popup in posts.
 * 2. Add a movie/person section based on a title/name or IMDb ID.
 * 3. Open a search window to find IMDb IDs.
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2021, Lost Highway
 *
 * @version       5.0
 * @since 4.6.1 refactored using copilot, simplifed
 */

(function ($) {
	// Register TinyMCE plugin
	tinymce.create('tinymce.plugins.lumiere_link_maker', {
		// Extended valid elements for TinyMCE
		extended_valid_elements: [
			"+@[data-lum_link_maker]",
			"+@[data-lum_movie_maker]"
		],

		/**
		* Initialization of TinyMCE
		* @param {tinymce.Editor} ed Editor instance.
		*/
		init: function (ed) {
			this.setupMenu(ed);
			this.setupEventHandlers(ed);
		},

		/**
		* Setup the Lumière! menu and its items.
		* @param {tinymce.Editor} ed Editor instance.
		*/
		setupMenu: function (ed) {
			const menu = [
				this.createPopupMenuItem(ed),
				this.createMovieSectionMenuItem(ed),
				this.createFindIMDbIDMenuItem(ed)
			];

			// Add the Lumière! button with the menu items
			ed.addButton('lumiere_tiny', {
				title: 'Lumière! add info',
				image: lumiere_admin_vars.lum_path + 'assets/pics/lumiere-ico-noir13x13.png',
				type: 'menubutton',
				menu: menu,
				onPostRender: function () {
					// Save a reference to the button instance
					tinymce.plugins.lumiere_link_maker.buttonReference = this;
				}
			});
		},

		/**
		* Create the "Add a popup link" menu item.
		* @param {tinymce.Editor} ed Editor instance.
		* @returns {Object} Menu item configuration.
		*/
		createPopupMenuItem: function (ed) {
			return {
				text: 'Add a popup link',
				onclick: function () {
					const selectedText = ed.selection.getContent();
					ed.windowManager.open({
					width: 400,
					height: 150,
					title: 'Lumière! Add a link to a popup',
						body: [
							{
								type: 'textbox',
								name: 'textboxName',
								label: 'Movie title',
								value: selectedText,
							}
						],
						onsubmit: function (e) {
						    ed.insertContent(`<span data-lum_link_maker="popup">${e.data.textboxName}</span>`);
						}
					});
				}
			};
		},

		/**
		 * Create the "Add a movie section" menu item.
		 * @param {tinymce.Editor} ed Editor instance.
		 * @returns {Object} Menu item configuration.
		 */
		createMovieSectionMenuItem: function (ed) {
		    return {
			text: 'Add a movie/person section',
			onclick: function () {
			    const selectedText = ed.selection.getContent();
			    ed.windowManager.open({
				width: 450,
				height: 200,
				title: 'Lumière! Add a movie section inside the post',
				body: [
				    {
				        type: 'textbox',
				        name: 'movieReference',
				        label: 'Movie/person title/IMDb ID',
				        value: selectedText,
				    },
				    {
				        type: 'listbox',
				        name: 'movieFormat',
				        label: 'My items are inserted by:',
				        values: [
				            { text: 'By movie title', value: 'lum_movie_title' },
				            { text: 'By movie id', value: 'lum_movie_id' },
				            { text: 'By person name', value: 'lum_person_name' },
				            { text: 'By person id', value: 'lum_person_id' }
				        ]
				    },
				    {
				        type: 'button',
				        name: 'IMDbID',
				        label: 'Find IMDb ID',
				        text: 'Open Search Window',
				        onclick: function () {
				            ed.windowManager.open({
				                title: 'Internal Query to find IMDb ID',
				                file: `${lumiere_admin_vars.wordpress_path}${lumiere_admin_vars.admin_movie_search_url}?${lumiere_admin_vars.admin_movie_search_qstring}=${selectedText}`,
				                width: 800,
				                height: 400,
				                inline: 1
				            });
				        }
				    }
				],
				onsubmit: function (e) {
					ed.insertContent(`<span data-lum_movie_maker="${e.data.movieFormat}">${e.data.movieReference}</span>`);
				}
			    });
			}
		    };
		},

		/**
		* Create the "Find IMDb ID" menu item.
		* @param {tinymce.Editor} ed Editor instance.
		* @returns {Object} Menu item configuration.
		*/
		createFindIMDbIDMenuItem: function (ed) {
			return {
				text: 'Find IMDb ID',
				onclick: function () {
					const selectedText = ed.selection.getContent();
					ed.windowManager.open({
						title: 'Internal Query to find IMDb ID',
						file: `${lumiere_admin_vars.wordpress_path}${lumiere_admin_vars.admin_movie_search_url}?${lumiere_admin_vars.admin_movie_search_qstring}=${selectedText}`,
						width: 700,
						height: 400,
						inline: 1
					});
				}
			};
		},

		/**
		* Setup event handlers for TinyMCE instances.
		* @param {tinymce.Editor} ed Editor instance.
		*/
		setupEventHandlers: function (ed) {
			ed.on('PostProcess', this.removeTemporaryIcons);
			ed.on('BeforeSetContent', this.addTemporaryIcons);
			ed.on('click', this.handleNodeClick.bind(this));
		},

		/**
		 * Remove temporary icons when saving or switching to text view.
		 * @param {Object} event TinyMCE event.
		 */
		removeTemporaryIcons: function (event) {
			if (event.get) {
				event.content = event.content.replace(/<img class="lumiere_admin_tiny_img"[^>]+>\s?\s?/g, '');
			}
		},

		/**
		 * Add temporary icons to spans (display only, not saved).
		 * @param {Object} event TinyMCE event.
		 */
		addTemporaryIcons: function (event) {
			const icon_url = `<img src="${lumiere_admin_vars.lum_path}assets/pics/lumiere-ico-noir13x13.png" class="lumiere_admin_tiny_img" width="13" />&nbsp;`;
			const icons = {
				span_popup: icon_url + '<span data-lum_link_maker="popup">',
				span_movie_title: icon_url + '<span data-lum_movie_maker="lum_movie_title">',
				span_movie_id: icon_url + '<span data-lum_movie_maker="lum_movie_id">',
				span_person_name: icon_url + '<span data-lum_movie_maker="lum_person_name">',
				span_person_id: icon_url + '<span data-lum_movie_maker="lum_person_id">',
			};

			event.content = event.content
				.replace(/<span data-lum_link_maker="popup">/g, icons.span_popup)
				.replace(/<span data-lum_movie_maker="lum_movie_title">/g, icons.span_movie_title)
				.replace(/<span data-lum_movie_maker="lum_movie_id">/g, icons.span_movie_id)
				.replace(/<span data-lum_movie_maker="lum_person_name">/g, icons.span_person_name)
				.replace(/<span data-lum_movie_maker="lum_person_id">/g, icons.span_person_id);
		},

		/**
		 * Handle node click events and activate buttons when needed.
		 * @param {Object} event Click event.
		 */
		handleNodeClick: function (event) {
			const target = $(event.target);
			const isPopup = target.data('lum_link_maker') !== undefined;
			const isMovie = target.data('lum_movie_maker') !== undefined;

			const button = tinymce.plugins.lumiere_link_maker.buttonReference;
			if (button) {
				// Activate the button if the clicked element is relevant
				button.active(isPopup || isMovie);
			}
		},

		/**
		 * Return plugin information.
		 * @returns {Object} Plugin information.
		 */
		getInfo: function () {
			return {
				longname: "Lumière TinyMCE editor",
				version: "5.0"
			};
		}
	});

	// Register the plugin
	tinymce.PluginManager.add('lumiere_tiny', tinymce.plugins.lumiere_link_maker);
})(jQuery);
