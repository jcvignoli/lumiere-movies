<?php
/**
 * Class for displaying movies module Source.
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
 * Method to display Source for movies
 * @since 4.8.2 Using interface
 *
 * @extends Parent_Module<'source', array{}, \Lumiere\Vendor\Imdb\Title>
 */
final class Movie_Source extends Parent_Module {

	/**
	 * Display the module
	 * @inherit
	 */
	#[\Override]
	public function get_module( object $imdb_class, string $item_name ): string {

		$get_mid = (int) $imdb_class->imdbid();

		if ( $get_mid === 0 ) {
			return '';
		}

		if ( $this->is_popup_page() === true ) { // Method in trait Main.
			return $this->get_module_popup( $item_name, [], $get_mid );
		}

		$output = $this->output_class->misc_layout(
			'frontend_subtitle_item',
			ucfirst( Get_Options_Movie::get_all_fields( /* no number because no plural here */ )[ $item_name ] )
		);

		$output .= $this->link_maker->get_source( (string) $get_mid );

		return $output;
	}

	/**
	 * Display the Popup version of the module
	 * @inherit
	 */
	#[\Override]
	public function get_module_popup( string $item_name, array $item_results, int $nb_total_items ): string {

		$output = $this->output_class->misc_layout(
			'popup_subtitle_item',
			ucfirst( Get_Options_Movie::get_all_fields( /* no number because no plural here */ )[ $item_name ] )
		);

		$output .= $this->link_maker->get_source( (string) $nb_total_items );

		return $output;
	}
}
