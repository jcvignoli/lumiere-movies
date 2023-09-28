( function( blocks, blockEditor, element, components, data, i18n ) {

	var el = element.createElement;
	var elwithhtml = element.RawHTML; /* this type of block can include html */

	var { registerBlockType } = blocks;

	/* Remove useless formatting options
	-> removes it everywhere, unactivated
	wp.domReady(function () {
		wp.richText.unregisterFormatType('core/bold');
		wp.richText.unregisterFormatType('core/italic');
		wp.richText.unregisterFormatType('core/link');
		wp.richText.unregisterFormatType('core/strikethrough');
		wp.richText.unregisterFormatType('core/underline');
		wp.richText.unregisterFormatType('core/code');
		wp.richText.unregisterFormatType('core/image');
		wp.richText.unregisterFormatType('core/subscript');
		wp.richText.unregisterFormatType('core/superscript');

	})
	formattingControls= [ 'bold' , 'superscript' ];
	*/

	var intro_words = i18n.__( 'Enter the name or the IMDb ID movie' , 'lumiere-movies' );

	var empty = '';

	const iconLumiere = el(
		'svg',
		{ width: 35, height: 35, viewBox: "0 0 200 200" },
		el(
			'path',
			{ d: "M10 100 c0 -47 4 -80 10 -80 6 0 10 33 10 80 0 47 -4 80 -10 80 -6 0 -10 -33 -10 -80zM50 170 c0 -5 23 -10 50 -10 28 0 50 5 50 10 0 6 -22 10 -50 10 -27 0 -50 -4 -50 -10zM170 100 c0 -47 4 -80 10 -80 6 0 10 33 10 80 0 47 -4 80 -10 80 -6 0 -10 -33 -10 -80zM50 110 c0 -29 1 -30 50 -30 49 0 50 1 50 30 0 29 -1 30 -50 30 -49 0 -50 -1 -50 -30zM50 35 c0 -11 12 -15 50 -15 38 0 50 4 50 15 0 11 -12 15 -50 15 -38 0 -50 -4 -50 -15z"
			}
		)
	);

	registerBlockType(
		'lumiere/main',
		{
			title: i18n.__( 'Lumière! movie blocks', 'lumiere-movies' ),
			description: i18n.__( 'Insert a series of details related to a movie in your post.', 'lumiere-movies' ),
			icon: iconLumiere,
			category: 'embed',
			keywords: [ 'embed', 'lumiere', 'imdb', 'movies', 'film' ],

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
			edit: function( props ) {
				return (
					el(
						'div',
						{
							className: props.className,
							tagName: 'div',
							className: 'lumiere_block_intothepost',
						},
						el(
							'img',
							{
								className: props.className,
								className: 'lumiere_block_intothepost-image',
								src: lumiere_admin_vars.imdb_path + 'assets/pics/lumiere-ico-noir80x80.png',
								},
						),// end img
						elwithhtml(
							{ /* this type of block can include html */
								className: props.className,
								className: 'lumiere_block_intothepost-title',
								children: 'Lumière! movies',
								},
						),// end h2 title
						elwithhtml(
							{ /* this type of block can include html */
								className: props.className,
								className: 'lumiere_block_intothepost-explanation',
								tagName: 'gutenberg',
								children: i18n.__( 'This block will vanish in your post, only the movie will be shown.' , 'lumiere-movies' )
									+ '<br />'
									+ i18n.__( 'Use this block to retrieve movie or people information from the IMDb and insert in your post.' , 'lumiere-movies' )
									+ '<br />'
									+ i18n.__( 'You can also click on this link to get the' , 'lumiere-movies' )
									+ ' <a data-lumiere_admin_popup="somevalue" '
									+ 'onclick="window.open(\'' + lumiere_admin_vars.wordpress_admin_path + lumiere_admin_vars.gutenberg_search_url_string + '\', \'_blank\', \'location=yes,height=400,width=500,scrollbars=yes,status=yes\');" '
									+ 'class="linkincmovie link-imdblt-highslidepeople highslide" '
									+ 'target="_blank">'
									+ i18n.__( 'IMDb movie id' , 'lumiere-movies' )
									+ '</a> ' + i18n.__( 'and insert it.' , 'lumiere-movies' ),

								},
						),// end explanation div
						el(
							'div',
							{
								className: props.className,
								tagName: 'div',
								className: 'lumiere_block_intothepost-container',
								},
							el(
								'div',
								{
									className: props.className,
									tagName: 'div',
									className: 'lumiere_block_intothepost-select',
									},
								el(
									'select',
									{
										value: props.attributes.lumiere_imdblt_select,

										onChange:  function updateType( event ) {
											// reset the text field when changing the option
											props.setAttributes( { content: empty } );
											props.setAttributes( { lumiere_imdblt_select: event.target.value } );
										},
									},
									// Keeping double i18n, but only the second is needed
									el( "option", { label: i18n.__( "By movie title", 'lumiere-movies' ), value: "movie_title" }, i18n.__( "By movie title", 'lumiere-movies' ) ),
									el( "option", { label: i18n.__( "By IMDb ID", 'lumiere-movies' ), value: "movie_id" }, i18n.__( "By IMDb ID", 'lumiere-movies' ) ),
								)
							),
							el(
								blockEditor.RichText,
								{
									tagName: 'div',
									className: 'lumiere_block_intothepost-content',
									value: props.attributes.content,
									onChange: function( content ) {
										props.setAttributes( { content: content } );
									}
								}
							)
						)// end div container
					) // end div intothepost
				); // end return
			},// end function

			save: function( props ) {
				return (
				el(
					'div',
					{ className: props.className },
					el(
						blockEditor.RichText.Content,
						{
							className: 'lumiere_block_intothepost-content',
							value: '<span data-lum_movie_maker="' + props.attributes.lumiere_imdblt_select + '">' + props.attributes.content + '</span>',
						}
					)
				)
				);
			},

		}
	);
})( window.wp.blocks, window.wp.blockEditor, window.wp.element, window.wp.components, window.wp.data, window.wp.i18n, );
