( function ( wp ) {

	// Activated if text is selected in Movie block
	
	
	var withSelect = wp.data.withSelect;
	var ifCondition = wp.compose.ifCondition;
	var compose = wp.compose.compose;
	var el = wp.element.createElement;

	const iconLumiereWindow = el(
		'svg',
		{ width: 20, height: 20, viewBox: "0 0 1200 1200" },
		el(
			'path',
			{ d: "M70 998 c-16 -93 -26 -756 -10 -778 22 -33 31 14 28 155 -3 129 -1 142 17 162 11 11 29 23 40 25 19 3 20 12 23 138 2 90 -3 174 -14 253 -17 116 -17 117 -44 117 -25 0 -27 -4 -40 -72zM348 1053 c-52 -14 -58 -18 -58 -42 0 -26 4 -28 83 -44 110 -23 333 -23 439 0 74 16 78 18 78 43 0 25 -5 29 -65 44 -86 22 -393 21 -477 -1zM1027 963 c-13 -82 -17 -172 -17 -373 0 -201 4 -291 17 -372 17 -106 17 -108 43 -108 26 0 27 2 43 113 13 82 17 180 17 367 0 187 -4 285 -17 368 -16 110 -17 112 -43 112 -26 0 -26 -2 -43 -107zM340 814 c-37 -25 -50 -45 -50 -75 l0 -28 180 6 181 6 35 -34 36 -34 -6 -92 -6 -93 53 0 c52 0 87 15 114 50 8 10 12 55 12 130 0 122 -3 132 -51 163 -24 15 -56 17 -250 17 -191 0 -227 -2 -248 -16zM274 672 c-21 -14 -34 -51 -34 -98 0 -46 0 -46 -30 -41 -36 8 -86 -9 -95 -31 -9 -24 -24 -322 -18 -356 10 -48 51 -58 233 -52 88 2 167 9 179 16 18 9 21 20 21 70 0 47 -3 60 -15 60 -10 0 -15 -10 -15 -33 0 -21 -8 -41 -20 -52 -18 -16 -33 -17 -162 -11 -79 4 -149 12 -155 18 -9 8 -13 52 -13 159 0 141 1 149 22 163 12 9 33 16 47 16 l24 0 -6 -88 c-7 -99 9 -152 50 -162 17 -4 20 -9 13 -20 -5 -8 -10 -25 -10 -37 0 -22 3 -23 91 -23 81 0 90 2 87 18 -3 14 -12 16 -47 14 -24 -2 -54 -1 -67 2 -43 11 -25 26 30 26 30 0 57 4 60 9 3 4 41 9 85 9 104 1 146 18 155 63 4 19 4 97 0 174 -6 132 -8 142 -32 168 l-26 27 -170 0 c-94 0 -176 -4 -182 -8z m357 -43 c23 -23 22 -8 3 -239 -3 -41 -10 -80 -15 -86 -10 -12 -288 -17 -306 -6 -7 4 -14 67 -19 160 -9 202 -18 192 173 192 132 0 145 -2 164 -21zM325 612 c-3 -3 -5 -26 -5 -52 0 -76 16 -84 171 -88 l129 -4 0 54 c0 85 -4 87 -157 91 -73 2 -135 2 -138 -1zM692 256 c-18 -20 -41 -36 -51 -36 -10 0 -21 -3 -25 -7 -4 -5 -17 -8 -29 -8 -17 0 -23 -8 -28 -40 -4 -22 -3 -44 1 -49 4 -4 61 -6 127 -4 91 3 127 9 158 24 38 18 40 22 40 64 0 42 -2 46 -38 63 -21 9 -57 20 -80 23 -39 6 -45 3 -75 -30z"
			}
		)
	);

	var ButtonOpenSearch = function ( props ) {
		return wp.element.createElement(
			wp.blockEditor.RichTextToolbarButton,
			{
				icon: iconLumiereWindow,
				title: wp.i18n.__( 'Open search IMDB ID', 'lumiere-movies' ),
				onClick: function () {
					open( lumiere_admin_vars.wordpress_path + '/' + lumiere_admin_vars.gutenberg_search_url_string );
				},
				isActive: props.isActive,
			}
		);
	};
	var ConditionalButton = compose(
		withSelect(
			function ( select ) {
				return {
					selectedBlock: select( 'core/block-editor' ).getSelectedBlock(),
				};
			}
		),
		ifCondition(
			function ( props ) {
				return (
				props.selectedBlock &&
				props.selectedBlock.name === 'lumiere/main'
				);
			}
		)
	)( ButtonOpenSearch );

	wp.richText.registerFormatType(
		'lumiere/opensearch',
		{
			title: wp.i18n.__( 'Open search IMDB ID', 'lumiere-movies' ),
			tagName: "someRandomTag",
			className: null,
			edit: ConditionalButton,
			attributes: {
				'data-lum_link_maker': 'data-lum_link_maker',
			},
		}
	);
} )( window.wp );
