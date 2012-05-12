$(document).ready(function() {


	/* relationship of inner to outer slides takes some tweaking
	
	what i came up with initially is for an inner speed (inner-slideshow-content) of
	
	1000 (in: 200, out: 400)
	
	to cycle through 10 slides takes an outer speed of 42000 (in 500, out 500)
	
	*/

	$('.view .view-content').cycle({ 
    fx:    'fade', 
    speed:  42000,
		speedIn: 500,
    speedOut: 500,
 });
 
 $('.inner-slideshow-content').cycle({
    fx:    'fade', 
    speed:  1000,
/*		speedIn: 200,
    speedOut: 400,
 */
    after:   onAfter 
 });
 

function onAfter() { 
    var caption = $('.flickr-slide-caption').html();
    $('#title-line').html(caption);
}



 
 // set page background by URL
var loc = new String(window.location);

if (loc.indexOf('/south_america_updates', 0) > 0) {
	$('body').css('background-color', '#e0fcea');
	$('.view .view-content .views-row').css('background-color', '#e0fcea');
} else if (loc.indexOf('/nyc_updates', 0) > 0) {
	$('body').css('background-color', '#e0faff');
	$('.view .view-content .views-row').css('background-color', '#e0faff');
} else if (loc.indexOf('/east_asia_updates', 0) > 0) {
	$('body').css('background-color', '#ffebeb');
	$('.view .view-content .views-row').css('background-color', '#ffebeb');
} else if (loc.indexOf('/mumbai_updates', 0) > 0) {
	$('body').css('background-color', '#fff3e0');
	$('.view .view-content .views-row').css('background-color', '#fff3e0');
} else if (loc.indexOf('/amman_updates', 0) > 0) {
	$('body').css('background-color', '#ffe0f4');
	$('.view .view-content .views-row').css('background-color', '#ffe0f4');
}



});

