/* Function to display/hide an element if clicking on another element
*
* Content Security Policy (CSP) Compliant, needs JQuery
*
*/

/* Show the next hidden div if clicking a tag class="activatehidesection"
 * The following div must follow immediately that one
 * source https://isabelcastillo.com/toggle-showhide-with-jquery
*/
jQuery( ".activatehidesection" ).click(
	function () {
		jQuery( this ).next().slideToggle();
	}
).next().hide();


/* Show the next hidden div if clicking a checkbox button class="activatehidesectionAdd"
 * The (next) div to be impacted must have the class="hidesectionOfRadio" and a css "display:none;" to work
 * Works similarly with a second checkbox child of the first checkbox, if button class="activatehidesectionAdd2" is added
 * source: Lost Highway
*/

document.addEventListener(
	'DOMContentLoaded',
	function () {

		if (jQuery( "input.activatehidesectionAdd" ).prop( "checked" ) == true) {
			jQuery( ".hidesectionOfCheckbox" ).show();
		} else if (jQuery( ".activatehidesectionRemove" ).prop( "checked" ) == true) {
			jQuery( ".hidesectionOfCheckbox" ).hide();
		}

		jQuery( "input.activatehidesectionAdd" ).click(
			function () {
				jQuery( ".hidesectionOfCheckbox" ).slideToggle();
			}
		).nextAll( ".hidesectionOfCheckbox" ).show();

		if (jQuery( "input.activatehidesectionAddTwo" ).prop( "checked" ) == true) {
			jQuery( ".hidesectionOfCheckboxTwo" ).show();
		} else if (jQuery( ".activatehidesectionRemoveTwo" ).prop( "checked" ) == true) {
			jQuery( ".hidesectionOfCheckboxTwo" ).hide();
		}

		jQuery( "input.activatehidesectionAddTwo" ).click(
			function () {
				jQuery( ".hidesectionOfCheckboxTwo" ).slideToggle();
			}
		).nextAll( ".hidesectionOfCheckboxTwo" ).show();

	}
);
