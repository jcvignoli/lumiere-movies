( function ( wp ) {
    var withSelect = wp.data.withSelect;
    var ifCondition = wp.compose.ifCondition;
    var compose = wp.compose.compose;

	var el = wp.element.createElement;

	const iconLumiereNoir = el('svg', { width: 35, height: 35, viewBox: "0 0 200 200" },
		el( 'path', 
			{ d: "M10 100 c0 -47 4 -80 10 -80 6 0 10 33 10 80 0 47 -4 80 -10 80 -6 0 -10 -33 -10 -80zM50 170 c0 -5 23 -10 50 -10 28 0 50 5 50 10 0 6 -22 10 -50 10 -27 0 -50 -4 -50 -10zM170 100 c0 -47 4 -80 10 -80 6 0 10 33 10 80 0 47 -4 80 -10 80 -6 0 -10 -33 -10 -80zM50 110 c0 -29 1 -30 50 -30 49 0 50 1 50 30 0 29 -1 30 -50 30 -49 0 -50 -1 -50 -30zM50 35 c0 -11 12 -15 50 -15 38 0 50 4 50 15 0 11 -12 15 -50 15 -38 0 -50 -4 -50 -15z"
			}
		)
	);

    var MyCustomButton = function ( props ) {
        return wp.element.createElement( wp.blockEditor.RichTextToolbarButton, {
            icon: iconLumiereNoir,
            title: 'Test',
            onClick: function () {
                console.log( 'toggle format' );
            },
        } );
    };
    var ConditionalButton = compose(
        withSelect( function ( select ) {
            return {
                selectedBlock: select( 'core/block-editor' ).getSelectedBlock(),
            };
        } ),
        ifCondition( function ( props ) {
            return (
                props.selectedBlock &&
                props.selectedBlock.name === 'lumiere/main'
            );
        } )
    )( MyCustomButton );
 
    wp.richText.registerFormatType( 'my-custom-format/sample-output', {
        title: 'Sample output',
        tagName: 'samp',
        className: null,
        edit: ConditionalButton,
    } );
} )( window.wp );
