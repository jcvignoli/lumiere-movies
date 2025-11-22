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
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Config\Settings' ) ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

// Retrieve the vars from calling class.
$lum_results = get_transient( 'calendar_vars' )[0];
$lum_link_maker = get_transient( 'calendar_vars' )[1];

foreach ( $lum_results as $lum_date => $lum_arrays ) {
	$lum_date_first = is_string( $lum_date ) ? strtotime( str_replace( '-', '/', $lum_date ) ) : false;
	$lum_date_full = $lum_date_first !== false ? wp_date( get_option( 'date_format' ), intval( $lum_date_first ) ) : false;

	echo $lum_date_full !== false ? "\n\t\t" . '<div class="lum_calendar_date">' . esc_html__( 'Release date:', 'lumiere-movies' ) . ' ' . esc_html( $lum_date_full ) . '</div>' : '';
	echo "\n\t" . '<div class="lum_calendar_container">';
	foreach ( $lum_arrays as $lum_row => $lum_cal_data ) {
		$lum_nb_casts = count( $lum_cal_data['cast'] );
		$lum_img_url = strlen( $lum_cal_data['imgUrl'] ?? '' ) > 0 ? $lum_cal_data['imgUrl'] : \Lumiere\Config\Get_Options::LUM_NOPICS_URL;

		echo "\n\t\t" . '<div class="lum_calendar_contained">';
		echo "\n\t\t\t" . '<div class="lum_calendar_pic"><a href="' . esc_attr( $lum_cal_data['imgUrl'] ) . '"><img class="lum_calendar_pic" loading="lazy" src="' . esc_attr( $lum_img_url ) . '" /></a></div>';
		$lum_popup_link = $lum_link_maker->get_popup_film_id( esc_html( $lum_cal_data['title'] ), esc_html( $lum_cal_data['imdbid'] ) );
		echo "\n\t\t\t" . '<div class="lum_calendar_contained_container">';
		echo "\n\t\t\t\t" . '<div class="lum_calendar_contained_title">' . wp_kses(
			$lum_popup_link,
			[
				'a' => [
					'data-*' => true,
					'title' => [],
					'class' => [],
				],
			]
		) . '</div>';
		echo $lum_nb_casts > 0 ? "\n\t\t\t\t" . '<div class="lum_calendar_actors_list">' . esc_html( _n( 'Actor:', 'Actors:', $lum_nb_casts, 'lumiere-movies' ) ) . ' ' : '';
		foreach ( $lum_cal_data['cast'] as $lum_actor_names ) {
			echo "\n\t\t\t\t\t" . '<li class="lum_calendar_actors">' . esc_html( $lum_actor_names ) . '</li>';
		}
		echo $lum_nb_casts > 0 ? '</div>' : '';
		echo "\n\t\t\t" . '</div>';
		echo "\n\t\t" . '</div>';
	}
	echo "\n\t" . '</div>';
}
