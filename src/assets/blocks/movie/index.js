// Wrapping it into a function allows to have a unique constant definition in this file
( ( wp ) => {

	const el = wp.element.createElement;
	const elWithHTML = wp.element.RawHTML; /* this type of block can include html */
	const { registerBlockType } = wp.blocks;
	const { blockProps } = wp.blockEditor.useBlockProps;
	const { blockPropsSave } = wp.blockEditor.useBlockProps.save;
	const RichText = wp.blockEditor.RichText;
	const RichContent = wp.blockEditor.RichText.Content;
	const __ = wp.i18n.__;

	var intro_words = __( 'Enter the name or the IMDb ID movie' , 'lumiere-movies' );
	var empty = '';
	var iconLumiere = el(
		'svg',
		{ width: 35, height: 35, viewBox: "0 0 200 200" },
		el(
			'path',
			{ d: "M10 100 c0 -47 4 -80 10 -80 6 0 10 33 10 80 0 47 -4 80 -10 80 -6 0 -10 -33 -10 -80zM50 170 c0 -5 23 -10 50 -10 28 0 50 5 50 10 0 6 -22 10 -50 10 -27 0 -50 -4 -50 -10zM170 100 c0 -47 4 -80 10 -80 6 0 10 33 10 80 0 47 -4 80 -10 80 -6 0 -10 -33 -10 -80zM50 110 c0 -29 1 -30 50 -30 49 0 50 1 50 30 0 29 -1 30 -50 30 -49 0 -50 -1 -50 -30zM50 35 c0 -11 12 -15 50 -15 38 0 50 4 50 15 0 11 -12 15 -50 15 -38 0 -50 -4 -50 -15z"
			}
		)
	);
	var linkPopup = '<a data-lumiere_admin_search_popup="noInfoNeeded" class="link-imdblt-highslidepeople" target="_blank">' + __( 'IMDb movie id' , 'lumiere-movies' ) + '</a>';

	registerBlockType(
		'lumiere/main', {
			title: __( 'Add a movie into your post', 'lumiere-movies' ),
			description: __( 'Add a block in your posts that displays movie data.', 'lumiere-movies' ),
			icon: iconLumiere,
			category: 'embed',
			keywords: [ 'lumiere', 'imdb', 'movies', 'film' ],
			example: {},
			attributes: {
				lumiere_imdblt_select: {
					type: 'string',
					options: 'html',
					default: 'movie_title'
				},
				content: {
					type: 'string',
					default: intro_words
				},
			},
			edit: ( blockProps ) => {
				return (
					el(
						'div', {
							className: blockProps.className,
							tagName: 'div',
							className: 'wp-block',
						},
						el(
							'div', {
								className: blockProps.className,
								tagName: 'div',
								className: 'lumiere_block_intothepost',
							},
							el(
								'img', {
									className: blockProps.className,
									className: 'lumiere_block_intothepost-image',
									src: lumiere_admin_vars.ico80,
									},
							),
							elWithHTML( { /* this type of block can include html */
								className: blockProps.className,
								className: 'lumiere_block_intothepost-title',
								children: 'Lumière! movies',
							},),// end h2 title
							elWithHTML( { /* this type of block can include html */
								className: blockProps.className,
								className: 'lumiere_block_intothepost-explanation',
								tagName: 'gutenberg',
								children: __( 'This block is visible only in your admin area. In your blog frontpage, it will be replaced by the movie you selected here.' , 'lumiere-movies' )
									+ '<br />'
									+ __( '"By Movie title": You can just enter the movie name.' , 'lumiere-movies' )
									+ '<br />'
									+ __( '"By Movie ID": you can get the' , 'lumiere-movies' )
									+ ' ' + linkPopup + ' '
									+ __( 'or type your movie name and select "Open search IMDb Id" and copy the ID found.' , 'lumiere-movies' ),

							},),// end explanation div
							el(
								'div', {
									className: blockProps.className,
									tagName: 'div',
									className: 'lumiere_block_intothepost-container',
								},
								el(
									'div', {
										className: blockProps.className,
										tagName: 'div',
										className: 'lumiere_block_intothepost-select',
									},
									el(
										'select', {
											value: blockProps.attributes.lumiere_imdblt_select,

											onChange:  function updateType( event ) {
												// reset the text field when changing the option
												blockProps.setAttributes( { content: empty } );
												blockProps.setAttributes( { lumiere_imdblt_select: event.target.value } );
											},
											name: 'movie_type_selection',
										},
										// Keeping double i18n, but only the second is needed
										el( "option", { label: __( "By movie title", 'lumiere-movies' ), value: "movie_title" }, __( "By movie title", 'lumiere-movies' ) ),
										el( "option", { label: __( "By IMDb ID", 'lumiere-movies' ), value: "movie_id" }, __( "By IMDb ID", 'lumiere-movies' ) ),
									)
								),
								el(
									RichText, {
										tagName: 'div',
										className: 'lumiere_block_intothepost-content',
										value: blockProps.attributes.content,
										onChange: ( content ) => {
											blockProps.setAttributes( { content: content } );
										}
									}
								)
							)// end div container
						) // end div intothepost
					) // end div editor-styles-wrapper
				); // end return
			},// end function

			// Use "blockPropsSave" instead of "Props" with apiVersion 2, but then can't move the block
			save: ( Props ) => {
				return (
					el(
						'div',
						{ className: Props.className },
						el(
							RichContent, {
								className: 'lumiere_block_intothepost-content',
								value: '<span data-lum_movie_maker="' + Props.attributes.lumiere_imdblt_select + '">' + Props.attributes.content + '</span>',
							}
						)
					)
				);
			},

		}
	);
} )( window.wp );
