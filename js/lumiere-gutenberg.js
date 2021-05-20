( function( blocks, element ) {

	const el = element.createElement;

	//const { RichText } = editor;
	const { registerBlockType } = blocks;



	const iconLumiere = el('svg', { width: 20, height: 20 },
		el( 'path',
			{
				d: "M10 100 c0 -47 4 -80 10 -80 6 0 10 33 10 80 0 47 -4 80 -10 80 -6 0 -10 -33 -10 -80zM50 170 c0 -5 23 -10 50 -10 28 0 50 5 50 10 0 6 -22 10 -50 10 -27 0 -50 -4 -50 -10zM170 100 c0 -47 4 -80 10 -80 6 0 10 33 10 80 0 47 -4 80 -10 80 -6 0 -10 -33 -10 -80zM50 110 c0 -29 1 -30 50 -30 49 0 50 1 50 30 0 29 -1 30 -50 30 -49 0 -50 -1 -50 -30zM50 35 c0 -11 12 -15 50 -15 38 0 50 4 50 15 0 11 -12 15 -50 15 -38 0 -50 -4 -50 -15z"
			}
		)
	);


	registerBlockType( 'lumiere/lumiere', {
		title: 'Lumiere block',
		icon: iconLumiere,
		category: 'embed',
		keywords: [ 'lumiere', 'imdb', 'movies' ],

		// The "edit" property must be a valid function.
		edit: function( props ) {
			return (
				el( 'div', { className: props.className },
					el( 'div', { className: 'lumiere-block-wrap' },
						el( 'div', {},
							'Enter your email address'
						),
						el( 'div', {},
							'Subscribe'
						)
					)
				)
			);
		},

		save: function( props ) {
			return (
				el( 'div', { className: props.className },
					el( 'form', { className: 'lumiere-block-wrap' },
						el( 'input', { 'type': 'email', 'placeholder' : 'Enter your email address' } ),
						el( 'button', {}, 'Subscribe' )
					)
				)
			);
		},
	} );
} )(
	window.wp.blocks,
	window.wp.element,
);
