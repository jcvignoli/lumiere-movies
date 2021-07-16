/* Function to display/hide an element if clicking on another element
*										
* Content Security Policy (CSP) Compliant, needs JQuery
* 
*/

/* Show the next hidden div if clicking a tag class="activatehidesection"
 * The following div must follow immediately that one
 * source https://isabelcastillo.com/toggle-showhide-with-jquery
*/
jQuery(".activatehidesection").click(function () {
    jQuery(this).next().slideToggle();
}).next().hide();


/* Show the next hidden div if clicking a radio button class="activatehidesectionAdd"
 * The next div must have the class="hidesectionOfRadio"
 * And a css "display:none;" to work
 * Then a new radio button class="activatehidesectionAdd2" does the same
 * source: Lost Highway
*/


if(jQuery("input.activatehidesectionAdd").checked === true){
	jQuery(".hidesectionOfRadio").hide();
} else {
	jQuery(".hidesectionOfRadio").show();
}

jQuery("input.activatehidesectionAdd").click(function () {
    jQuery(".hidesectionOfRadio").slideToggle();
}).nextAll(".hidesectionOfRadio").show();

jQuery("input.activatehidesectionRemove").click(function () {
    jQuery(".hidesectionOfRadio").slideToggle();
}).nextAll(".hidesectionOfRadio").hide();

if(jQuery("input.activatehidesectionAddTwo").checked == true){
	jQuery(".hidesectionOfRadioTwo").hide();
} else {
	jQuery(".hidesectionOfRadioTwo").show();
}

jQuery("input.activatehidesectionAddTwo").click(function () {
    jQuery(".hidesectionOfRadioTwo").slideToggle();
}).nextAll(".hidesectionOfRadioTwo").hide();

jQuery("input.activatehidesectionRemoveTwo").click(function () {
    jQuery(".hidesectionOfRadioTwo").slideToggle();
}).nextAll(".hidesectionOfRadioTwo").show();




