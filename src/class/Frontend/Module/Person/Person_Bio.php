<?php
/**
 * Class for displaying persons module Bio.
 *
 * @copyright (c) 2025, Lost Highway
 *
 * @version       1.0
 * @package       lumieremovies
 */
declare( strict_types = 1 );

namespace Lumiere\Frontend\Module\Person;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Frontend\Module\Parent_Module;

/**
 * Method to display bio for Persons
 *
 * @since 4.6 new class
 * @since 4.8.2 Using interface
 *
 * @extends Parent_Module<'bio', array<array<string, string>>, \Lumiere\Vendor\Imdb\Name>
 */
final class Person_Bio extends Parent_Module {

	/**
	 * Display the biography
	 * @inherit
	 */
	#[\Override]
	public function get_module( object $imdb_class, string $item_name ): string {
		$bio = $imdb_class->$item_name();
		if ( $this->is_popup_page() === true ) { // Method in trait Main.
			return $this->get_module_popup( 'bio', $bio, 0 );
		}
		return $this->link_maker->get_medaillon_bio( $bio, 800 );
	}

	/**
	 * Display the Popup version of the module
	 * Not in use, kept for compatibility
	 * @inherit
	 */
	#[\Override]
	public function get_module_popup( string $item_name, array $item_results, int $nb_total_items ): string {
		$output = "\n\t\t\t\t" . '<div id="bio" class="lumiere_padding_one_em lumiere_align_left lum_minus10">';
		$output .= $this->link_maker->get_medaillon_bio( $item_results, 300 );
		$output .= '</div>';
		return $output;
	}
}
