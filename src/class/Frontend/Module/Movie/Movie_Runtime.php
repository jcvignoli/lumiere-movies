<?php
/**
 * Class for displaying movies module Runtime.
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

use Lumiere\Config\Get_Options_Movie;
use Lumiere\Frontend\Module\Parent_Module;

/**
 * Method to display Runtime for movies
 *
 * @since 4.5 new class
 * @since 4.8.2 Using interface
 *
 * @extends Parent_Module<'runtime', array{}, \Lumiere\Vendor\Imdb\Title>
 */
final class Movie_Runtime extends Parent_Module {

	/**
	 * Display the Runtime
	 * @inherit
	 */
	#[\Override]
	public function get_module( object $imdb_class, string $item_name ): string {

		$item_results = isset( $imdb_class->$item_name()[0]['time'] ) ? strval( $imdb_class->$item_name()[0]['time'] ) : '';

		if ( strlen( $item_results ) === 0 ) {
			return '';
		}

		if ( $this->is_popup_page() === true ) { // Method in trait Main.
			return $this->get_module_popup( $item_name, [], (int) $item_results );
		}

		return $this->output_class->misc_layout(
			'frontend_subtitle_item',
			ucfirst( Get_Options_Movie::get_all_fields( /* no number because no plural here */ )[ $item_name ] )
		)
			. $item_results . ' ' . __( 'minutes', 'lumiere-movies' );
	}

	/**
	 * Display the Popup version of the module
	 * @inherit
	 */
	#[\Override]
	public function get_module_popup( string $item_name, array $item_results, int $nb_total_items ): string {
		return $this->output_class->misc_layout(
			'popup_subtitle_item',
			ucfirst( Get_Options_Movie::get_all_fields( /* no number because no plural here */ )[ $item_name ] )
		)
			. $nb_total_items . ' ' . __( 'minutes', 'lumiere-movies' );
	}
}
