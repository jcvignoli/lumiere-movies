/**
 * Functions for Gutenberg search
 */

/** on click, copy the selected IMDb ID */
spans = document.querySelectorAll( ".lum_search_imdbid" );
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
