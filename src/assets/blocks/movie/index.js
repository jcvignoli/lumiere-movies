( function( blocks, blockEditor, element, components, data, i18n, jQuery ) {

	var el = element.createElement;
	var elwithhtml = element.RawHTML; /* this type of block can include html */
	var { registerBlockType } = blocks;
	const { blockProps } = blockEditor.useBlockProps;
	const { blockPropsSave } = blockEditor.useBlockProps.save;

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
	jQuery( 'a[data-lumiere_admin_popup]' ).click(
		function(){
			var tmppopupLarg = lumiere_admin_vars.popupLarg;
			var tmppopupLong = lumiere_admin_vars.popupLong;
			var url_imdbperso = lumiere_admin_vars.wordpress_admin_path + lumiere_admin_vars.gutenberg_search_url_string;

			// classic popup
			window.open( url_imdbperso, 'popup', 'resizable=yes, toolbar=no, scrollbars=yes, location=no, width=' + tmppopupLarg + ', height=' + tmppopupLong + ', top=100, left=100' );
		}
	);
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
						'div',
						{
							className: blockProps.className,
							tagName: 'div',
							className: 'wp-block',
						},
						el(
							'div',
							{
								className: blockProps.className,
								tagName: 'div',
								className: 'lumiere_block_intothepost',
							},
							el(
								'img',
								{
									className: blockProps.className,
									className: 'lumiere_block_intothepost-image',
									src: lumiere_admin_vars.ico80,
									},
							),// end img
							elwithhtml(
								{ /* this type of block can include html */
									className: blockProps.className,
									className: 'lumiere_block_intothepost-title',
									children: 'Lumière! movies',
									},
							),// end h2 title
							elwithhtml(
								{ /* this type of block can include html */
									className: blockProps.className,
									className: 'lumiere_block_intothepost-explanation',
									tagName: 'gutenberg',
									children: i18n.__( 'This block is display only in the admin area, it will vanish in your post, where only the movie you select here will be shown.' , 'lumiere-movies' )
										+ '<br />'
										+ i18n.__( 'Use this block to retrieve movie information from the IMDb and insert in your post.' , 'lumiere-movies' )
										+ '<br />'
										+ i18n.__( 'You can also click on this link to get the' , 'lumiere-movies' )
										+ ' <a data-lumiere_admin_popup="useSomeValuePopupOpen" '
										+ 'onclick="window.open(\'' + lumiere_admin_vars.wordpress_admin_path + lumiere_admin_vars.gutenberg_search_url_string + '\', \'_blank\', \'location=yes,height=' + lumiere_admin_vars.popupLong + ',width=' + lumiere_admin_vars.popupLarg + ',scrollbars=yes,status=yes, top=100, left=100\');" '
										+ 'class="linkincmovie link-imdblt-highslidepeople highslide" target="_blank">'
										+ i18n.__( 'IMDb movie id' , 'lumiere-movies' )
										+ '</a> ' + i18n.__( 'and insert it.' , 'lumiere-movies' ),

									},
							),// end explanation div
							el(
								'div',
								{
									className: blockProps.className,
									tagName: 'div',
									className: 'lumiere_block_intothepost-container',
									},
								el(
									'div',
									{
										className: blockProps.className,
										tagName: 'div',
										className: 'lumiere_block_intothepost-select',
										},
									el(
										'select',
										{
											value: blockProps.attributes.lumiere_imdblt_select,

											onChange:  function updateType( event ) {
												// reset the text field when changing the option
												blockProps.setAttributes( { content: empty } );
												blockProps.setAttributes( { lumiere_imdblt_select: event.target.value } );
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
						blockEditor.RichText.Content,
						{
							className: 'lumiere_block_intothepost-content',
							value: '<span data-lum_movie_maker="' + Props.attributes.lumiere_imdblt_select + '">' + Props.attributes.content + '</span>',
						}
					)
				)
				);
			},

		}
	);
})( window.wp.blocks, window.wp.blockEditor, window.wp.element, window.wp.components, window.wp.data, window.wp.i18n, jQuery );
