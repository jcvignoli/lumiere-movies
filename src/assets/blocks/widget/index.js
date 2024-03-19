( function( blocks, blockEditor, element, components, data, i18n ) {

	var el = element.createElement;
	var elwithhtml = element.RawHTML; /* this type of block can include html */

	var { registerBlockType } = blocks;
	var { blockProps } = blockEditor.useBlockProps;
	var { blockPropsSave } = blockEditor.useBlockProps.save;

	const iconLumiere = el(
		'svg',
		{ width: 35, height: 35, viewBox: "0 0 200 200" },
		el(
			'path',
			{ d: "M10 100 c0 -47 4 -80 10 -80 6 0 10 33 10 80 0 47 -4 80 -10 80 -6 0 -10 -33 -10 -80z M50 170 c0 -5 23 -10 50 -10 28 0 50 5 50 10 0 6 -22 10 -50 10 -27 0 -50 -4 -50 -10z M170 100 c0 -47 4 -80 10 -80 6 0 10 33 10 80 0 47 -4 80 -10 80 -6 0 -10 -33 -10 -80z M50 110 c0 -29 1 -30 50 -30 49 0 50 1 50 30 0 29 -1 30 -50 30 -49 0 -50 -1 -50 -30z M57 44 c-17 -17 -4 -24 44 -24 36 0 49 4 47 13 -5 13 -79 22 -91 11z"
			}
		)
	);

	registerBlockType(
		'lumiere/widget',
		{

			title: 'Lumière! Widget',
			description: i18n.__( 'Lumière Widget automatically adds movies to your widgets sidebar. After adding the widget, either use the new box on the right when editing your post to add your movie or select "Auto title widget" in advanced options.', 'lumiere-movies' ),
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

			example: {},

			edit: function( blockProps ) {
				return (
					el(
						'div',
						{
							className: blockProps.className,
							tagName: 'div',
							className: 'lumiere_block_widget',
						},
						el(
							'img',
							{
								className: blockProps.className,
								className: 'lumiere_block_widget_image',
								src: lumiere_admin_vars.imdb_path + 'assets/pics/lumiere-ico80x80.png',
							},
						),
						elwithhtml(
							{ /* this type of block can include html */
								className: blockProps.className,
								className: 'lumiere_block_widget_title',
								children: 'Lumière! Widget',
							},
						), // end h2 title
						elwithhtml(
							{ /* this type of block can include html */
								className: blockProps.className,
								className: 'lumiere_block_widget_explanation',
								tagName: 'gutenberg',
								children: i18n.__( 'This widget shows movies in your posts.', 'lumiere-movies' )
									+ '<br />'
									+ i18n.__( 'Movie details will be displayed in Lumière! Widget according to 1/ their post title, if Lumière auto-widget option is checked; 2/ the movie ID or title you entered in the metabox of a post. If you are using auto-widget, make sure the post titles are exactly the same as the movie titles on the IMDb website.', 'lumiere-movies' )
									+ '<br />'
							},
						),// end explanation div
						el(
							'div',
							{
								className: blockProps.className,
								tagName: 'div',
								className: 'lumiere_block_widget_container',
							},
							el(
								'div',
								{
									className: blockProps.className,
									tagName: 'div',
									className: 'lumiere_block_widget_entertitle',
									children: 'Enter widget title:',
									onChange:  function updateType( event ) {
										blockProps.setAttributes( {lumiere_input: event.target.value } );
									},
								},
							),
							el(
								'div',
								{
									className: blockProps.className,
									tagName: 'div',
									className: 'lumiere_block_widget_enterinput',
								},
								el(
									'input',
									{
										value: blockProps.attributes.lumiere_input,
										className: blockProps.className,
										tagName: 'input',
										className: 'lumiere_block_widget_input',

										onChange:  function updateType( event ) {
											blockProps.setAttributes( {lumiere_input: event.target.value } );
										},
									},
								),
							),
						) // end div container
					) // end div intothepost
				); // end return
			}, // end function

			save: function( blockPropsSave ) {
				return (
					el(
						'div',
						{ className: blockPropsSave.className },
						el(
							blockEditor.RichText.Content,
							{
								className: 'lumiere_block_widget_input',
								value: '[lumiereWidget]' + blockPropsSave.attributes.lumiere_input + '[/lumiereWidget]',
							}
						)
					)
				);
			},

		}
	);
} )( window.wp.blocks, window.wp.blockEditor, window.wp.element, window.wp.components, window.wp.data, window.wp.i18n );
