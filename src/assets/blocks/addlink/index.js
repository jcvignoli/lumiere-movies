( function ( blockEditor, richText, element, i18n ) {

	var el = element.createElement;
	var { blockProps } = blockEditor.useBlockProps;
	
	const iconLumiereLink = el(
		'svg',
		{ width: 20, height: 20, viewBox: "0 0 350 350" },
		el(
			'path',
			{ d: "M25 301 c-7 -24 -4 -248 5 -259 8 -11 51 -13 64 -3 6 5 19 6 28 2 9 -3 44 -4 77 -3 51 3 61 7 61 22 0 20 -27 30 -87 30 -31 0 -43 -4 -43 -14 0 -8 -4 -17 -9 -20 -5 -4 -7 4 -4 16 l5 23 -16 -22 c-10 -13 -24 -23 -32 -23 -26 0 -28 30 -4 56 28 29 9 33 -20 4 -11 -11 -20 -16 -20 -11 0 6 5 13 10 16 12 8 13 175 1 194 -7 11 -10 9 -16 -8zM123 313 c-35 -5 -43 -17 -17 -27 41 -16 173 -1 154 17 -8 8 -103 15 -137 10zM306 305 c-9 -25 -7 -248 3 -263 15 -24 21 14 21 136 0 114 -10 164 -24 127zM90 196 c0 -53 10 -63 35 -36 10 11 24 20 32 20 22 0 27 -29 8 -50 -10 -11 -14 -20 -8 -20 12 0 43 33 43 47 0 5 -9 18 -19 27 -24 22 -54 14 -69 -17 -7 -15 -11 -17 -11 -6 -1 24 34 51 61 47 15 -2 28 -15 38 -36 11 -23 21 -31 35 -30 16 3 21 12 23 51 l3 47 -85 0 -86 0 0 -44zM147 153 c4 -10 2 -14 -4 -10 -5 3 -25 -9 -43 -28 -21 -21 -28 -35 -19 -35 20 1 91 69 84 80 -10 17 -25 11 -18 -7z"
			}
		)
	);

	var ButtonTagIMDb = function ( blockProps ) {
		return el(
			blockEditor.RichTextToolbarButton,
			{
				icon: iconLumiereLink,
				title: i18n.__( 'Add IMDb link', 'lumiere-movies' ),
				onClick: function () {
					blockProps.onChange(
						richText.toggleFormat(
							blockProps.value,
							{
								type: "lumiere/addimdblink",
								attributes: {
									"data-lum_link_maker": "popup"
								},
							},
						)
					);
				},
				isActive: blockProps.isActive,
			}
		);
	}; 

	richText.registerFormatType(
		'lumiere/addimdblink',
		{
			title: i18n.__( 'Add IMDb link', 'lumiere-movies' ),
			tagName: 'span',
			className: 'notneeded',
			edit: ButtonTagIMDb,

		}
	);

} )( window.wp.blockEditor, window.wp.richText, window.wp.element, window.wp.i18n, );
