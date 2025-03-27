import './index.css';

const { createElement: el, RawHTML: elWithHTML } = wp.element;
const { registerBlockType } = wp.blocks;
const { useBlockProps } = wp.blockEditor;
const { __: widget__ } = wp.i18n;
const { Content: RichContent } = wp.blockEditor.RichText;

(function($) {	
var iconLumiere = el(
	'svg',
	{ width: 35, height: 35, viewBox: "0 0 200 200" },
	el(
		'path',
		{ d: "M10 100 c0 -47 4 -80 10 -80 6 0 10 33 10 80 0 47 -4 80 -10 80 -6 0 -10 -33 -10 -80z M50 170 c0 -5 23 -10 50 -10 28 0 50 5 50 10 0 6 -22 10 -50 10 -27 0 -50 -4 -50 -10z M170 100 c0 -47 4 -80 10 -80 6 0 10 33 10 80 0 47 -4 80 -10 80 -6 0 -10 -33 -10 -80z M50 110 c0 -29 1 -30 50 -30 49 0 50 1 50 30 0 29 -1 30 -50 30 -49 0 -50 -1 -50 -30z M57 44 c-17 -17 -4 -24 44 -24 36 0 49 4 47 13 -5 13 -79 22 -91 11z"
		}
	)
);
var linkAutoTitleWidgetOption = '<a href="admin.php?page=lumiere_options&subsection=advanced#imdbautopostwidget" target="_blank">' + widget__( 'Lumière main advanced options' , 'lumiere-movies' )	+ '</a>';
	
registerBlockType(
	'lumiere/widget', {
		title: widget__( 'Add a movie in your Widget', 'lumiere-movies' ),
		description: widget__( 'Widget to add a movie or prevent auto title widget from adding movies in selected posts', 'lumiere-movies' ),
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
		edit: ( props ) => {
			const { className, setAttributes, attributes } = props;
			const blockProps = useBlockProps();
			    return (
				el(
					'div', { ...blockProps, className: 'lumiere_block_widget' },
					el('img', { className: 'lumiere_block_widget_image', src: lumiere_admin_vars.ico80 }),
					elWithHTML({ className: 'lumiere_block_widget_title', children: 'Lumière! Widget' }),
					elWithHTML({
						className: 'lumiere_block_widget_explanation', tagName: 'gutenberg',
						children: `${widget__('This widget fills your selected area with movie data according to the metabox data or the title of your post. After adding this widget, either find the metabox in your post or the title[...]')}`
						+ ` ${linkAutoTitleWidgetOption} `
						+ `${widget__('should you want your selected area to be filled with movie data according to your post title.', 'lumiere-movies')}<br />`
					}),
					el(
						'div', { className: 'lumiere_block_widget_container' },
						el('div', {
							className: 'lumiere_block_widget_entertitle',
							children: 'Enter widget title:',
							onChange: event => setAttributes({ lumiere_input: event.target.value })
						}),
						el('div', { className: 'lumiere_block_widget_enterinput' },
						el('input', {
							value: attributes.lumiere_input,
								className: 'lumiere_block_widget_input',
								onChange: event => setAttributes({ lumiere_input: event.target.value })
							})
						)
					)
				)
			);
		},

		save: ( props ) => {
			const { className, attributes } = props;
			return (
				el('div', { className },
				el(RichContent, {
					  className: 'lumiere_block_widget_input',
					  value: `[lumiereWidget]${attributes.lumiere_input}[/lumiereWidget]`
					})
				)
			);
		},

	}
);
})(jQuery);

