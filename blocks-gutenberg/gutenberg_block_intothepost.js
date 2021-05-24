( function( blocks, blockEditor, element, components, data, i18n ) {

	var el = element.createElement;
	var { registerBlockType } = blocks;
	var intro_words = i18n.__( 'Type the name or IMDb movie' , 'lumiere-movies') ;
	var empty = '';
	const iconLumiere = el('svg', { width: 35, height: 35, viewBox: "0 0 200 200" },
		el( 'path', 
			{ d: "M10 100 c0 -47 4 -80 10 -80 6 0 10 33 10 80 0 47 -4 80 -10 80 -6 0 -10 -33 -10 -80zM50 170 c0 -5 23 -10 50 -10 28 0 50 5 50 10 0 6 -22 10 -50 10 -27 0 -50 -4 -50 -10zM170 100 c0 -47 4 -80 10 -80 6 0 10 33 10 80 0 47 -4 80 -10 80 -6 0 -10 -33 -10 -80zM50 110 c0 -29 1 -30 50 -30 49 0 50 1 50 30 0 29 -1 30 -50 30 -49 0 -50 -1 -50 -30zM50 35 c0 -11 12 -15 50 -15 38 0 50 4 50 15 0 11 -12 15 -50 15 -38 0 -50 -4 -50 -15z"
			}
		)
	);

	registerBlockType( 'lumiere/intothepost', {
		title: i18n.__('Lumière: movie inside a post', 'lumiere-movies'),
		icon: iconLumiere,
		category: 'embed',
		keywords: [ 'embed', 'inside post', 'imdb', 'movies', 'film' ],

		attributes: {
			lumiere_imdblt_select: {
				type: 'array',
				default: 'imdblt'
			},
			content: {
				type: 'string',
				default: intro_words
			},
		},
		edit: function( props ) {
			return ( 
				el( 'div', { 
						className: props.className,
						tagName: 'div',
						className: 'lumiere_gutenberg_block_intothepost',
					},
					el( 'div', { 
						className: props.className,
						tagName: 'div',
						className: 'lumiere_gutenberg_block_intothepost-select',
						},
						el( 'select', {
							value: props.attributes.lumiere_imdblt_select,
							onChange:  function updateType( event ) {
									props.setAttributes( { 
										lumiere_imdblt_select: event.target.value 
									});
								},
						  	},
						el("option", {value: "imdblt" }, i18n.__("By movie's name", 'lumiere-movies') ),
						el("option", {value: "imdbltid" }, i18n.__("By IMDb's ID", 'lumiere-movies') ),
						)
					),
					el(	blockEditor.RichText,
							{
							tagName: 'div',
							className: 'lumiere_gutenberg_block_intothepost-content',
							value: props.attributes.content,
							/* find a way to make it work
							onClick: function ( checker ) { 
								if ( checker == intro_words) {
									props.setAttributes( { content: empty });
								}
							},
							*/
							onChange: function( content ) {
								props.setAttributes( { content: content });
							}
						}
					)
				)
			);
		},

		save: function( props ) {
			return (
				el( 'div', { className: props.className },
					el( blockEditor.RichText.Content, {
						className: 'lumiere_gutenberg_block_intothepost-content',
						value: '[' + props.attributes.lumiere_imdblt_select + ']' + props.attributes.content + '[/' + props.attributes.lumiere_imdblt_select + ']',
					} )
				)
			);
		},	

	});
}) ( window.wp.blocks, window.wp.blockEditor, window.wp.element, window.wp.components, window.wp.data, window.wp.i18n, );

