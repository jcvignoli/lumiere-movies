<?php declare( strict_types = 1 );
/**
 * Template for the Compatibility in help
 *
 * @copyright (c) 2024, Lost Highway
 *
 * @version       1.0
 * @package       lumieremovies
 */
namespace Lumiere\Admin;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Config\Settings' ) ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

// Retrieve vars from calling class.
//$lum_compatsection_processed = get_transient( Admin_Menu::TRANSIENT_ADMIN )[0]; // text in array from the faq section in readme

?>

<div class="lumiere_wrap">

	<div class="lumiere_title_options lumiere_border_shadow">
		<h3 id="layout" name="layout"><?php esc_html_e( 'WordPress filters', 'lumiere-movies' ); ?></h3>
	</div>

	<div class="lumiere_border_shadow helpdiv">
	
		<h4 class="lum_h4_help_filters">Find the IMDb ID for a person</h4>
		<div>Get the IMDb identification for a person.(return array)</div>
		<blockquote class="lum_bloquote_help_filters">apply_filters( 'lum_find_person_id', 'person_name' )</blockquote>
		<ol>
			<li><i>person_name</i> (array): The name of the person to search for (ie: <span class="lum_exemple">[ 'Stanley Kubrick' ]</span> )</li>
		</ol>
		
		<h4 class="lum_h4_help_filters">Find the Movie ID for a movie</h4>
		<div>Get the IMDb identification for a movie. (return array)</div>
		<blockquote class="lum_bloquote_help_filters">apply_filters( 'lum_find_movie_id', 'title' )</blockquote>
		<ol>
			<li><i>array_titles</i> (array):  The title of the movie to search for (ie: <span class="lum_exemple">[ '2001 the space odyssey' ]</span> )</li>
		</ol>
		
		<h4 class="lum_h4_help_filters">Display IMDb data on a person</h4>
		<div>Show a box of IMDb information related to a/multiple selected person/s (return array)</div>
		<blockquote class="lum_bloquote_help_filters">apply_filters( 'lum_display_persons_box', 'array_persons_with_imdbid' )</blockquote>
		<ol>
			<li><i>array_persons_with_imdbid</i> (array): An array of IMDb people to display (ie: <span class="lum_exemple">[ '0000040' ]</span> )</li>
		</ol>
		
		<h4 class="lum_h4_help_filters">Display IMDb data on a movie</h4>
		<div>Show a box of IMDb information related to a/multiple selected movie/s (return array)</div>
		<blockquote class="lum_bloquote_help_filters">apply_filters( 'lum_display_movies_box', 'array_movies_with_imdbid' )</blockquote>
		<ol>
			<li><i>array_movies_with_imdbid</i> (array): An array of IMDb movies to display (ie: <span class="lum_exemple">[ '0319061' ]</span> )</li>
		</ol>
		
		<h4 class="lum_h4_help_filters">Display a list of upcoming movies</h4>
		<div>Show a list of upcoming movies according to a selected country</div>
		<blockquote class="lum_bloquote_help_filters">apply_filters( 'lum_coming_soon', 'countryCode', 'type', 'startDays', 'endDays' )</blockquote>
		<ol>
			<li><i>countryCode</i> (string): The country on two positions: <span class="lum_exemple">'US'</span> for USA, <span class="lum_exemple">'FR'</span> for France</li>
			<li><i>type</i> (string): What type of information is searched for. There are three options: <span class="lum_exemple">MOVIE</span>, <span class="lum_exemple">TV</span>, <span class="lum_exemple">TV_EPISODE</span>.</li>
			<li><i>startDays</i> (int): The number of days the movies release list date should start. If <span class="lum_exemple">10</span> is selected, movies being released starting in 10 days at least will be displayed.</li>
			<li><i>endDays</i> (int): The number of days the movies release list date should start.  If <span class="lum_exemple">10</span> is selected, movies being released until 10 days will be displayed.</li>
		</ol>
	</div>
</div>
