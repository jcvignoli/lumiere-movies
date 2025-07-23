<?php declare( strict_types = 1 );
/**
 * Class for displaying movies module Pic.
 *
 * @copyright (c) 2025, Lost Highway
 *
 * @version       1.0
 * @package       lumieremovies
 */

namespace Lumiere\Frontend\Module\Person;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Config\Settings' ) ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Config\Get_Options;

/**
 * Method to display pic for persons
 *
 * @since 4.5 new class
 */
final class Person_Pic extends \Lumiere\Frontend\Module\Parent_Module {

	/**
	 * Display the title and possibly the year
	 *
	 * @param \Lumiere\Vendor\Imdb\Name $person IMDbPHP person class
	 * @param 'pic' $item_name The name of the item
	 */
	public function get_module( \Lumiere\Vendor\Imdb\Name $person, string $item_name ): string {

		$person_name = $person->name() ?? '';

		if ( $this->is_popup_page() === true ) { // Method in trait Main.
			return $this->get_module_popup( $person, $item_name );
		}

		// If cache is active, use the pictures from IMDBphp class.
		if ( $this->imdb_cache_values['imdbusecache'] === '1' ) {
			return $this->link_maker->get_picture( $person->photoLocalurl( false ), $person->photoLocalurl( true ), $person_name );
		}

		// If cache is deactivated, display no_pics.gif
		$no_pic_url = Get_Options::LUM_PICS_URL . 'no_pics.gif';
		return $this->link_maker->get_picture( $no_pic_url, $no_pic_url, $person_name );
	}

	/**
	 * Display the Popup version of the module
	 * This one is never used, kept for compatibility
	 *
	 * @param \Lumiere\Vendor\Imdb\Name $person IMDbPHP person class
	 * @param 'pic' $item_name The name of the item
	 */
	public function get_module_popup( \Lumiere\Vendor\Imdb\Name $person, string $item_name ): string {

		$output = "\n\t\t\t\t\t\t\t\t\t<!-- star photo -->";
		$output .= "\n\t\t\t" . '<div class="lumiere_padding_two lum_popup_img">';

		// Select pictures: big poster, if not small poster, if not 'no picture'.
		$photo_url = '';
		$photo_big = (string) $person->photoLocalurl( false );
		$photo_thumb = (string) $person->photoLocalurl( true );

		if ( $this->imdb_cache_values['imdbusecache'] === '1' ) { // use IMDBphp only if cache is active
			$photo_url = strlen( $photo_big ) > 1 ? esc_url( $photo_big ) : esc_url( $photo_thumb ); // create big picture, thumbnail otherwise.
		}

		// Picture for a href, takes big/thumbnail picture if exists, no_pics otherwise.
		$photo_url_href = strlen( $photo_url ) === 0 ? esc_url( Get_Options::LUM_PICS_URL . 'no_pics.gif' ) : $photo_url; // take big/thumbnail picture if exists, no_pics otherwise.

		// Picture for img: if 1/ thumbnail picture exists, use it, 2/ use no_pics otherwise
		$photo_url_img = strlen( $photo_thumb ) === 0 ? esc_url( Get_Options::LUM_PICS_URL . 'no_pics.gif' ) : $photo_thumb;

		$output .= "\n\t\t\t\t" . '<a class="lum_pic_inpopup" href="' . esc_url( $photo_url_href ) . '">';
		$output .= "\n\t\t\t\t\t" . '<img loading="lazy" src="' . esc_url( $photo_url_img ) . '" alt="' . esc_attr( $person->name() ?? '' ) . '"';

		// add width only if "Display only thumbnail" is unactive.
		if ( $this->imdb_admin_values['imdbcoversize'] === '0' ) {
			$width = intval( $this->imdb_admin_values['imdbcoversizewidth'] );
			$height = (float) $width * 1.4;
			$output .= ' width="' . esc_attr( strval( $width ) ) . '" height="' . esc_attr( strval( $height ) ) . '"';

			// add 100px width if "Display only thumbnail" is active.
		} elseif ( $this->imdb_admin_values['imdbcoversize'] === '1' ) {

			$output .= ' width="100" height="160"';

		}

		$output .= ' />';
		$output .= "\n\t\t\t\t</a>";

		$output .= "\n\t\t\t" . '</div>';

		return $output;
	}
}
