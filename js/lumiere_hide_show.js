/* Function to display/hide an element if clicking on another element
*										
* Content Security Policy (CSP) Compliant, needs JQuery
* 
* source https://isabelcastillo.com/toggle-showhide-with-jquery
*/

jQuery(".activatehidesection").click(function () {
    jQuery(this).next().slideToggle();
}).next().hide();



