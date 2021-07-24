( function( blocks, blockEditor, element, components, data, i18n ) {

	var el = element.createElement;
	var elwithhtml = element.RawHTML; /* this type of block can include html */

	var { registerBlockType } = blocks;

	const iconLumiere = el('svg', { width: 35, height: 35, viewBox: "0 0 200 200" },
		el( 'path', 
			{ d: "M10 100 c0 -47 4 -80 10 -80 6 0 10 33 10 80 0 47 -4 80 -10 80 -6 0 -10 -33 -10 -80z M50 170 c0 -5 23 -10 50 -10 28 0 50 5 50 10 0 6 -22 10 -50 10 -27 0 -50 -4 -50 -10z M170 100 c0 -47 4 -80 10 -80 6 0 10 33 10 80 0 47 -4 80 -10 80 -6 0 -10 -33 -10 -80z M50 110 c0 -29 1 -30 50 -30 49 0 50 1 50 30 0 29 -1 30 -50 30 -49 0 -50 -1 -50 -30z M57 44 c-17 -17 -4 -24 44 -24 36 0 49 4 47 13 -5 13 -79 22 -91 11z"
			}
		)
	);

	registerBlockType(  'lumiere/widget', {  

		title: i18n.__('Lumière! Widget', 'lumiere-movies'),
		description: i18n.__('Lumière Widget adds movies to your widgets sidebar and enhance your posts about cinema.', 'lumiere-movies'),
		icon: iconLumiere,
		category: 'widgets',
		keywords: [ 'widget', 'lumiere', 'imdb', 'movie', 'film' ],
		attributes: {
			lumiere_input: {
				type: 'string',
				options: 'html',
				default: 'Lumière Movies'
			},
		},
/*
		example: {
			attributes: {
				backgroundColor: '#000000',
				padding: 30,
				textColor: '#FFFFFF',
				radius: 10,
				title: i18n.__('Lumière widget example', 'lumiere-movies'),
			},
		},
*/
		edit: function( props ) {
			return ( 
			el( 'div', 	{ 
					className: props.className,
					tagName: 'div',
					className: 'lumiere_block_widget',
					},

					el( 'img', { 
						className: props.className,
						className: 'lumiere_block_widget_image',
						src: lumiere_admin_vars.imdb_path + 'pics/lumiere-ico80x80.png',
						},
					),// end img

					elwithhtml( { /* this type of block can include html */
						className: props.className,
						className: 'lumiere_block_widget_title',
						children: 'Lumière! Widget',
						},
					),// end h2 title

					elwithhtml( { /* this type of block can include html */
						className: props.className,
						className: 'lumiere_block_widget_explanation',
						tagName: 'gutenberg',
						children: i18n.__('This widget will display movies in your articles.', 'lumiere-movies')
							+ '<br />'
							+ i18n.__('When editing a post or a page, simply add a movie title or id using the Lumière tool in your sidebar to show a movie.', 'lumiere-movies')
							+ '<br />'
						},
					),// end explanation div

					el( 'div', { 
						className: props.className,
						tagName: 'div',
						className: 'lumiere_block_widget_container',
						},
						el( 'div', {
							className: props.className,
							tagName: 'div',
							className: 'lumiere_block_widget_entertitle',
							children: 'Enter widget title:',
							onChange:  function updateType( event ) {
									props.setAttributes({lumiere_input: event.target.value });
								},
						  	},
						),


						el( 'div', {
							className: props.className,
							tagName: 'div',
							className: 'lumiere_block_widget_enterinput',
						  	},

							el( 'input', {
								value: props.attributes.lumiere_input,
								className: props.className,
								tagName: 'input',
								className: 'lumiere_block_widget_input',

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
						className: 'lumiere_block_widget_input',
						value: '[lumiereWidget]' + props.attributes.lumiere_input + '[/lumiereWidget]',
					} )
				)
			);
		},

	}); // end registerBlockType()
} ) ( window.wp.blocks, window.wp.blockEditor, window.wp.element, window.wp.components, window.wp.data, window.wp.i18n, );

