( function( blocks, blockEditor, element, components, data, i18n ) {

	var el = element.createElement;
	var elwithhtml = element.RawHTML; /* this type of block can include html */

	var { registerBlockType } = blocks;

	const iconLumiere = el('svg', { width: 35, height: 35, viewBox: "0 0 200 200" },
		el( 'path', 
			{ d: "M10 100 c0 -47 4 -80 10 -80 6 0 10 33 10 80 0 47 -4 80 -10 80 -6 0 -10 -33 -10 -80zM50 170 c0 -5 23 -10 50 -10 28 0 50 5 50 10 0 6 -22 10 -50 10 -27 0 -50 -4 -50 -10zM170 100 c0 -47 4 -80 10 -80 6 0 10 33 10 80 0 47 -4 80 -10 80 -6 0 -10 -33 -10 -80zM50 110 c0 -29 1 -30 50 -30 49 0 50 1 50 30 0 29 -1 30 -50 30 -49 0 -50 -1 -50 -30zM50 35 c0 -11 12 -15 50 -15 38 0 50 4 50 15 0 11 -12 15 -50 15 -38 0 -50 -4 -50 -15z"
			}
		)
	);

	var empty = '';

	registerBlockType(  'lumiere/widget', {  

		title: i18n.__('Lumière Widget', 'lumiere-movies'),
		description: i18n.__('Lumière Widget adds movies to your widgets sidebar', 'lumiere-movies'),
		icon: iconLumiere,
		category: 'widgets',
		attributes: {
			lumiere_input: {
				type: 'string',
				options: 'html',
				default: 'Lumière Movies'
			},
		},
		example: {
			attributes: {
				backgroundColor: '#000000',
				padding: 30,
				textColor: '#FFFFFF',
				radius: 10,
				title: i18n.__('Lumière widget example', 'lumiere-movies'),
			},
		},

		edit: function( props ) {
			return ( 
			el( 'div', 	{ 
					className: props.className,
					tagName: 'div',
					className: 'lumiere_gutenberg_block_widget',
					},

					el( 'img', { 
						className: props.className,
						className: 'lumiere_gutenberg_block_widget-image',
						src: lumiere_admin_vars.imdb_path + 'pics/lumiere-ico-noir80x80.png',
						},
					),// end img

					elwithhtml( { /* this type of block can include html */
						className: props.className,
						className: 'lumiere_gutenberg_block_widget-title',
						children: 'Lumière! Widget',
						},
					),// end h2 title

					elwithhtml( { /* this type of block can include html */
						className: props.className,
						className: 'lumiere_gutenberg_block_widget-explanation',
						tagName: 'gutenberg',
						children: i18n.__('This widget will enable the display of movies in your articles.', 'lumiere-movies')
							+ '<br />'
							+ i18n.__('When editing a post or a page, the movie title or id you enter in your sidebar will be displayed in the widget location.', 'lumiere-movies')
							+ '<br />'
						},
					),// end explanation div

					el( 'div', { 
						className: props.className,
						tagName: 'div',
						className: 'lumiere_gutenberg_block_widget-container',
						},
						el( 'div', {
							className: props.className,
							tagName: 'div',
							className: 'lumiere_gutenberg_block_intothepost-entertitle',
							children: 'Enter widget title:',
							onChange:  function updateType( event ) {
									props.setAttributes({lumiere_input: event.target.value });
								},
						  	},
						),


						el( 'div', {
							className: props.className,
							tagName: 'div',
							className: 'lumiere_gutenberg_block_intothepost-enterinput',
						  	},

							el( 'input', {
								value: props.attributes.lumiere_input,
								className: props.className,
								tagName: 'input',
								className: 'lumiere_gutenberg_block_intothepost-input',

								onChange:  function updateType( event ) {
										props.setAttributes({lumiere_input: event.target.value });
									},
							  	},
							),
						),


					)// end div container
				) // end div intothepost
			); // end return
		},// end function

		save: function( props ) {
			return (
				el( 'div', { className: props.className },
					el( blockEditor.RichText.Content, {
						className: 'lumiere_gutenberg_block_widget-input',
						value: '[lumiereWidget]' + props.attributes.lumiere_input + '[/lumiereWidget]',
					} )
				)
			);
		},

	}); // end registerBlockType()
} ) ( window.wp.blocks, window.wp.blockEditor, window.wp.element, window.wp.components, window.wp.data, window.wp.i18n, );

