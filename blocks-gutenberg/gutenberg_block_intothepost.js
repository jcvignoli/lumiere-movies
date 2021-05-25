( function( blocks, blockEditor, element, components, data, i18n ) {

	var el = element.createElement;
	var { registerBlockType } = blocks;
	var intro_words = i18n.__( 'Type the name or IMDb movie' , 'lumiere-movies') ;
	var empty = '';

	/* trying to remove panels, but doesn't work
	wp.domReady( () => {
	wp.data.dispatch('core/edit-post').removeEditorPanel( 'taxonomy-panel-category' ) ; // category
	wp.data.dispatch('core/edit-post').removeEditorPanel( 'taxonomy-panel-post_tag' ); // tags
	wp.data.dispatch('core/edit-post').removeEditorPanel( 'featured-image' ); // featured image
	wp.data.dispatch('core/edit-post').removeEditorPanel( 'post-link' ); // permalink
	wp.data.dispatch('core/edit-post').removeEditorPanel( 'page-attributes' ); // page attributes
	wp.data.dispatch('core/edit-post').removeEditorPanel( 'post-excerpt' ); // Excerpt
	wp.data.dispatch('core/edit-post').removeEditorPanel( 'discussion-panel' ); // Discussion
	} );
	*/

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
			supports: {
			    // Declare support for specific alignment options.
			    align: [ 'left', 'right', 'full' ],
			}
		},
		edit: function( props ) {
			return ( 
			el( 'div', 	{ 
					className: props.className,
					tagName: 'div',
					className: 'lumiere_gutenberg_block_intothepost',
					},

					el( 'img', { 
						className: props.className,
						className: 'lumiere_gutenberg_block_intothepost-image',
						src: lumiere_admin_vars.imdb_path + 'pics/lumiere-ico-noir80x80.png',
						},
					),// end img

					el( blockEditor.RichText, { 
						className: props.className,
						tagName: 'h2',
						className: 'lumiere_gutenberg_block_intothepost-title',
						value: 'Lumière! movies',
						},
					),// end h2 title

					el( blockEditor.RichText, { 
						className: props.className,
						className: 'lumiere_gutenberg_block_intothepost-explanation',
						value: i18n.__( 'Use this block to retrieve movie or people information from the IMDb and insert in your post.' , 'lumiere-movies') 
							+ '<br />'
							+ i18n.__( 'You can also click on this link to get the' , 'lumiere-movies') 
							+ ' <a href="' + lumiere_admin_vars.imdb_path 
							+ 'inc/gutenberg-search.php?gutenberg=yes">' 
							+ i18n.__( 'IMDb movie id' , 'lumiere-movies') 
							+ '</a> ' + i18n.__( 'and insert it.' , 'lumiere-movies'),
						},
					),// end explanation div

					el( 'div', { 
						className: props.className,
						tagName: 'div',
						className: 'lumiere_gutenberg_block_intothepost-container',
						},

						el( 'div', { 
							className: props.className,
							tagName: 'div',
							className: 'lumiere_gutenberg_block_intothepost-select',
							},
							el( 'select', {
								value: props.attributes.lumiere_imdblt_select,
								onChange:  function updateType( event ) {
							// reset the text field when changing the option
							props.setAttributes( { content: empty });

							props.setAttributes({lumiere_imdblt_select: event.target.value });
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
								onChange: function( content ) {
									props.setAttributes( { content: content });
								}
							}
						)
					)// end div container
				) // end div intothepost
			); // end return
		},// end function

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

