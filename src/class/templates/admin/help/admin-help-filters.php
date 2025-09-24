<?php declare( strict_types = 1 );
/**
 * Template for the Hooks in help
 *
 * @copyright (c) 2025, Lost Highway
 *
 * @version       1.0
 * @package       lumieremovies
 */
namespace Lumiere\Admin;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Config\Settings' ) ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}
?>

<div class="lumiere_wrap">

	<div class="lumiere_title_options lumiere_border_shadow">
		<h3 id="layout" name="layout"><?php esc_html_e( 'WordPress hooks', 'lumiere-movies' ); ?></h3>
	</div>

	<div class="lumiere_border_shadow helpdiv">
	
		<li class="lum_h4_help_filters"><?php esc_html_e( 'Find the IMDb ID for a person', 'lumiere-movies' ); ?></li>
		<div><?php esc_html_e( 'Get the IMDb identification for a person.(return array)', 'lumiere-movies' ); ?></div>
		<blockquote class="lum_bloquote_help_filters">apply_filters( 'lum_find_person_id', 'person_name' )</blockquote>
		<ol>
			<li><i>person_name</i> (array): <?php
			/* translators: %s is an HTML tag (a code) */
			echo wp_kses( wp_sprintf( __( 'The name of the person to search for (ie: %s )', 'lumiere-movies' ), '<span class="lum_exemple">[ \'Stanley Kubrick\' ]</span>' ), [ 'span' => [ 'class' => [] ] ] ); ?></li>
		</ol>
		
		<li class="lum_h4_help_filters"><?php esc_html_e( 'Find the Movie ID for a movie', 'lumiere-movies' ); ?></li>
		<div><?php esc_html_e( 'Get the IMDb identification for a movie. (return array)', 'lumiere-movies' ); ?></div>
		<blockquote class="lum_bloquote_help_filters">apply_filters( 'lum_find_movie_id', 'array_titles' )</blockquote>
		<ol>
			<li><i>array_titles</i> (array):  <?php
			/* translators: %s is an HTML tag (a code) */
			echo wp_kses( wp_sprintf( __( 'The title of the movie to search for (ie: %s )', 'lumiere-movies' ), '<span class="lum_exemple">[ \'2001 the space odyssey\' ]</span>' ), [ 'span' => [ 'class' => [] ] ] ); ?></li>
		</ol>
		
		<li class="lum_h4_help_filters"><?php esc_html_e( 'Display IMDb data on a person', 'lumiere-movies' ); ?></li>
		<div><?php esc_html_e( 'Show a box of IMDb information related to a/multiple selected person/s (return array)', 'lumiere-movies' ); ?></div>
		<blockquote class="lum_bloquote_help_filters">apply_filters( 'lum_display_persons_box', 'array_persons_with_imdbid' )</blockquote>
		<ol>
			<li><i>array_persons_with_imdbid</i> (array): <?php
			/* translators: %s is an HTML tag (a code) */
			echo wp_kses( wp_sprintf( __( 'An array of IMDb people to display (ie: %s )', 'lumiere-movies' ), '<span class="lum_exemple">[ \'0000040\' ]</span>' ), [ 'span' => [ 'class' => [] ] ] ); ?></li>
		</ol>
		
		<li class="lum_h4_help_filters"><?php esc_html_e( 'Display IMDb data on a movie', 'lumiere-movies' ); ?></li>
		<div><?php esc_html_e( 'Show a box of IMDb information related to a/multiple selected movie/s (return array)', 'lumiere-movies' ); ?></div>
		<blockquote class="lum_bloquote_help_filters">apply_filters( 'lum_display_movies_box', 'array_movies_with_imdbid' )</blockquote>
		<ol>
			<li><i>array_movies_with_imdbid</i> (array): <?php
			/* translators: %s is an HTML tag (a code) */
			echo wp_kses( wp_sprintf( __( 'An array of IMDb movies to display (ie: %s )', 'lumiere-movies' ), '<span class="lum_exemple">[ \'0319061\' ]</span>' ), [ 'span' => [ 'class' => [] ] ] ); ?></li>
		</ol>
		
		<li class="lum_h4_help_filters"><?php esc_html_e( 'Display a list of upcoming movies', 'lumiere-movies' ); ?></li>
		<div><?php esc_html_e( 'Show a list of upcoming movies according to a selected country', 'lumiere-movies' ); ?></div>
		<blockquote class="lum_bloquote_help_filters">apply_filters( 'lum_coming_soon', 'countryCode', 'type', 'startDays', 'endDays' )</blockquote>
		<ol>
			<li><i>countryCode</i> (string): <?php
			/* translators: %1$s and %2$s are HTML tags (codes) */
			echo wp_kses( wp_sprintf( __( 'The country on two positions: %1$s for USA, %2$sfor France', 'lumiere-movies' ), '<span class="lum_exemple">\'US\'</span>', '<span class="lum_exemple">\'FR\'</span>' ), [ 'span' => [ 'class' => [] ] ] ); ?></li>
			<li><i>type</i> (string): <?php esc_html_e( 'What type of information is searched for. There are three options:', 'lumiere-movies' ); ?> <span class="lum_exemple">MOVIE</span>, <span class="lum_exemple">TV</span>, <span class="lum_exemple">TV_EPISODE</span>.</li>
			<li><i>startDays</i> (int): <?php
			/* translators: %s is an HTML tag (a code) */
			echo wp_kses( wp_sprintf( __( 'The number of days the movies release list date should start. If %s is selected, movies being released starting in 10 days at least will be displayed.', 'lumiere-movies' ), '<span class="lum_exemple">10</span>' ), [ 'span' => [ 'class' => [] ] ] ); ?></li>
			<li><i>endDays</i> (int): <?php
			/* translators: %s is an HTML tag (a code) */
			echo wp_kses( wp_sprintf( __( 'The number of days the movies release list date should start.  If %s is selected, movies being released until 10 days will be displayed.', 'lumiere-movies' ), '<span class="lum_exemple">10</span>' ), [ 'span' => [ 'class' => [] ] ] ); ?></li>
		</ol>
	</div>
</div>
