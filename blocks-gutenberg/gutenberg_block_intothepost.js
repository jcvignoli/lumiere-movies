( function( blocks, blockEditor, element ) {

	const el = element.createElement;
	const { registerBlockType } = blocks;
	const iconLumiere = el('svg', { width: 35, height: 35, viewBox: "0 0 200 200" },
		el( 'path', 
			{ d: "M10 100 c0 -47 4 -80 10 -80 6 0 10 33 10 80 0 47 -4 80 -10 80 -6 0 -10 -33 -10 -80zM50 170 c0 -5 23 -10 50 -10 28 0 50 5 50 10 0 6 -22 10 -50 10 -27 0 -50 -4 -50 -10zM170 100 c0 -47 4 -80 10 -80 6 0 10 33 10 80 0 47 -4 80 -10 80 -6 0 -10 -33 -10 -80zM50 110 c0 -29 1 -30 50 -30 49 0 50 1 50 30 0 29 -1 30 -50 30 -49 0 -50 -1 -50 -30zM50 35 c0 -11 12 -15 50 -15 38 0 50 4 50 15 0 11 -12 15 -50 15 -38 0 -50 -4 -50 -15z"
			}
		)
	);

	registerBlockType( 'lumiere/intothepost', {
		title: 'Lumiere: movie inside a post',
		icon: iconLumiere,
		category: 'embed',
		keywords: [ 'lumiere', 'embed', 'inside post', 'imdb', 'movies' ],

		attributes: {
			content: {
				type: 'string',
				default: 'Insert movie\'s name' 
			},
		},
		edit: function( props ) {
			return ( 
				el( 'div', { className: props.className },
					el(
						blockEditor.RichText,
							{
							tagName: 'div',
							className: 'lumiere_gutenberg_block_intothepost-content',
							value: props.attributes.content,
							onChange: function( content ) {
								props.setAttributes( { content: content } );
							}
						}
					),
				)
			);
		},

		save: function( props ) {
			return (
				el( 'div', { className: props.className },
					el( blockEditor.RichText.Content, {
						className: 'lumiere_gutenberg_block_intothepost-content',
						value: '[imdblt]' + props.attributes.content + '[/imdblt]',
					} )
				)
			);
		},	

	});
}) ( window.wp.blocks, window.wp.blockEditor, window.wp.element );

