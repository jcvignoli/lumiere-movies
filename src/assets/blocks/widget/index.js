// Wrapping it into a function allows to have a unique constant definition in this file
// Can't get it work with API > 1 https://developer.wordpress.org/block-editor/reference-guides/block-api/block-api-versions/
( ( wp ) => {

	const el = wp.element.createElement;
	const elWithHTML = wp.element.RawHTML; /* this type of block can include html */
	const { registerBlockType } = wp.blocks;
	const { blockProps } = wp.blockEditor.useBlockProps;
	const { blockPropsSave } = wp.blockEditor.useBlockProps.save;
	const RichContent = wp.blockEditor.RichText.Content;
	const __ = wp.i18n.__;
	
	var iconLumiere = el(
		'svg',
		{ width: 35, height: 35, viewBox: "0 0 200 200" },
		el(
			'path',
			{ d: "M10 100 c0 -47 4 -80 10 -80 6 0 10 33 10 80 0 47 -4 80 -10 80 -6 0 -10 -33 -10 -80z M50 170 c0 -5 23 -10 50 -10 28 0 50 5 50 10 0 6 -22 10 -50 10 -27 0 -50 -4 -50 -10z M170 100 c0 -47 4 -80 10 -80 6 0 10 33 10 80 0 47 -4 80 -10 80 -6 0 -10 -33 -10 -80z M50 110 c0 -29 1 -30 50 -30 49 0 50 1 50 30 0 29 -1 30 -50 30 -49 0 -50 -1 -50 -30z M57 44 c-17 -17 -4 -24 44 -24 36 0 49 4 47 13 -5 13 -79 22 -91 11z"
			}
		)
	);
	var linkAutoTitleWidgetOption = '<a href="admin.php?page=lumiere_options&subsection=advanced#imdbautopostwidget" target="_blank">' + __( 'Lumière general advanced options' , 'lumiere-movies' )	+ '</a>';
	
	registerBlockType(
		'lumiere/widget', {
			title: 'Lumière! Widget',
			description: __( 'Display a movie according to the metabox data or the post title', 'lumiere-movies' ),
			icon: iconLumiere,
			keywords: [ 'widget', 'lumiere', 'imdb', 'movie', 'film' ],
			attributes: {
				lumiere_input: {
					type: 'string',
					options: 'html',
					default: 'Lumière Movies'
				},
			},
			example: {},
			edit: ( blockProps ) => {
				return (
					el(
						'div', {
							className: blockProps.className,
							tagName: 'div',
							className: 'lumiere_block_widget',
						},
						el( 'img', {
								className: blockProps.className,
								className: 'lumiere_block_widget_image',
								src: lumiere_admin_vars.ico80,
						},),
						elWithHTML( { /* this type of block can include html */
								className: blockProps.className,
								className: 'lumiere_block_widget_title',
								children: 'Lumière! Widget',
						},), // end h2 title
						elWithHTML( { /* this type of block can include html */
								className: blockProps.className,
								className: 'lumiere_block_widget_explanation',
								tagName: 'gutenberg',
								children: __( 'This widget fills your selected area with movie data according to the metabox data or the title of your post. After adding this widget, either find the metabox in your post settings to add a specific movie, or select "Auto title widget" in', 'lumiere-movies' )
									+ ' ' + linkAutoTitleWidgetOption + ' '
									+ __( 'should you want your selected area to be filled with movie data according to your post title.', 'lumiere-movies' )
									+ '<br />'
						},),// end explanation div
						el(
							'div', {
								className: blockProps.className,
								tagName: 'div',
								className: 'lumiere_block_widget_container',
							},
							el(
								'div', {
									className: blockProps.className,
									tagName: 'div',
									className: 'lumiere_block_widget_entertitle',
									children: 'Enter widget title:',
									onChange: function updateType( event ) {
										blockProps.setAttributes( {lumiere_input: event.target.value } );
									},
								},
							),
							el(
								'div', {
									className: blockProps.className,
									tagName: 'div',
									className: 'lumiere_block_widget_enterinput',
								},
								el( 'input', {
									value: blockProps.attributes.lumiere_input,
									className: blockProps.className,
									tagName: 'input',
									className: 'lumiere_block_widget_input',
									onChange: function updateType( event ) {
										blockProps.setAttributes( {lumiere_input: event.target.value } );
									},
								}),
							),
						) // end div container
					) // end div intothepost
				); // end return
			}, // end function

			save: ( blockPropsSave ) => {
				return (
					el(	'div',
						{ className: blockPropsSave.className },
						el(
							RichContent, {
								className: 'lumiere_block_widget_input',
								value: '[lumiereWidget]' + blockPropsSave.attributes.lumiere_input + '[/lumiereWidget]',
							}
						)
					)
				);
			},

		}
	);
} )( window.wp );
