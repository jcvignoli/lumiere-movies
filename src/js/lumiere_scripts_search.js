/* Function for Gutenberg search
*/

spans = document.querySelectorAll( ".lumiere_gutenberg_copy_class" );
for (const span of spans) {
	span.onclick = function() {
		document.execCommand( "copy" );
	}

	span.addEventListener(
		"copy",
		function(event) {
			event.preventDefault();
			if (event.clipboardData) {
				event.clipboardData.setData( "text/plain", span.textContent );
				alert( "Successfully copied " + event.clipboardData.getData( "text" ) );
				console.log( event.clipboardData.getData( "text" ) );
			}
		}
	);
};
