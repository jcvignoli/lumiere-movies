<?php
/**
 * Class for displaying movies module Pic.
 *
 * @copyright (c) 2025, Lost Highway
 *
 * @version       1.0
 * @package       lumieremovies
 */
declare( strict_types = 1 );

namespace Lumiere\Frontend\Module\Movie;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Config\Get_Options;
use Lumiere\Frontend\Module\Parent_Module;

/**
 * Method to display pic for movies
 *
 * @since 4.5 new class
 * @since 4.8 $movie_title = $movie->title(); https://wordpress.org/support/topic/critical-bug-missing-imdb-data-images-causing-php-fatal-errors-and-100-cpu/
 * @since 4.8.2 Using interface
 *
 * @extends Parent_Module<'pic', array<string, \Lumiere\Vendor\Imdb\Title>, \Lumiere\Vendor\Imdb\Title>
 */
final class Movie_Pic extends Parent_Module {

	/**
	 * Display the title and possibly the year
	 * @inherit
	 */
	#[\Override]
	public function get_module( object $imdb_class, string $item_name ): string {

		if ( $this->is_popup_page() === true ) { // Method in trait Main.
			return $this->get_module_popup( $item_name, [ 'class' => $imdb_class ], 0 );
		}

		$movie_title = $imdb_class->title();

		// If cache is active, use the pictures from IMDBphp class.
		if ( $this->settings->get_cache_option( 'imdbusecache' ) === '1' ) {
			return $this->link_maker->get_picture( $imdb_class->photoLocalurl( false ), $imdb_class->photoLocalurl( true ), $movie_title );
		}

		// If cache is deactivated, display no_pics.png
		$no_pic_url = Get_Options::LUM_NOPICS_URL;
		return $this->link_maker->get_picture( $no_pic_url, $no_pic_url, $movie_title );
	}

	/**
	 * Display the Popup version of the module
	 * @inherit
	 */
	#[\Override]
	public function get_module_popup( string $item_name, array $item_results, int $nb_total_items ): string {

		$output = "\n\t\t\t\t\t\t\t\t\t<!-- Movie's picture display -->";
		$output .= "\n\t\t" . '<div class="lum_popup_img">';

		// Select pictures: big poster, if not small poster, if not 'no picture'.
		$photo_url = '';
		$photo_big = (string) $item_results['class']->photoLocalurl( false );
		$photo_thumb = (string) $item_results['class']->photoLocalurl( true );

		if ( $this->settings->get_cache_option( 'imdbusecache' ) === '1' ) { // use IMDBphp only if cache is active
			$photo_url = strlen( $photo_big ) > 1 ? esc_html( $photo_big ) : esc_html( $photo_thumb ); // create big picture, thumbnail otherwise.
		}

		$movie_title = $item_results['class']->title();

		// Picture for a href, takes big/thumbnail picture if exists, no_pics otherwise.
		$photo_url_href = strlen( $photo_url ) === 0 ? Get_Options::LUM_NOPICS_URL : $photo_url;

		// Picture for img: if 1/ thumbnail picture exists, use it, 2/ use no_pics otherwise
		$photo_url_img = strlen( $photo_thumb ) === 0 ? esc_url( Get_Options::LUM_NOPICS_URL ) : $photo_thumb;

		$output .= '<a class="lum_pic_inpopup" href="' . esc_url( $photo_url_href ) . '">';
		// loading="eager" to prevent WordPress loading lazy that doesn't go well with cache scripts.
		$output .= "\n\t\t" . '<img loading="lazy" src="' . esc_url( $photo_url_img ) . '" alt="' . esc_attr( $movie_title ) . '"';

		// add width only if "Display only thumbnail" is not active.
		if ( $this->settings->get_admin_option( 'imdbcoversize' ) === '0' ) {
			$width = intval( $this->settings->get_admin_option( 'imdbcoversizewidth' ) );
			$height = (float) $width * 1.4;
			$output .= ' width="' . esc_attr( strval( $width ) ) . '" height="' . esc_attr( strval( $height ) ) . '"';

			// set width to 100px width if "Display only thumbnail" is active.
		} elseif ( $this->settings->get_admin_option( 'imdbcoversize' ) === '1' ) {

			$output .= ' height="160" width="100"';

		}

			$output .= ' />';
			$output .= "\n\t\t\t\t</a>";
			$output .= "\n\t\t\t</div>";
			return $output;
	}

	/**
	 * Wrapping method for Popup_Film
	 *
	 * @param \Lumiere\Vendor\Imdb\Title $movie IMDbPHP title class
	 * @param 'pic' $item_name The name of the item
	 * @since 4.7.1
	 */
	public function get_module_popup_two_columns( \Lumiere\Vendor\Imdb\Title $movie, string $item_name ): string {
		return $this->get_module( $movie, $item_name );
	}
}
