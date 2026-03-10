<?php declare( strict_types = 1 );
/**
 * Text that will be displayed on frontend and block editor
 *
 * @copyright (c) 2026, Lost Highway
 *
 * @version       1.0
 * @package       lumieremovies
 * @since         4.7.4 New file
 */
namespace Lumiere;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

/**
 * Display text only if main attributes are available, and also if 'lum_display_movies_box' filter is available
 */
if ( isset( $attributes['lumiere_imdblt_select'], $attributes['content'] ) && has_filter( 'lum_display_movies_box' ) === true   ) {

	$lumiere_value_array = explode( '_', $attributes['lumiere_imdblt_select'] );
	$lumiere_movie_or_person = $lumiere_value_array[1] ?? 'movie'; // Either movie or person.
	$lumiere_mid_or_name = isset( $lumiere_value_array[2] ) && str_contains( $lumiere_value_array[2], 'id' ) ? 'bymid' : 'byname';

	$lumiere_imdbid_or_title = [];
	$lumiere_imdbid_or_title[][ $lumiere_mid_or_name ] = esc_html( $attributes['content'] );

	$lumiere_array = [];
	if ( $lumiere_movie_or_person === 'movie' ) {
		$lumiere_array = apply_filters( 'lum_find_movie_id', $lumiere_imdbid_or_title );
	} elseif ( $lumiere_movie_or_person === 'person' ) {
		$lumiere_array = apply_filters( 'lum_find_person_id', $lumiere_imdbid_or_title );
	}
	echo wp_kses(
		/**
		 * Filter to display movie and person box.
		 *
		 *
		 * @var array<array{bymid?: string, byname?: string}> $lumiere_array List of movies/persons with IMDb IDs.
		 */
		apply_filters( "lum_display_{$lumiere_movie_or_person}s_box", $lumiere_array ),
		[
			'a' => [
				'data-*' => true,
				'id' => [],
				'class' => [],
				'href' => [],
				'title' => [],
			],
			'div' => [
				'id' => [],
				'class' => [],
			],
			'span' => [
				'id' => [],
				'class' => [],
			],
			'h4' => [
				'id' => [],
				'class' => [],
			],
			'img' => [
				'decoding' => [],
				'loading' => [],
				'class' => [],
				'alt' => [],
				'src' => [],
				'width' => [],
			],
		]
	);
}

