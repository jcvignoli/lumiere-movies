<?php declare( strict_types = 1 );
/**
 * Class for displaying persons module Bio.
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

/**
 * Method to display bio for Persons
 *
 * @since 4.6 new class
 */
final class Person_Bio extends \Lumiere\Frontend\Module\Parent_Module {

	/**
	 * Display the biography
	 *
	 * @param \Lumiere\Vendor\Imdb\Name $name IMDbPHP title class
	 * @param 'bio' $item_name The name of the item
	 */
	public function get_module( \Lumiere\Vendor\Imdb\Name $name, string $item_name ): string {
		$bio = $name->$item_name();
		if ( $this->is_popup_page() === true ) { // Method in trait Main.
			return $this->get_module_popup( $bio );
		}
		return $this->link_maker->get_medaillon_bio( $bio, 800 );
	}

	/**
	 * Display the Popup version of the module
	 * Not in use, kept for compatibility
	 *
	 * @param array<array<string, string>> $bio Biography
	 */
	public function get_module_popup( array $bio ): string {
		$output = "\n\t\t\t\t" . '<div id="bio" class="lumiere_padding_one_em lumiere_align_left"><font size="-1">';
		$output .= $this->link_maker->get_medaillon_bio( $bio, 300 );
		$output .= '</font></div>';
		return $output;
	}
}
