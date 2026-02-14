<?php declare( strict_types = 1 );
/**
 * Template for the calendar pages
 *
 * @copyright (c) 2025, Lost Highway
 *
 * @version       1.0
 * @package       lumieremovies
 */
namespace Lumiere\Admin;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

// Get vars from the calling class.
$lumiere_results = $variables['lum_results']; /** @phpstan-ignore variable.undefined  */
$lumiere_link_maker = $variables['lum_link_maker']; /** @phpstan-ignore variable.undefined  */

foreach ( $lumiere_results as $lumiere_date => $lumiere_arrays ) {

	echo is_string( $lumiere_date ) !== false ? "\n\t\t" . '<div class="lum_calendar_date">' . esc_html__( 'Release date:', 'lumiere-movies' ) . ' ' . esc_html( $lumiere_date ) . '</div>' : '';
	echo "\n\t" . '<div class="lum_calendar_container">';
	foreach ( $lumiere_arrays as $lumiere_row => $lumiere_cal_data ) {
		$lumiere_nb_casts = count( $lumiere_cal_data['cast'] );
		$lumiere_img_url = strlen( $lumiere_cal_data['imgUrl'] ?? '' ) > 0 ? $lumiere_cal_data['imgUrl'] : \Lumiere\Config\Get_Options::LUM_NOPICS_URL;

		echo "\n\t\t" . '<div class="lum_calendar_contained">';
		echo "\n\t\t\t" . '<div class="lum_calendar_pic"><a href="' . esc_attr( $lumiere_cal_data['imgUrl'] ) . '"><img class="lum_calendar_pic" loading="lazy" src="' . esc_attr( $lumiere_img_url ) . '" /></a></div>';
		$lumiere_popup_link = $lumiere_link_maker->get_popup_film_id( esc_html( $lumiere_cal_data['title'] ), esc_html( $lumiere_cal_data['imdbid'] ) );
		echo "\n\t\t\t" . '<div class="lum_calendar_contained_container">';
		echo "\n\t\t\t\t" . '<div class="lum_calendar_contained_title">' . wp_kses(
			$lumiere_popup_link,
			[
				'a' => [
					'data-*' => true,
					'title' => [],
					'class' => [],
				],
			]
		) . '</div>';
		echo $lumiere_nb_casts > 0 ? "\n\t\t\t\t" . '<div class="lum_calendar_actors_list">' . esc_html( _n( 'Actor:', 'Actors:', $lumiere_nb_casts, 'lumiere-movies' ) ) . ' ' : '';
		foreach ( $lumiere_cal_data['cast'] as $lumiere_actor_names ) {
			echo "\n\t\t\t\t\t" . '<li class="lum_calendar_actors">' . esc_html( $lumiere_actor_names ) . '</li>';
		}
		echo $lumiere_nb_casts > 0 ? '</div>' : '';
		echo "\n\t\t\t" . '</div>';
		echo "\n\t\t" . '</div>';
	}
	echo "\n\t" . '</div>';
}
